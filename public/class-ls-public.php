<?php
/**
 * Public-facing hooks: asset enqueuing, logout handling.
 *
 * @package LoyalSystem
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LS_Public {

    public static function enqueue_assets() {
        wp_enqueue_style(
            'ls-public',
            LS_PLUGIN_URL . 'public/assets/css/ls-public.css',
            array(),
            LS_VERSION
        );

        wp_enqueue_script(
            'ls-public',
            LS_PLUGIN_URL . 'public/assets/js/ls-public.js',
            array( 'jquery' ),
            LS_VERSION,
            true
        );

        wp_localize_script( 'ls-public', 'lsPublic', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'ls_public_nonce' ),
            'i18n'    => array(
                'error'   => __( 'An unexpected error occurred.', 'loyal-system' ),
                'loading' => __( 'Loading…', 'loyal-system' ),
            ),
        ) );
    }

    /**
     * Handle ?ls_action=logout (nonce-verified).
     */
    public static function handle_logout() {
        if (
            isset( $_GET['ls_action'] ) && 'logout' === $_GET['ls_action'] &&
            isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ls_logout' )
        ) {
            LS_Session::destroy();
            $redirect = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : home_url( '/' );
            wp_safe_redirect( $redirect );
            exit;
        }
    }
}
