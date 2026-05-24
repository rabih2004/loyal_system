<?php
/**
 * Frontend AJAX handlers (nopriv + customer-session-gated).
 *
 * @package LoyalSystem
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LS_Public_Ajax {

    public static function init() {
        $nopriv = array(
            'ls_request_otp',
            'ls_verify_otp',
            'ls_customer_login',
            'ls_set_password',
            'ls_customer_logout',
            'ls_submit_ticket',
            'ls_submit_feedback',
            'ls_submit_merchant_feedback',
        );

        $priv = array(
            'ls_get_balance',
            'ls_get_ledger',
            'ls_get_tickets',
            'ls_update_profile',
        );

        foreach ( $nopriv as $action ) {
            $handler = array( __CLASS__, 'handle_' . str_replace( 'ls_', '', $action ) );
            add_action( 'wp_ajax_' . $action,        $handler );
            add_action( 'wp_ajax_nopriv_' . $action, $handler );
        }

        foreach ( $priv as $action ) {
            $handler = array( __CLASS__, 'handle_' . str_replace( 'ls_', '', $action ) );
            add_action( 'wp_ajax_' . $action,        $handler );
            add_action( 'wp_ajax_nopriv_' . $action, $handler );
        }
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private static function verify_nonce() {
        if ( ! check_ajax_referer( 'ls_public_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'loyal-system' ) ), 403 );
        }
    }

    private static function require_customer() {
        if ( ! LS_Session::is_customer_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Please sign in to continue.', 'loyal-system' ) ), 401 );
        }
        return LS_Session::get_customer_id();
    }

    private static function send_error( WP_Error $error ) {
        wp_send_json_error( array( 'message' => $error->get_error_message() ), 400 );
    }

    // ── OTP flow ──────────────────────────────────────────────────────────────

    public static function handle_request_otp() {
        self::verify_nonce();

        $phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
        $name  = sanitize_text_field( wp_unslash( $_POST['name']  ?? '' ) );

        if ( empty( $phone ) ) {
            wp_send_json_error( array( 'message' => __( 'Phone number is required.', 'loyal-system' ) ), 400 );
        }

        $result = LS_Customer::request_otp( $phone, $name );

        if ( is_wp_error( $result ) ) {
            self::send_error( $result );
        }

        $test_mode = ( 'test' === LS_Settings::sms_provider() );

        wp_send_json_success( array(
            'message'    => __( 'Verification code sent. Please check your phone.', 'loyal-system' ),
            'expires_in' => LS_Settings::otp_expiry() * 60,
            'cooldown'   => LS_Settings::otp_resend_cooldown(),
        ) );
    }

    public static function handle_verify_otp() {
        self::verify_nonce();

        $phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
        $code  = sanitize_text_field( wp_unslash( $_POST['code']  ?? '' ) );

        if ( empty( $phone ) || empty( $code ) ) {
            wp_send_json_error( array( 'message' => __( 'Phone and code are required.', 'loyal-system' ) ), 400 );
        }

        $result = LS_Customer::verify_otp_and_login( $phone, $code );

        if ( is_wp_error( $result ) ) {
            self::send_error( $result );
        }

        $customer       = LS_Customer::get_current();
        $balance        = LS_Database::get_balance( LS_Session::get_customer_id() );
        $needs_password = empty( $customer->password_hash );

        wp_send_json_success( array(
            'message'        => __( 'Login successful. Welcome!', 'loyal-system' ),
            'customer'       => array(
                'id'        => (int) $customer->id,
                'phone'     => $customer->phone,
                'full_name' => $customer->full_name,
            ),
            'balance'        => $balance,
            'needs_password' => $needs_password,
        ) );
    }

    // ── Password login ────────────────────────────────────────────────────────

    public static function handle_customer_login() {
        self::verify_nonce();

        $phone    = sanitize_text_field( wp_unslash( $_POST['phone']    ?? '' ) );
        $password = $_POST['password'] ?? '';

        if ( empty( $phone ) || empty( $password ) ) {
            wp_send_json_error( array( 'message' => __( 'Phone and password are required.', 'loyal-system' ) ), 400 );
        }

        $customer = LS_Customer::login_with_password( $phone, $password );

        if ( is_wp_error( $customer ) ) {
            self::send_error( $customer );
        }

        wp_send_json_success( array(
            'message'  => __( 'Login successful.', 'loyal-system' ),
            'customer' => array(
                'id'        => (int) $customer->id,
                'phone'     => $customer->phone,
                'full_name' => $customer->full_name,
            ),
        ) );
    }

    // ── Set password ──────────────────────────────────────────────────────────

    public static function handle_set_password() {
        self::verify_nonce();
        $customer_id = self::require_customer();

        $password = $_POST['password']         ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        if ( strlen( $password ) < 6 ) {
            wp_send_json_error( array( 'message' => __( 'Password must be at least 6 characters.', 'loyal-system' ) ), 400 );
        }
        if ( $password !== $confirm ) {
            wp_send_json_error( array( 'message' => __( 'Passwords do not match.', 'loyal-system' ) ), 400 );
        }

        $result = LS_Customer::set_password( $customer_id, $password );
        if ( ! $result ) {
            wp_send_json_error( array( 'message' => __( 'Could not save password.', 'loyal-system' ) ), 500 );
        }

        wp_send_json_success( array( 'message' => __( 'Password set successfully.', 'loyal-system' ) ) );
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    public static function handle_customer_logout() {
        self::verify_nonce();
        LS_Session::destroy();
        wp_send_json_success( array( 'message' => __( 'Logged out successfully.', 'loyal-system' ) ) );
    }

    // ── Submit ticket ─────────────────────────────────────────────────────────

    public static function handle_submit_ticket() {
        self::verify_nonce();

        $subject        = sanitize_text_field( wp_unslash( $_POST['subject']        ?? '' ) );
        $description    = sanitize_textarea_field( wp_unslash( $_POST['description']  ?? '' ) );
        $category_id    = (int) ( $_POST['category_id']    ?? 0 );
        $branch_id      = (int) ( $_POST['branch_id']      ?? 0 );
        $guest_name     = sanitize_text_field( wp_unslash( $_POST['guest_name']    ?? '' ) );
        $phone          = sanitize_text_field( wp_unslash( $_POST['phone']         ?? '' ) );
        $invoice_number = sanitize_text_field( wp_unslash( $_POST['invoice_number'] ?? '' ) );
        $invoice_date   = sanitize_text_field( wp_unslash( $_POST['invoice_date']   ?? '' ) );
        // Validate date format if provided.
        if ( $invoice_date && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $invoice_date ) ) {
            $invoice_date = '';
        }

        if ( empty( $subject ) ) {
            wp_send_json_error( array( 'message' => __( 'Subject is required.', 'loyal-system' ) ), 400 );
        }

        global $wpdb;

        if ( LS_Session::is_customer_logged_in() ) {
            $customer_id = LS_Session::get_customer_id();
        } else {
            // Try to map the ticket to an existing customer by phone number.
            $customer_id = 0;
            if ( $phone ) {
                $found = $wpdb->get_var( $wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}ls_customers WHERE phone = %s LIMIT 1",
                    $phone
                ) );
                if ( $found ) {
                    $customer_id = (int) $found;
                }
            }
        }
        $inserted = $wpdb->insert( $wpdb->prefix . 'ls_tickets', array(
            'customer_id'    => $customer_id,
            'category_id'    => $category_id,
            'branch_id'      => $branch_id,
            'guest_name'     => $guest_name,
            'contact_phone'  => $phone ?: LS_Session::get_customer_phone(),
            'subject'        => $subject,
            'description'    => $description,
            'invoice_number' => $invoice_number,
            'invoice_date'   => $invoice_date ?: null,
            'status'         => 'open',
            'priority'       => 'normal',
        ) );

        if ( ! $inserted ) {
            wp_send_json_error( array( 'message' => __( 'Could not submit ticket.', 'loyal-system' ) ), 500 );
        }

        $ticket_id = $wpdb->insert_id;

        // Handle image uploads.
        if ( ! empty( $_FILES['images'] ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            $upload_dir = wp_upload_dir();
            $ts_dir     = $upload_dir['basedir'] . '/ls-tickets/' . $ticket_id;
            wp_mkdir_p( $ts_dir );

            foreach ( $_FILES['images']['tmp_name'] as $i => $tmp ) {
                if ( empty( $tmp ) ) { continue; }
                $name      = sanitize_file_name( $_FILES['images']['name'][ $i ] );
                $dest      = $ts_dir . '/' . $name;
                if ( move_uploaded_file( $tmp, $dest ) ) {
                    $rel = '/ls-tickets/' . $ticket_id . '/' . $name;
                    $wpdb->insert( $wpdb->prefix . 'ls_ticket_images', array(
                        'ticket_id' => $ticket_id,
                        'file_name' => $name,
                        'file_path' => $rel,
                    ) );
                }
            }
        }

        // ── Email notification to support ────────────────────────────────────
        $to           = LS_Settings::support_email();
        $site         = get_bloginfo( 'name' );
        $email_subject = sprintf( '[%s] New Ticket #%d: %s', $site, $ticket_id, $subject );

        if ( $customer_id ) {
            $cust   = LS_Customer::get_by_id( $customer_id );
            $caller = $cust ? ( $cust->full_name ?: $cust->phone ) : '';
        } else {
            $caller = $guest_name ?: __( 'Guest', 'loyal-system' );
        }

        $contact = $phone ?: LS_Session::get_customer_phone();
        $body    = "New support ticket submitted.\r\n\r\n"
                 . "Ticket #:     {$ticket_id}\r\n"
                 . "Subject:      {$subject}\r\n"
                 . "From:         {$caller}\r\n"
                 . "Phone:        {$contact}\r\n";
        if ( $invoice_number ) { $body .= "Invoice #:    {$invoice_number}\r\n"; }
        if ( $invoice_date )   { $body .= "Invoice Date: {$invoice_date}\r\n"; }
        $body   .= "\r\nDescription:\r\n{$description}\r\n\r\n"
                 . "Manage tickets: " . admin_url( 'admin.php?page=ls-tickets' );

        wp_mail( $to, $email_subject, $body );

        wp_send_json_success( array(
            'message'   => __( 'Ticket submitted successfully. We will get back to you soon.', 'loyal-system' ),
            'ticket_id' => $ticket_id,
        ) );
    }

    // ── Feedback ──────────────────────────────────────────────────────────────

    public static function handle_submit_feedback() {
        self::verify_nonce();

        $type      = sanitize_key( $_POST['feedback_type'] ?? '' );
        $phone     = sanitize_text_field( wp_unslash( $_POST['phone']     ?? '' ) );
        $full_name = sanitize_text_field( wp_unslash( $_POST['full_name'] ?? '' ) );
        $comment   = sanitize_textarea_field( wp_unslash( $_POST['comment'] ?? '' ) );

        if ( ! in_array( $type, array( 'maintenance', 'delivery' ), true ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid feedback type.', 'loyal-system' ) ), 400 );
        }
        if ( empty( $phone ) ) {
            wp_send_json_error( array( 'message' => __( 'Phone number is required.', 'loyal-system' ) ), 400 );
        }

        // Collect all answer fields (prefixed with "q_")
        $answers = array();
        foreach ( $_POST as $key => $val ) {
            if ( strpos( $key, 'q_' ) === 0 ) {
                $answers[ sanitize_key( $key ) ] = sanitize_text_field( wp_unslash( $val ) );
            }
        }

        // Resolve customer if logged in or by phone
        $customer_id = 0;
        if ( LS_Session::is_customer_logged_in() ) {
            $customer_id = LS_Session::get_customer_id();
        } else {
            global $wpdb;
            $found = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}ls_customers WHERE phone = %s LIMIT 1", $phone
            ) );
            if ( $found ) { $customer_id = (int) $found; }
        }

        global $wpdb;
        $inserted = $wpdb->insert( $wpdb->prefix . 'ls_feedback', array(
            'type'        => $type,
            'customer_id' => $customer_id,
            'phone'       => $phone,
            'full_name'   => $full_name,
            'answers'     => wp_json_encode( $answers ),
            'comment'     => $comment,
        ) );

        if ( ! $inserted ) {
            wp_send_json_error( array( 'message' => __( 'Could not save feedback.', 'loyal-system' ) ), 500 );
        }

        // ── Email notification ────────────────────────────────────────────────
        $to      = LS_Settings::support_email();
        $site    = get_bloginfo( 'name' );
        $label   = $type === 'maintenance' ? 'Maintenance' : 'Delivery';
        $email_subject = sprintf( '[%s] New %s Feedback — %s', $site, $label, $phone );
        $body    = "New {$label} feedback received.\r\n\r\n"
                 . "Name:  {$full_name}\r\nPhone: {$phone}\r\n\r\nAnswers:\r\n";
        foreach ( $answers as $k => $v ) {
            $body .= str_replace( 'q_', '', $k ) . ': ' . $v . "\r\n";
        }
        if ( $comment ) {
            $body .= "\r\nComment: {$comment}";
        }
        $body .= "\r\nView feedback: " . admin_url( 'admin.php?page=ls-feedback' );
        wp_mail( $to, $email_subject, $body );

        wp_send_json_success( array( 'message' => __( 'Thank you! Your feedback has been submitted.', 'loyal-system' ) ) );
    }

    // ── Merchant feedback ─────────────────────────────────────────────────────

    public static function handle_submit_merchant_feedback() {
        self::verify_nonce();

        $phone     = sanitize_text_field( wp_unslash( $_POST['phone']      ?? '' ) );
        $full_name = sanitize_text_field( wp_unslash( $_POST['full_name']  ?? '' ) );
        $branch_id = (int) ( $_POST['branch_id'] ?? 0 );

        if ( empty( $phone ) || $phone === '+224' ) {
            wp_send_json_error( array( 'message' => __( 'Phone number is required.', 'loyal-system' ) ), 400 );
        }
        if ( ! $branch_id ) {
            wp_send_json_error( array( 'message' => __( 'Please select a branch / store.', 'loyal-system' ) ), 400 );
        }

        $q_welcoming = sanitize_text_field( wp_unslash( $_POST['q_welcoming'] ?? '' ) );
        $q_fast      = sanitize_text_field( wp_unslash( $_POST['q_fast']      ?? '' ) );
        $q_quality   = min( 10, max( 1, (int) ( $_POST['q_quality']  ?? 5 ) ) );
        $q_value     = min( 10, max( 1, (int) ( $_POST['q_value']    ?? 5 ) ) );
        $q_recommend = sanitize_text_field( wp_unslash( $_POST['q_recommend'] ?? '' ) );
        $comment     = sanitize_textarea_field( wp_unslash( $_POST['comment'] ?? '' ) );

        // Resolve customer.
        $customer_id = 0;
        if ( LS_Session::is_customer_logged_in() ) {
            $customer_id = LS_Session::get_customer_id();
        } else {
            global $wpdb;
            $found = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}ls_customers WHERE phone = %s LIMIT 1", $phone
            ) );
            if ( $found ) { $customer_id = (int) $found; }
        }

        global $wpdb;
        $inserted = $wpdb->insert( $wpdb->prefix . 'ls_feedback_merchant', array(
            'customer_id' => $customer_id,
            'phone'       => $phone,
            'full_name'   => $full_name,
            'branch_id'   => $branch_id,
            'q_welcoming' => $q_welcoming,
            'q_fast'      => $q_fast,
            'q_quality'   => $q_quality,
            'q_value'     => $q_value,
            'q_recommend' => $q_recommend,
            'comment'     => $comment,
        ) );

        if ( ! $inserted ) {
            wp_send_json_error( array( 'message' => __( 'Could not save feedback. Please try again.', 'loyal-system' ) ), 500 );
        }

        // Email notification.
        $to      = LS_Settings::support_email();
        $site    = get_bloginfo( 'name' );
        global $wpdb;
        $branch  = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}ls_branches WHERE id = %d", $branch_id ) );
        $b_name  = $branch ? $branch->name : '#' . $branch_id;
        $subject = sprintf( '[%s] Merchant Feedback — %s — %s', $site, $b_name, $phone );
        $body    = "Merchant feedback received.\r\n\r\n"
                 . "Name:        {$full_name}\r\nPhone:       {$phone}\r\nBranch:      {$b_name}\r\n\r\n"
                 . "Personnel accueillant: {$q_welcoming}\r\n"
                 . "Service rapide:        {$q_fast}\r\n"
                 . "Qualité produit:       {$q_quality}/10\r\n"
                 . "Rapport qualité/prix:  {$q_value}/10\r\n"
                 . "Recommande:            {$q_recommend}\r\n"
                 . "Commentaire:           {$comment}\r\n\r\n"
                 . "View: " . admin_url( 'admin.php?page=ls-feedback' );
        wp_mail( $to, $subject, $body );

        wp_send_json_success( array( 'message' => __( 'Thank you! Your feedback has been submitted.', 'loyal-system' ) ) );
    }

    // ── Dashboard data ────────────────────────────────────────────────────────

    public static function handle_get_balance() {
        self::verify_nonce();
        $customer_id = self::require_customer();
        $balance     = LS_Database::get_balance( $customer_id );
        wp_send_json_success( array( 'balance' => $balance ) );
    }

    public static function handle_get_ledger() {
        self::verify_nonce();
        $customer_id = self::require_customer();
        $ledger      = LS_Database::get_ledger( $customer_id );
        wp_send_json_success( $ledger );
    }

    public static function handle_get_tickets() {
        self::verify_nonce();
        $customer_id = self::require_customer();
        $tickets     = LS_Database::get_all_tickets( array( 'customer_id' => $customer_id, 'limit' => 50 ) );
        wp_send_json_success( $tickets );
    }

    public static function handle_update_profile() {
        self::verify_nonce();
        $customer_id = self::require_customer();

        $full_name = sanitize_text_field( wp_unslash( $_POST['full_name'] ?? '' ) );
        $email     = sanitize_email( wp_unslash( $_POST['email']     ?? '' ) );
        $address   = sanitize_textarea_field( wp_unslash( $_POST['address']   ?? '' ) );

        LS_Customer::update( $customer_id, array(
            'full_name' => $full_name,
            'email'     => $email,
            'address'   => $address,
        ) );

        wp_send_json_success( array( 'message' => __( 'Profile updated.', 'loyal-system' ) ) );
    }

}
