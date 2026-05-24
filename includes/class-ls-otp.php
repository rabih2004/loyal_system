<?php
/**
 * OTP generation and verification.
 *
 * @package LoyalSystem
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LS_OTP {

    /**
     * Generate a 6-digit OTP for a phone number, store it in the DB, and return the code.
     *
     * @param  string $phone
     * @return string|false  The OTP code, or false on failure.
     */
    public static function generate( $phone ) {
        global $wpdb;
        $p = $wpdb->prefix . 'ls_';

        // Remove any existing OTPs for this phone.
        $wpdb->delete( "{$p}otp", array( 'phone' => $phone ) );

        $code       = (string) wp_rand( 100000, 999999 );
        $expires_at = gmdate( 'Y-m-d H:i:s', time() + LS_Settings::otp_expiry() * 60 );

        $inserted = $wpdb->insert( "{$p}otp", array(
            'phone'      => $phone,
            'code'       => $code,
            'expires_at' => $expires_at,
            'created_at' => gmdate( 'Y-m-d H:i:s' ),
        ) );

        return $inserted ? $code : false;
    }

    /**
     * Verify an OTP code for a phone number.
     *
     * @param  string $phone
     * @param  string $code
     * @return bool
     */
    public static function verify( $phone, $code ) {
        global $wpdb;
        $p = $wpdb->prefix . 'ls_';

        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$p}otp WHERE phone = %s AND code = %s AND expires_at > UTC_TIMESTAMP()",
            $phone, $code
        ) );

        if ( ! $row ) {
            return false;
        }

        // Consume the OTP.
        $wpdb->delete( "{$p}otp", array( 'id' => $row->id ) );

        return true;
    }
}
