<?php
/**
 * PHP session wrapper for customer portal authentication.
 *
 * @package LoyalSystem
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LS_Session {

    public static function start() {
        if ( ! session_id() ) {
            session_start();
        }
    }

    public static function login_customer( $customer_id, $phone ) {
        self::start();
        $_SESSION['ls_customer_id']    = (int) $customer_id;
        $_SESSION['ls_customer_phone'] = sanitize_text_field( $phone );
    }

    public static function is_customer_logged_in() {
        self::start();
        return ! empty( $_SESSION['ls_customer_id'] );
    }

    public static function get_customer_id() {
        self::start();
        return isset( $_SESSION['ls_customer_id'] ) ? (int) $_SESSION['ls_customer_id'] : 0;
    }

    public static function get_customer_phone() {
        self::start();
        return isset( $_SESSION['ls_customer_phone'] ) ? $_SESSION['ls_customer_phone'] : '';
    }

    public static function destroy() {
        self::start();
        unset( $_SESSION['ls_customer_id'], $_SESSION['ls_customer_phone'] );
    }
}
