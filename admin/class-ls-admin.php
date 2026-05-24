<?php
/**
 * Admin menus, pages, and settings handling.
 *
 * @package LoyalSystem
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LS_Admin {

    // ── Menus ─────────────────────────────────────────────────────────────────

    public static function register_menus() {
        if ( ! LS_Roles::current_user_can_access() ) {
            return;
        }

        $top_page     = current_user_can( 'manage_options' ) ? 'ls-dashboard' : 'ls-invoices';
        $top_callback = current_user_can( 'manage_options' ) ? array( __CLASS__, 'render_dashboard_page' ) : array( __CLASS__, 'render_invoices_page' );

        add_menu_page(
            __( 'Loyal System', 'loyal-system' ),
            __( 'Loyal System', 'loyal-system' ),
            'read',
            $top_page,
            $top_callback,
            'dashicons-chart-bar',
            56
        );

        // Full menu only for admins (not invoice_staff).
        if ( current_user_can( 'manage_options' ) ) {
            add_submenu_page( 'ls-dashboard', __( 'Dashboard',   'loyal-system' ), __( 'Dashboard',   'loyal-system' ), 'manage_options', 'ls-dashboard',   array( __CLASS__, 'render_dashboard_page' ) );
            add_submenu_page( 'ls-dashboard', __( 'Invoices',    'loyal-system' ), __( 'Invoices',    'loyal-system' ), 'read',           'ls-invoices',    array( __CLASS__, 'render_invoices_page' ) );
            add_submenu_page( 'ls-dashboard', __( 'Credits',     'loyal-system' ), __( 'Credits',     'loyal-system' ), 'read',           'ls-credits',     array( __CLASS__, 'render_credits_page' ) );
            add_submenu_page( 'ls-dashboard', __( 'Tickets',     'loyal-system' ), __( 'Tickets',     'loyal-system' ), 'manage_options', 'ls-tickets',     array( __CLASS__, 'render_tickets_page' ) );
            add_submenu_page( 'ls-dashboard', __( 'Customers',   'loyal-system' ), __( 'Customers',   'loyal-system' ), 'manage_options', 'ls-customers',   array( __CLASS__, 'render_customers_page' ) );
            add_submenu_page( 'ls-dashboard', __( 'Branches',    'loyal-system' ), __( 'Branches',    'loyal-system' ), 'manage_options', 'ls-branches',    array( __CLASS__, 'render_branches_page' ) );
            add_submenu_page( 'ls-dashboard', __( 'Categories',  'loyal-system' ), __( 'Categories',  'loyal-system' ), 'manage_options', 'ls-categories',  array( __CLASS__, 'render_categories_page' ) );
            add_submenu_page( 'ls-dashboard', __( 'Feedback',    'loyal-system' ), __( 'Feedback',    'loyal-system' ), 'manage_options', 'ls-feedback',    array( __CLASS__, 'render_feedback_page' ) );
            add_submenu_page( 'ls-dashboard', __( 'Settings',    'loyal-system' ), __( 'Settings',    'loyal-system' ), 'manage_options', 'ls-settings',    array( __CLASS__, 'render_settings_page' ) );
        } else {
            // Staff: only invoices + credits
            add_submenu_page( 'ls-invoices', __( 'Invoices', 'loyal-system' ), __( 'Invoices', 'loyal-system' ), 'read', 'ls-invoices', array( __CLASS__, 'render_invoices_page' ) );
            add_submenu_page( 'ls-invoices', __( 'Credits',  'loyal-system' ), __( 'Credits',  'loyal-system' ), 'read', 'ls-credits',  array( __CLASS__, 'render_credits_page' ) );
        }
    }

    // ── Asset enqueuing ────────────────────────────────────────────────────────

    public static function enqueue_assets( $hook ) {
        // Match top-level page or any subpage whose slug starts with ls-
        if ( 'toplevel_page_ls-dashboard' !== $hook && 'toplevel_page_ls-invoices' !== $hook && strpos( $hook, '_page_ls-' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'ls-admin',
            LS_PLUGIN_URL . 'admin/assets/css/ls-admin.css',
            array(),
            LS_VERSION
        );

        // Chart.js — only on dashboard
        if ( strpos( $hook, 'ls-dashboard' ) !== false || $hook === 'toplevel_page_ls-dashboard' ) {
            wp_enqueue_script( 'chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js', array(), '4', true );
        }

        wp_enqueue_script(
            'ls-admin',
            LS_PLUGIN_URL . 'admin/assets/js/ls-admin.js',
            array( 'jquery' ),
            LS_VERSION,
            true
        );

        wp_localize_script( 'ls-admin', 'lsAdmin', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'ls_admin_nonce' ),
            'i18n'    => array(
                'error'          => __( 'An unexpected error occurred.', 'loyal-system' ),
                'confirm_delete' => __( 'Are you sure you want to delete this?', 'loyal-system' ),
                'saved'          => __( 'Saved successfully.', 'loyal-system' ),
            ),
        ) );
    }

    // ── Page renderers ─────────────────────────────────────────────────────────

    public static function render_dashboard_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Access denied.', 'loyal-system' ) );
        }
        $stats = LS_Database::get_dashboard_stats();
        include LS_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    public static function render_invoices_page() {
        if ( ! LS_Roles::current_user_can_access() ) {
            wp_die( esc_html__( 'Access denied.', 'loyal-system' ) );
        }

        $search        = sanitize_text_field( wp_unslash( $_GET['s']     ?? '' ) );
        $paged         = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
        $per_page      = 50;
        $currencies    = LS_Invoice::get_available_currencies();
        $default_cur   = LS_Invoice::get_default_currency();
        $branches      = LS_Database::get_all_branches();
        $discount_rate = LS_Settings::discount_rate();
        $created_by_filter = LS_Roles::is_staff_only() ? get_current_user_id() : 0;
        $invoices      = LS_Database::get_invoices( array(
            'search'     => $search,
            'limit'      => $per_page,
            'offset'     => ( $paged - 1 ) * $per_page,
            'created_by' => $created_by_filter,
        ) );
        $total_invoices = LS_Database::count_invoices( array( 'search' => $search, 'created_by' => $created_by_filter ) );

        include LS_PLUGIN_DIR . 'admin/views/invoices.php';
    }

    public static function render_credits_page() {
        if ( ! LS_Roles::current_user_can_access() ) {
            wp_die( esc_html__( 'Access denied.', 'loyal-system' ) );
        }

        include LS_PLUGIN_DIR . 'admin/views/credits.php';
    }

    public static function render_tickets_page() {
        if ( ! LS_Roles::current_user_can_access() ) {
            wp_die( esc_html__( 'Access denied.', 'loyal-system' ) );
        }

        $status   = sanitize_key( $_GET['status'] ?? '' );
        $search   = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
        $paged    = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
        $per_page = 50;
        $tickets  = LS_Database::get_all_tickets( array(
            'status' => $status,
            'search' => $search,
            'limit'  => $per_page,
            'offset' => ( $paged - 1 ) * $per_page,
        ) );
        $statuses = array(
            'open'        => __( 'Open',        'loyal-system' ),
            'in_progress' => __( 'In Progress', 'loyal-system' ),
            'resolved'    => __( 'Resolved',    'loyal-system' ),
            'closed'      => __( 'Closed',      'loyal-system' ),
        );

        include LS_PLUGIN_DIR . 'admin/views/tickets.php';
    }

    public static function render_customers_page() {
        if ( ! LS_Roles::current_user_can_access() ) {
            wp_die( esc_html__( 'Access denied.', 'loyal-system' ) );
        }

        $search   = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
        $paged    = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
        $per_page = 50;
        $customers = LS_Database::get_all_customers( array(
            'search' => $search,
            'limit'  => $per_page,
            'offset' => ( $paged - 1 ) * $per_page,
        ) );
        $total = LS_Database::count_customers( $search );

        include LS_PLUGIN_DIR . 'admin/views/customers.php';
    }

    public static function render_branches_page() {
        if ( ! LS_Roles::current_user_can_access() ) {
            wp_die( esc_html__( 'Access denied.', 'loyal-system' ) );
        }

        $branches = LS_Database::get_all_branches();
        include LS_PLUGIN_DIR . 'admin/views/branches.php';
    }

    public static function render_feedback_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Access denied.', 'loyal-system' ) );
        }
        include LS_PLUGIN_DIR . 'admin/views/feedback.php';
    }

    public static function render_categories_page() {
        if ( ! LS_Roles::current_user_can_access() ) {
            wp_die( esc_html__( 'Access denied.', 'loyal-system' ) );
        }

        $categories = LS_Database::get_all_categories();
        include LS_PLUGIN_DIR . 'admin/views/categories.php';
    }

    public static function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Access denied.', 'loyal-system' ) );
        }

        $saved   = false;
        $message = '';

        if ( isset( $_POST['ls_settings_nonce'] ) && wp_verify_nonce( $_POST['ls_settings_nonce'], 'ls_save_settings' ) ) {
            self::handle_settings_save( $_POST );
            $saved   = true;
            $message = __( 'Settings saved.', 'loyal-system' );
        }

        $settings     = LS_Settings::get_all();
        $all_pages    = get_pages( array( 'post_status' => 'publish' ) );
        $portal_pages = array(
            'ls_login_page_id'                => __( 'Customer Login Page',       'loyal-system' ),
            'ls_dashboard_page_id'            => __( 'Customer Dashboard',        'loyal-system' ),
            'ls_submit_ticket_page_id'        => __( 'Submit Ticket Page',        'loyal-system' ),
            'ls_my_tickets_page_id'           => __( 'My Tickets Page',           'loyal-system' ),
            'ls_ticket_view_page_id'          => __( 'Ticket View Page',          'loyal-system' ),
            'ls_feedback_maintenance_page_id' => __( 'Maintenance Feedback Page', 'loyal-system' ),
            'ls_feedback_delivery_page_id'    => __( 'Delivery Feedback Page',    'loyal-system' ),
            'ls_my_feedback_page_id'          => __( 'My Feedback Page',          'loyal-system' ),
            'ls_feedback_merchant_page_id'    => __( 'Merchant Feedback Page',    'loyal-system' ),
        );

        include LS_PLUGIN_DIR . 'admin/views/settings.php';
    }

    // ── Settings save ─────────────────────────────────────────────────────────

    private static function handle_settings_save( array $data ) {
        // Scalar settings.
        LS_Settings::save( array(
            'support_email'       => sanitize_email( $data['support_email']     ?? '' ),
            'sms_provider'        => sanitize_key( $data['sms_provider']        ?? 'test' ),
            'twilio_sid'          => sanitize_text_field( $data['twilio_sid']   ?? '' ),
            'twilio_token'        => sanitize_text_field( $data['twilio_token'] ?? '' ),
            'twilio_from'         => sanitize_text_field( $data['twilio_from']  ?? '' ),
            'http_sms_url'             => esc_url_raw( $data['http_sms_url']             ?? '' ),
            'http_sms_params'          => sanitize_text_field( $data['http_sms_params']  ?? '' ),
            'orangesmspro_token'        => sanitize_textarea_field( $data['orangesmspro_token']         ?? '' ),
            'orangesmspro_signature_id' => (int) ( $data['orangesmspro_signature_id'] ?? 0 ),
            'orangeapi_auth_key'        => sanitize_text_field( $data['orangeapi_auth_key']             ?? '' ),
            'orangeapi_sender'          => sanitize_text_field( $data['orangeapi_sender']               ?? '' ),
            'orangeapi_sender_name'     => sanitize_text_field( $data['orangeapi_sender_name']          ?? '' ),
            'otp_expiry_minutes'  => (int) ( $data['otp_expiry_minutes']        ?? 10 ),
            'otp_resend_cooldown' => (int) ( $data['otp_resend_cooldown']       ?? 60 ),
            'invoice_credit_pct'       => (float) ( $data['invoice_credit_pct']            ?? 0 ),
            'discount_rate'            => (float) ( $data['discount_rate']               ?? 0 ),
            'default_invoice_currency' => strtoupper( sanitize_key( $data['default_invoice_currency'] ?? 'GNF' ) ),
        ) );

        // Portal page IDs — stored as individual WP options.
        // Keys must match the name="" attribute used by wp_dropdown_pages() in the view.
        $portal_page_options = array(
            'ls_login_page_id',
            'ls_dashboard_page_id',
            'ls_submit_ticket_page_id',
            'ls_my_tickets_page_id',
            'ls_ticket_view_page_id',
            'ls_feedback_maintenance_page_id',
            'ls_feedback_delivery_page_id',
            'ls_my_feedback_page_id',
            'ls_feedback_merchant_page_id',
        );

        foreach ( $portal_page_options as $option_name ) {
            if ( isset( $data[ $option_name ] ) ) {
                update_option( $option_name, (int) $data[ $option_name ] );
            }
        }
    }
}
