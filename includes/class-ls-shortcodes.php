<?php
/**
 * Shortcode registration and rendering.
 *
 * @package LoyalSystem
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LS_Shortcodes {

    public static function register() {
        $map = array(
            'ls_login'                => 'render_login',
            'ls_dashboard'            => 'render_dashboard',
            'ls_invoice_lookup'       => 'render_invoice_lookup',
            'ls_submit_ticket'        => 'render_submit_ticket',
            'ls_my_tickets'           => 'render_my_tickets',
            'ls_ticket_detail'        => 'render_ticket_detail',
            'ls_nav'                  => 'render_nav',
            'ls_feedback_maintenance' => 'render_feedback_maintenance',
            'ls_feedback_delivery'    => 'render_feedback_delivery',
            'ls_my_feedback'          => 'render_my_feedback',
            'ls_feedback_merchant'    => 'render_feedback_merchant',
            'ls_form_montage'         => 'render_form_montage',
            'ls_my_interventions'     => 'render_my_interventions',
        );

        foreach ( $map as $tag => $method ) {
            add_shortcode( $tag, array( __CLASS__, $method ) );
        }
    }

    // ── Shortcode handlers ────────────────────────────────────────────────────

    public static function render_login( $atts, $content = null ) {
        $redirect_url = esc_url_raw( wp_unslash( $_GET['redirect_to'] ?? '' ) );
        return self::render_template( 'login', compact( 'redirect_url' ), $content );
    }

    public static function render_dashboard( $atts, $content = null ) {
        if ( ! LS_Session::is_customer_logged_in() ) {
            return self::redirect_to_login();
        }
        return self::render_template( 'dashboard', array(), $content );
    }

    public static function render_invoice_lookup( $atts, $content = null ) {
        return self::render_template( 'invoice-lookup', array(), $content );
    }

    public static function render_submit_ticket( $atts, $content = null ) {
        $is_logged_in  = LS_Session::is_customer_logged_in();
        $customer      = $is_logged_in ? LS_Customer::get_by_id( LS_Session::get_customer_id() ) : null;
        $dashboard_id  = LS_Settings::dashboard_page_id();
        $dashboard_url = $dashboard_id ? get_permalink( $dashboard_id ) : '';
        $branches      = LS_Database::get_all_branches();
        return self::render_template( 'submit-ticket', compact( 'is_logged_in', 'customer', 'dashboard_url', 'branches' ), $content );
    }

    public static function render_my_tickets( $atts, $content = null ) {
        if ( ! LS_Session::is_customer_logged_in() ) {
            return self::redirect_to_login();
        }
        $customer_id = LS_Session::get_customer_id();
        $tickets     = LS_Database::get_all_tickets( array( 'customer_id' => $customer_id, 'limit' => 100 ) );
        return self::render_template( 'my-tickets', compact( 'tickets' ), $content );
    }

    public static function render_ticket_detail( $atts, $content = null ) {
        // ── Auth check first ─────────────────────────────────────────────────
        if ( ! LS_Session::is_customer_logged_in() ) {
            return self::render_template( 'login-required', array(), $content );
        }

        $ticket_id = isset( $_GET['ticket_id'] ) ? (int) $_GET['ticket_id'] : 0;
        if ( ! $ticket_id ) {
            return '<p class="ls-error">' . esc_html__( 'No ticket specified.', 'loyal-system' ) . '</p>';
        }

        $ticket = LS_Database::get_ticket_with_images( $ticket_id );
        if ( ! $ticket ) {
            return '<p class="ls-error">' . esc_html__( 'Ticket not found.', 'loyal-system' ) . '</p>';
        }

        // ── Ownership check ───────────────────────────────────────────────────
        $customer_id = LS_Session::get_customer_id();
        $owns_by_id    = ( (int) $ticket->customer_id === $customer_id );
        $customer_phone = LS_Session::get_customer_phone();
        $owns_by_phone = ( (int) $ticket->customer_id === 0 && $customer_phone && $ticket->contact_phone === $customer_phone );
        if ( ! $owns_by_id && ! $owns_by_phone ) {
            return '<p class="ls-error">' . esc_html__( 'You do not have permission to view this ticket.', 'loyal-system' ) . '</p>';
        }

        return self::render_template( 'ticket-detail', compact( 'ticket' ), $content );
    }

    public static function render_nav( $atts, $content = null ) {
        $is_logged_in = LS_Session::is_customer_logged_in();
        $customer     = $is_logged_in ? LS_Customer::get_current() : null;
        $current_url  = home_url( add_query_arg( array() ) );

        $page_ids = array(
            'dashboard'            => LS_Settings::dashboard_page_id(),
            'submit_ticket'        => LS_Settings::submit_ticket_page_id(),
            'my_tickets'           => LS_Settings::my_tickets_page_id(),
            'my_feedback'          => LS_Settings::my_feedback_page_id(),
            'feedback_maintenance' => LS_Settings::feedback_maintenance_page_id(),
            'feedback_delivery'    => LS_Settings::feedback_delivery_page_id(),
            'feedback_merchant'    => LS_Settings::feedback_merchant_page_id(),
        );

        // Build nav items.
        $nav_items = array(
            array( 'label' => __( 'Tableau de bord',           'loyal-system' ), 'page_id' => $page_ids['dashboard'],            'icon' => '&#127968;' ),
            array( 'label' => __( 'Mes tickets',               'loyal-system' ), 'page_id' => $page_ids['my_tickets'],           'icon' => '&#127916;' ),
            array( 'label' => __( 'Soumettre un ticket',       'loyal-system' ), 'page_id' => $page_ids['submit_ticket'],        'icon' => '&#128196;' ),
            array( 'label' => __( 'Mes avis',                  'loyal-system' ), 'page_id' => $page_ids['my_feedback'],          'icon' => '&#11088;' ),
            array( 'label' => __( 'Avis maintenance',          'loyal-system' ), 'page_id' => $page_ids['feedback_maintenance'], 'icon' => '&#128295;' ),
            array( 'label' => __( 'Avis livraison',            'loyal-system' ), 'page_id' => $page_ids['feedback_delivery'],    'icon' => '&#128666;' ),
            array( 'label' => __( 'Avis magasin',              'loyal-system' ), 'page_id' => $page_ids['feedback_merchant'],    'icon' => '&#127978;' ),
        );

        $login_url = LS_Settings::login_url();
        $current_id = 0;
        foreach ( $page_ids as $id ) {
            if ( $id && get_permalink( $id ) && trailingslashit( get_permalink( $id ) ) === trailingslashit( $current_url ) ) {
                $current_id = $id;
                break;
            }
        }

        /**
         * Closure: return URL for nav item, with redirect_to for guests.
         */
        $nav_url = function( $page_id ) use ( $is_logged_in, $login_url ) {
            if ( ! $page_id ) {
                return '#';
            }
            $dest = get_permalink( $page_id );
            if ( $is_logged_in ) {
                return $dest;
            }
            return add_query_arg( 'redirect_to', rawurlencode( $dest ), $login_url );
        };

        return self::render_template( 'nav', compact( 'is_logged_in', 'customer', 'nav_items', 'nav_url', 'current_id', 'page_ids', 'login_url' ), $content );
    }

    // ── Feedback shortcodes ────────────────────────────────────────────────────

    public static function render_feedback_merchant( $atts, $content = null ) {
        $is_logged_in  = LS_Session::is_customer_logged_in();
        $customer      = $is_logged_in ? LS_Customer::get_current() : null;
        $dashboard_url = LS_Settings::dashboard_page_id()
            ? get_permalink( LS_Settings::dashboard_page_id() ) : '';
        $branches      = LS_Database::get_all_branches();
        return self::render_template( 'feedback-merchant', compact( 'is_logged_in', 'customer', 'dashboard_url', 'branches' ), $content );
    }

    public static function render_my_feedback( $atts, $content = null ) {
        if ( ! LS_Session::is_customer_logged_in() ) {
            return self::redirect_to_login();
        }

        $customer_id    = LS_Session::get_customer_id();
        $customer_phone = LS_Session::get_customer_phone();

        // Fetch by customer_id; if none found by ID, fall back to phone for guest submissions.
        $feedback = LS_Database::get_feedback( array( 'customer_id' => $customer_id, 'limit' => 100 ) );
        if ( empty( $feedback ) && $customer_phone ) {
            $feedback = LS_Database::get_feedback( array( 'phone' => $customer_phone, 'limit' => 100 ) );
        }

        $maintenance_url = LS_Settings::feedback_maintenance_page_id()
            ? get_permalink( LS_Settings::feedback_maintenance_page_id() ) : '#';
        $delivery_url    = LS_Settings::feedback_delivery_page_id()
            ? get_permalink( LS_Settings::feedback_delivery_page_id() ) : '#';
        $montage_url     = LS_Settings::form_montage_page_id()
            ? get_permalink( LS_Settings::form_montage_page_id() ) : '#';
        $dashboard_url   = LS_Settings::dashboard_page_id()
            ? get_permalink( LS_Settings::dashboard_page_id() ) : '';

        return self::render_template( 'my-feedback', compact( 'feedback', 'maintenance_url', 'delivery_url', 'montage_url', 'dashboard_url' ), $content );
    }

    public static function render_feedback_maintenance( $atts, $content = null ) {
        $is_logged_in  = LS_Session::is_customer_logged_in();
        $customer      = $is_logged_in ? LS_Customer::get_current() : null;
        $dashboard_url = ( $is_logged_in && LS_Settings::dashboard_page_id() )
            ? get_permalink( LS_Settings::dashboard_page_id() ) : '';
        return self::render_template( 'feedback-maintenance', compact( 'is_logged_in', 'customer', 'dashboard_url' ), $content );
    }

    public static function render_feedback_delivery( $atts, $content = null ) {
        $is_logged_in  = LS_Session::is_customer_logged_in();
        $customer      = $is_logged_in ? LS_Customer::get_current() : null;
        $dashboard_url = ( $is_logged_in && LS_Settings::dashboard_page_id() )
            ? get_permalink( LS_Settings::dashboard_page_id() ) : '';
        return self::render_template( 'feedback-delivery', compact( 'is_logged_in', 'customer', 'dashboard_url' ), $content );
    }

    public static function render_form_montage( $atts, $content = null ) {
        $is_logged_in  = LS_Session::is_customer_logged_in();
        $customer      = $is_logged_in ? LS_Customer::get_current() : null;
        $dashboard_url = ( $is_logged_in && LS_Settings::dashboard_page_id() )
            ? get_permalink( LS_Settings::dashboard_page_id() ) : '';
        return self::render_template( 'form-montage', compact( 'is_logged_in', 'customer', 'dashboard_url' ), $content );
    }

    public static function render_my_interventions( $atts, $content = null ) {
        if ( ! LS_Session::is_customer_logged_in() ) {
            return self::redirect_to_login();
        }
        global $wpdb;
        $customer_id   = LS_Session::get_customer_id();
        $interventions = LS_Database::get_interventions( array( 'customer_id' => $customer_id, 'limit' => 100 ) );
        $dashboard_url = LS_Settings::dashboard_page_id()
            ? get_permalink( LS_Settings::dashboard_page_id() ) : '';
        return self::render_template( 'my-interventions', compact( 'interventions', 'dashboard_url' ), $content );
    }

    /**
     * Render a template file with extracted variables.
     *
     * @param  string $template  Template name without .php
     * @param  array  $vars      Variables to extract into template scope.
     * @param  mixed  $content   Shortcode enclosed content.
     * @return string
     */
    public static function render_template( $template, $vars = array(), $content = null ) {
        $file = LS_PLUGIN_DIR . 'public/templates/' . $template . '.php';
        if ( ! file_exists( $file ) ) {
            return '';
        }

        $is_logged_in = LS_Session::is_customer_logged_in();
        $customer     = $is_logged_in ? LS_Customer::get_current() : null;
        $redirect_url = '';

        extract( array_merge( array(
            'is_logged_in' => $is_logged_in,
            'customer'     => $customer,
            'redirect_url' => $redirect_url,
            'content'      => $content,
        ), $vars ) );

        ob_start();
        include $file;
        return ob_get_clean();
    }

    /**
     * Return an inline redirect to the login page.
     */
    private static function redirect_to_login() {
        $login_url = LS_Settings::login_url();
        $dest      = home_url( add_query_arg( array() ) );
        $url       = add_query_arg( 'redirect_to', rawurlencode( $dest ), $login_url );

        return '<script>window.location.href=' . wp_json_encode( $url ) . ';</script>'
             . '<p>' . esc_html__( 'Redirecting to sign in…', 'loyal-system' ) . '</p>';
    }
}
