<?php
/**
 * Admin-side AJAX handlers.
 *
 * @package LoyalSystem
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LS_Admin_Ajax {

    public static function init() {
        $actions = array(
            // Invoices & customers
            'ls_admin_generate_otp_for_phone',
            'ls_admin_add_invoice',
            'ls_admin_update_invoice',
            'ls_admin_lookup_phone',
            'ls_admin_search_customer',
            'ls_admin_get_balance',
            'ls_admin_get_ledger',
            // Tickets
            'ls_admin_update_ticket',
            'ls_admin_delete_ticket',
            'ls_admin_get_ticket_images',
            // SMS
            'ls_admin_test_sms',
            // Customers CRUD
            'ls_admin_get_customers',
            'ls_admin_update_customer',
            'ls_admin_delete_customer',
            // Branches CRUD
            'ls_admin_add_branch',
            'ls_admin_update_branch',
            'ls_admin_delete_branch',
            // Categories CRUD
            'ls_admin_add_category',
            'ls_admin_update_category',
            'ls_admin_delete_category',
        );

        foreach ( $actions as $action ) {
            $method  = 'handle_' . str_replace( 'ls_admin_', '', $action );
            $handler = array( __CLASS__, $method );
            if ( method_exists( __CLASS__, $method ) ) {
                add_action( 'wp_ajax_' . $action, $handler );
            }
        }
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private static function verify( $cap = 'read' ) {
        if ( ! check_ajax_referer( 'ls_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'loyal-system' ) ), 403 );
        }
        if ( ! LS_Roles::current_user_can_access() && ! current_user_can( $cap ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'loyal-system' ) ), 403 );
        }
    }

    private static function send_error( WP_Error $error ) {
        wp_send_json_error( array( 'message' => $error->get_error_message() ), 400 );
    }

    // ── OTP generation for staff ──────────────────────────────────────────────

    public static function handle_generate_otp_for_phone() {
        self::verify();

        $phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
        if ( empty( $phone ) ) {
            wp_send_json_error( array( 'message' => __( 'Phone number is required.', 'loyal-system' ) ), 400 );
        }

        $code = LS_OTP::generate( $phone );
        if ( ! $code ) {
            wp_send_json_error( array( 'message' => __( 'Could not generate verification code.', 'loyal-system' ) ), 500 );
        }

        wp_send_json_success( array( 'code' => $code ) );
    }

    // ── Phone lookup (balance check) ───────────────────────────────────────────

    public static function handle_lookup_phone() {
        self::verify();

        $phone    = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
        $customer = $phone ? LS_Customer::get_by_phone( $phone ) : null;

        if ( ! $customer ) {
            wp_send_json_success( array( 'found' => false, 'balance' => 0, 'name' => '' ) );
        }

        wp_send_json_success( array(
            'found'   => true,
            'name'    => $customer->full_name ?: '',
            'balance' => LS_Database::get_balance( (int) $customer->id ),
        ) );
    }

    // ── Invoices ───────────────────────────────────────────────────────────────

    public static function handle_add_invoice() {
        self::verify();

        $phone  = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
        $amount = (float) ( $_POST['amount'] ?? 0 );

        if ( empty( $phone ) ) {
            wp_send_json_error( array( 'message' => __( 'Phone number is required.', 'loyal-system' ) ), 400 );
        }
        if ( $amount <= 0 ) {
            wp_send_json_error( array( 'message' => __( 'Amount must be greater than zero.', 'loyal-system' ) ), 400 );
        }

        // Auto find or create customer by phone.
        $customer = LS_Customer::get_by_phone( $phone );
        if ( ! $customer ) {
            $id = LS_Customer::create( $phone );
            if ( ! $id ) {
                wp_send_json_error( array( 'message' => __( 'Could not create customer for this phone number.', 'loyal-system' ) ), 500 );
            }
            $customer = LS_Customer::get_by_id( $id );
        }

        $redeem_amount = (float) ( $_POST['redeem_amount'] ?? 0 );

        // Validate redeem amount against current balance.
        if ( $redeem_amount > 0 ) {
            $balance = LS_Database::get_balance( (int) $customer->id );
            if ( $redeem_amount > $balance ) {
                wp_send_json_error( array( 'message' => __( 'Redeem amount exceeds customer balance.', 'loyal-system' ) ), 400 );
            }
        }

        $result = LS_Invoice::add( array(
            'customer_id'     => (int) $customer->id,
            'branch_id'       => (int) ( $_POST['branch_id']   ?? 0 ),
            'amount'          => $amount,
            'currency'        => sanitize_text_field( $_POST['currency']     ?? '' ),
            'discount_amount' => $redeem_amount,
            'invoice_ref'     => sanitize_text_field( $_POST['invoice_ref']  ?? '' ),
            'invoice_date'    => sanitize_text_field( $_POST['invoice_date'] ?? '' ),
            'notes'           => sanitize_textarea_field( $_POST['notes']    ?? '' ),
            'created_by'      => get_current_user_id(),
        ) );

        if ( is_wp_error( $result ) ) {
            self::send_error( $result );
        }

        // ── Invoice file upload ────────────────────────────────────────────────
        if ( ! empty( $_FILES['invoice_file']['tmp_name'] ) ) {
            $upload_dir = wp_upload_dir();
            $dest_dir   = $upload_dir['basedir'] . '/ls-invoices/' . (int) $result;
            wp_mkdir_p( $dest_dir );

            $filename = sanitize_file_name( $_FILES['invoice_file']['name'] );
            $dest     = $dest_dir . '/' . $filename;

            if ( move_uploaded_file( $_FILES['invoice_file']['tmp_name'], $dest ) ) {
                global $wpdb;
                $wpdb->update(
                    $wpdb->prefix . 'ls_invoices',
                    array( 'file_path' => '/ls-invoices/' . (int) $result . '/' . $filename ),
                    array( 'id' => (int) $result )
                );
            }
        }

        // Debit loyalty credits if staff applied a redemption.
        if ( $redeem_amount > 0 ) {
            LS_Database::add_ledger_entry(
                (int) $customer->id,
                (int) $result,
                $redeem_amount,
                'debit',
                sprintf( __( 'Redeemed on invoice #%d', 'loyal-system' ), $result )
            );
        }

        wp_send_json_success( array(
            'message'       => __( 'Invoice added successfully.', 'loyal-system' ),
            'invoice_id'    => $result,
            'customer_name' => $customer->full_name ?: $phone,
        ) );
    }

    public static function handle_update_invoice() {
        self::verify();

        global $wpdb;
        $p          = $wpdb->prefix . 'ls_';
        $invoice_id = (int) ( $_POST['invoice_id'] ?? 0 );

        if ( ! $invoice_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid invoice.', 'loyal-system' ) ), 400 );
        }

        $invoice = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$p}invoices WHERE id = %d", $invoice_id ) );
        if ( ! $invoice ) {
            wp_send_json_error( array( 'message' => __( 'Invoice not found.', 'loyal-system' ) ), 404 );
        }

        // Staff can only edit invoices they created.
        if ( LS_Roles::is_staff_only() && (int) $invoice->created_by !== get_current_user_id() ) {
            wp_send_json_error( array( 'message' => __( 'You can only edit invoices you created.', 'loyal-system' ) ), 403 );
        }

        $amount   = (float) ( $_POST['amount'] ?? $invoice->amount );
        $currency = strtoupper( sanitize_text_field( $_POST['currency'] ?? $invoice->currency ) );

        if ( $amount <= 0 ) {
            wp_send_json_error( array( 'message' => __( 'Amount must be greater than zero.', 'loyal-system' ) ), 400 );
        }

        // Convert to base currency if needed.
        $base_currency = LS_Invoice::get_default_currency();
        if ( $currency !== $base_currency ) {
            $all_currencies = LS_Invoice::get_available_currencies();
            $rate           = isset( $all_currencies[ $currency ]['rate'] ) ? (float) $all_currencies[ $currency ]['rate'] : 0;
            if ( $rate > 0 ) {
                $amount = round( $amount / $rate, 2 );
            }
            $currency = $base_currency;
        }

        $discount_rate   = LS_Settings::discount_rate();
        $discount_amount = $discount_rate > 0 ? round( $amount * $discount_rate / 100, 2 ) : 0;

        $wpdb->update(
            "{$p}invoices",
            array(
                'branch_id'       => (int) ( $_POST['branch_id']   ?? $invoice->branch_id ),
                'amount'          => $amount,
                'currency'        => $currency,
                'discount_amount' => $discount_amount,
                'invoice_ref'     => sanitize_text_field( $_POST['invoice_ref']  ?? $invoice->invoice_ref ),
                'invoice_date'    => sanitize_text_field( $_POST['invoice_date'] ?? $invoice->invoice_date ),
                'notes'           => sanitize_textarea_field( $_POST['notes']    ?? $invoice->notes ),
            ),
            array( 'id' => $invoice_id )
        );

        // Recalculate credit: remove old credit entry for this invoice, add new one.
        $wpdb->delete( "{$p}ledger", array( 'invoice_id' => $invoice_id, 'type' => 'credit' ) );
        $credit_pct = LS_Settings::invoice_credit_pct();
        if ( $credit_pct > 0 ) {
            $points = round( $amount * $credit_pct / 100, 2 );
            if ( $points > 0 ) {
                LS_Database::add_ledger_entry(
                    (int) $invoice->customer_id,
                    $invoice_id,
                    $points,
                    'credit',
                    sprintf( __( 'Invoice #%d credit (edited)', 'loyal-system' ), $invoice_id )
                );
            }
        }

        wp_send_json_success( array( 'message' => __( 'Invoice updated.', 'loyal-system' ) ) );
    }

    // ── Customers ─────────────────────────────────────────────────────────────

    public static function handle_search_customer() {
        self::verify();

        $phone    = sanitize_text_field( $_POST['phone'] ?? '' );
        $customer = LS_Customer::get_by_phone( $phone );

        if ( ! $customer ) {
            wp_send_json_error( array( 'message' => __( 'Customer not found.', 'loyal-system' ) ), 404 );
        }

        $balance = LS_Database::get_balance( $customer->id );

        wp_send_json_success( array(
            'customer' => array(
                'id'        => (int) $customer->id,
                'phone'     => $customer->phone,
                'full_name' => $customer->full_name,
                'email'     => $customer->email,
                'balance'   => $balance,
            ),
        ) );
    }

    public static function handle_get_balance() {
        self::verify();

        $customer_id = (int) ( $_POST['customer_id'] ?? 0 );
        $balance     = LS_Database::get_balance( $customer_id );

        wp_send_json_success( array( 'balance' => $balance ) );
    }

    public static function handle_get_ledger() {
        self::verify();

        $customer_id = (int) ( $_POST['customer_id'] ?? 0 );
        if ( ! $customer_id ) {
            wp_send_json_error( array( 'message' => __( 'Customer ID required.', 'loyal-system' ) ), 400 );
        }

        $ledger = LS_Database::get_ledger( $customer_id );
        wp_send_json_success( $ledger );
    }

    public static function handle_get_customers() {
        self::verify();

        $search = sanitize_text_field( $_POST['search'] ?? '' );
        $paged  = max( 1, (int) ( $_POST['paged'] ?? 1 ) );
        $limit  = 50;
        $offset = ( $paged - 1 ) * $limit;

        $customers = LS_Database::get_all_customers( array(
            'search' => $search,
            'limit'  => $limit,
            'offset' => $offset,
        ) );
        $total = LS_Database::count_customers( $search );

        wp_send_json_success( array(
            'customers' => $customers,
            'total'     => $total,
        ) );
    }

    public static function handle_update_customer() {
        self::verify();

        $customer_id = (int) ( $_POST['customer_id'] ?? 0 );
        if ( ! $customer_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid customer.', 'loyal-system' ) ), 400 );
        }

        $update = array();
        if ( isset( $_POST['full_name'] ) ) {
            $update['full_name'] = sanitize_text_field( $_POST['full_name'] );
        }
        if ( isset( $_POST['email'] ) ) {
            $update['email'] = sanitize_email( $_POST['email'] );
        }
        if ( isset( $_POST['address'] ) ) {
            $update['address'] = sanitize_textarea_field( $_POST['address'] );
        }

        LS_Customer::update( $customer_id, $update );

        wp_send_json_success( array( 'message' => __( 'Customer updated.', 'loyal-system' ) ) );
    }

    public static function handle_delete_customer() {
        self::verify( 'manage_options' );

        $customer_id = (int) ( $_POST['customer_id'] ?? 0 );
        if ( ! $customer_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid customer.', 'loyal-system' ) ), 400 );
        }

        global $wpdb;
        $wpdb->delete( $wpdb->prefix . 'ls_customers', array( 'id' => $customer_id ) );
        $wpdb->delete( $wpdb->prefix . 'ls_ledger',    array( 'customer_id' => $customer_id ) );
        $wpdb->delete( $wpdb->prefix . 'ls_invoices',  array( 'customer_id' => $customer_id ) );

        wp_send_json_success( array( 'message' => __( 'Customer deleted.', 'loyal-system' ) ) );
    }

    // ── Tickets ───────────────────────────────────────────────────────────────

    public static function handle_update_ticket() {
        self::verify();

        $ticket_id   = (int) ( $_POST['ticket_id'] ?? 0 );
        $status      = sanitize_key( $_POST['status']      ?? '' );
        $admin_notes = sanitize_textarea_field( $_POST['admin_notes'] ?? '' );

        if ( ! $ticket_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid ticket.', 'loyal-system' ) ), 400 );
        }

        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'ls_tickets',
            array( 'status' => $status, 'admin_notes' => $admin_notes ),
            array( 'id' => $ticket_id )
        );

        wp_send_json_success( array( 'message' => __( 'Ticket updated.', 'loyal-system' ) ) );
    }

    public static function handle_delete_ticket() {
        self::verify( 'manage_options' );

        $ticket_id = (int) ( $_POST['ticket_id'] ?? 0 );
        if ( ! $ticket_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid ticket.', 'loyal-system' ) ), 400 );
        }

        global $wpdb;
        $wpdb->delete( $wpdb->prefix . 'ls_tickets',       array( 'id'        => $ticket_id ) );
        $wpdb->delete( $wpdb->prefix . 'ls_ticket_images', array( 'ticket_id' => $ticket_id ) );

        wp_send_json_success( array( 'message' => __( 'Ticket deleted.', 'loyal-system' ) ) );
    }

    public static function handle_get_ticket_images() {
        self::verify();

        $ticket_id = (int) ( $_POST['ticket_id'] ?? 0 );
        $images    = LS_Database::get_ticket_images( $ticket_id );

        $uploads_url = wp_upload_dir()['baseurl'];
        $out         = array();
        foreach ( $images as $img ) {
            $out[] = esc_url( $uploads_url . $img->file_path );
        }

        wp_send_json_success( array( 'images' => $out ) );
    }

    // ── SMS ───────────────────────────────────────────────────────────────────

    public static function handle_test_sms() {
        self::verify( 'manage_options' );

        $phone = sanitize_text_field( $_POST['phone'] ?? '' );
        if ( empty( $phone ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a phone number.', 'loyal-system' ) ), 400 );
        }

        $code = LS_OTP::generate( $phone );
        if ( ! $code ) {
            wp_send_json_error( array( 'message' => __( 'Could not generate OTP code.', 'loyal-system' ) ), 500 );
        }

        $result = LS_SMS::send_otp( $phone, $code );
        if ( is_wp_error( $result ) ) {
            self::send_error( $result );
        }

        $provider = LS_Settings::sms_provider();
        $note     = ( 'test' === $provider )
            ? __( 'Test mode — code logged to error_log (not sent via SMS).', 'loyal-system' )
            : sprintf( __( 'SMS sent via %s.', 'loyal-system' ), strtoupper( $provider ) );

        wp_send_json_success( array(
            'message'  => $note,
            'code'     => $code,
            'provider' => $provider,
        ) );
    }

    // ── Branches ──────────────────────────────────────────────────────────────

    public static function handle_add_branch() {
        self::verify();

        $name    = sanitize_text_field( $_POST['name']    ?? '' );
        $address = sanitize_textarea_field( $_POST['address'] ?? '' );
        $phone   = sanitize_text_field( $_POST['phone']   ?? '' );

        if ( empty( $name ) ) {
            wp_send_json_error( array( 'message' => __( 'Branch name is required.', 'loyal-system' ) ), 400 );
        }

        global $wpdb;
        $wpdb->insert( $wpdb->prefix . 'ls_branches', array(
            'name'    => $name,
            'address' => $address,
            'phone'   => $phone,
        ) );

        wp_send_json_success( array(
            'message'   => __( 'Branch added.', 'loyal-system' ),
            'branch_id' => $wpdb->insert_id,
        ) );
    }

    public static function handle_update_branch() {
        self::verify();

        $branch_id = (int) ( $_POST['branch_id'] ?? 0 );
        if ( ! $branch_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid branch.', 'loyal-system' ) ), 400 );
        }

        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'ls_branches',
            array(
                'name'    => sanitize_text_field( $_POST['name']    ?? '' ),
                'address' => sanitize_textarea_field( $_POST['address'] ?? '' ),
                'phone'   => sanitize_text_field( $_POST['phone']   ?? '' ),
            ),
            array( 'id' => $branch_id )
        );

        wp_send_json_success( array( 'message' => __( 'Branch updated.', 'loyal-system' ) ) );
    }

    public static function handle_delete_branch() {
        self::verify( 'manage_options' );

        $branch_id = (int) ( $_POST['branch_id'] ?? 0 );
        if ( ! $branch_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid branch.', 'loyal-system' ) ), 400 );
        }

        global $wpdb;
        $wpdb->delete( $wpdb->prefix . 'ls_branches', array( 'id' => $branch_id ) );

        wp_send_json_success( array( 'message' => __( 'Branch deleted.', 'loyal-system' ) ) );
    }

    // ── Categories ────────────────────────────────────────────────────────────

    public static function handle_add_category() {
        self::verify();

        $name = sanitize_text_field( $_POST['name'] ?? '' );
        $desc = sanitize_textarea_field( $_POST['description'] ?? '' );

        if ( empty( $name ) ) {
            wp_send_json_error( array( 'message' => __( 'Category name is required.', 'loyal-system' ) ), 400 );
        }

        global $wpdb;
        $wpdb->insert( $wpdb->prefix . 'ls_ticket_categories', array(
            'name'        => $name,
            'description' => $desc,
        ) );

        wp_send_json_success( array(
            'message'     => __( 'Category added.', 'loyal-system' ),
            'category_id' => $wpdb->insert_id,
        ) );
    }

    public static function handle_update_category() {
        self::verify();

        $cat_id = (int) ( $_POST['category_id'] ?? 0 );
        if ( ! $cat_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid category.', 'loyal-system' ) ), 400 );
        }

        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'ls_ticket_categories',
            array(
                'name'        => sanitize_text_field( $_POST['name'] ?? '' ),
                'description' => sanitize_textarea_field( $_POST['description'] ?? '' ),
            ),
            array( 'id' => $cat_id )
        );

        wp_send_json_success( array( 'message' => __( 'Category updated.', 'loyal-system' ) ) );
    }

    public static function handle_delete_category() {
        self::verify( 'manage_options' );

        $cat_id = (int) ( $_POST['category_id'] ?? 0 );
        if ( ! $cat_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid category.', 'loyal-system' ) ), 400 );
        }

        global $wpdb;
        $wpdb->delete( $wpdb->prefix . 'ls_ticket_categories', array( 'id' => $cat_id ) );

        wp_send_json_success( array( 'message' => __( 'Category deleted.', 'loyal-system' ) ) );
    }
}
