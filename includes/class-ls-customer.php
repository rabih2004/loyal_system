<?php
/**
 * Customer model — CRUD, OTP auth, password auth.
 *
 * @package LoyalSystem
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LS_Customer {

    // ── Retrieval ─────────────────────────────────────────────────────────────

    public static function get_by_id( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ls_customers WHERE id = %d",
            $id
        ) );
    }

    public static function get_by_phone( $phone ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ls_customers WHERE phone = %s",
            $phone
        ) );
    }

    /**
     * Get the currently logged-in customer (from PHP session).
     *
     * @return object|null
     */
    public static function get_current() {
        $id = LS_Session::get_customer_id();
        return $id ? self::get_by_id( $id ) : null;
    }

    // ── Create / Update ────────────────────────────────────────────────────────

    public static function create( $phone, $full_name = '' ) {
        global $wpdb;
        $wpdb->insert( $wpdb->prefix . 'ls_customers', array(
            'phone'     => $phone,
            'full_name' => $full_name,
        ) );
        return $wpdb->insert_id;
    }

    public static function update( $customer_id, array $data ) {
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'ls_customers',
            $data,
            array( 'id' => $customer_id )
        );
    }

    // ── OTP auth ──────────────────────────────────────────────────────────────

    /**
     * Find or create a customer and send OTP.
     *
     * @param  string $phone
     * @param  string $name  Optional name for new customers.
     * @return true|WP_Error
     */
    public static function request_otp( $phone, $name = '' ) {
        $customer = self::get_by_phone( $phone );

        if ( ! $customer ) {
            $id = self::create( $phone, $name );
            if ( ! $id ) {
                return new WP_Error( 'ls_create_fail', __( 'Could not create customer account.', 'loyal-system' ) );
            }
        } elseif ( $name && empty( $customer->full_name ) ) {
            self::update( $customer->id, array( 'full_name' => $name ) );
        }

        $code = LS_OTP::generate( $phone );
        if ( ! $code ) {
            return new WP_Error( 'ls_otp_fail', __( 'Could not generate verification code.', 'loyal-system' ) );
        }

        $result = LS_SMS::send_otp( $phone, $code );
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        // Return the code so callers can expose it in test mode.
        return $code;
    }

    /**
     * Verify OTP and start a portal session.
     *
     * @param  string $phone
     * @param  string $code
     * @return true|WP_Error
     */
    public static function verify_otp_and_login( $phone, $code ) {
        $ok = LS_OTP::verify( $phone, $code );

        if ( ! $ok ) {
            return new WP_Error( 'ls_otp_invalid', __( 'Invalid or expired code. Please try again.', 'loyal-system' ) );
        }

        $customer = self::get_by_phone( $phone );
        if ( ! $customer ) {
            return new WP_Error( 'ls_no_customer', __( 'Account not found.', 'loyal-system' ) );
        }

        LS_Session::login_customer( $customer->id, $customer->phone );

        return true;
    }

    // ── Password auth ─────────────────────────────────────────────────────────

    /**
     * Attempt password login.
     *
     * @param  string $phone
     * @param  string $password
     * @return object|WP_Error  Customer object on success.
     */
    public static function login_with_password( $phone, $password ) {
        $customer = self::get_by_phone( $phone );

        if ( ! $customer || empty( $customer->password_hash ) ) {
            return new WP_Error( 'ls_pw_invalid', __( 'Invalid phone number or password.', 'loyal-system' ) );
        }

        if ( ! wp_check_password( $password, $customer->password_hash ) ) {
            return new WP_Error( 'ls_pw_invalid', __( 'Invalid phone number or password.', 'loyal-system' ) );
        }

        LS_Session::login_customer( $customer->id, $customer->phone );

        return $customer;
    }

    /**
     * Set (hash and save) a password for a customer.
     *
     * @param  int    $customer_id
     * @param  string $password
     * @return bool
     */
    public static function set_password( $customer_id, $password ) {
        global $wpdb;
        $hash   = wp_hash_password( $password );
        $result = $wpdb->update(
            $wpdb->prefix . 'ls_customers',
            array( 'password_hash' => $hash, 'is_verified' => 1 ),
            array( 'id' => $customer_id )
        );
        return $result !== false;
    }
}
