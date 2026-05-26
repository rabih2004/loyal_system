<?php
/**
 * Settings helper — reads from the 'ls_settings' WP option.
 *
 * @package LoyalSystem
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LS_Settings {

    private static $cache = null;

    public static function get_all() {
        if ( null === self::$cache ) {
            self::$cache = (array) get_option( 'ls_settings', array() );
        }
        return self::$cache;
    }

    public static function get( $key, $default = '' ) {
        $all = self::get_all();
        return isset( $all[ $key ] ) ? $all[ $key ] : $default;
    }

    public static function save( array $data ) {
        $current = self::get_all();
        $merged  = array_merge( $current, $data );
        update_option( 'ls_settings', $merged );
        self::$cache = $merged;
    }

    // ── Convenience getters ──────────────────────────────────────────────────

    public static function support_email()           { return self::get( 'support_email', get_option( 'admin_email' ) ); }
    public static function ticket_sms_message()                { return self::get( 'ticket_sms_message', '' ); }
    public static function feedback_maintenance_sms_message() { return self::get( 'feedback_maintenance_sms_message', '' ); }
    public static function feedback_delivery_sms_message()    { return self::get( 'feedback_delivery_sms_message', '' ); }
    public static function feedback_montage_sms_message()     { return self::get( 'feedback_montage_sms_message', '' ); }
    public static function feedback_merchant_sms_message()    { return self::get( 'feedback_merchant_sms_message', '' ); }
    public static function sms_provider()         { return self::get( 'sms_provider', 'test' ); }
    public static function twilio_sid()            { return self::get( 'twilio_sid', '' ); }
    public static function twilio_token()          { return self::get( 'twilio_token', '' ); }
    public static function twilio_from()           { return self::get( 'twilio_from', '' ); }
    public static function http_sms_url()              { return self::get( 'http_sms_url', '' ); }
    public static function http_sms_params()           { return self::get( 'http_sms_params', '' ); }
    public static function orangesmspro_token()        { return self::get( 'orangesmspro_token', '' ); }
    public static function orangesmspro_signature_id() { return (int) self::get( 'orangesmspro_signature_id', 0 ); }
    public static function orangeapi_auth_key()         { return self::get( 'orangeapi_auth_key', '' ); }
    public static function orangeapi_sender()           { return self::get( 'orangeapi_sender', '+2240000' ); }
    public static function orangeapi_sender_name()      { return self::get( 'orangeapi_sender_name', '' ); }
    public static function otp_expiry()            { return (int) self::get( 'otp_expiry_minutes', 10 ); }
    public static function otp_resend_cooldown()   { return (int) self::get( 'otp_resend_cooldown', 60 ); }
    public static function invoice_credit_pct()        { return (float) self::get( 'invoice_credit_pct', 0 ); }
    public static function discount_rate()             { return (float) self::get( 'discount_rate', 0 ); }
    public static function default_invoice_currency()  { return strtoupper( self::get( 'default_invoice_currency', 'GNF' ) ); }

    // ── Portal page IDs (stored as individual options) ───────────────────────

    public static function login_page_id()                  { return (int) get_option( 'ls_login_page_id', 0 ); }
    public static function dashboard_page_id()              { return (int) get_option( 'ls_dashboard_page_id', 0 ); }
    public static function submit_ticket_page_id()          { return (int) get_option( 'ls_submit_ticket_page_id', 0 ); }
    public static function my_tickets_page_id()             { return (int) get_option( 'ls_my_tickets_page_id', 0 ); }
    public static function ticket_view_page_id()            { return (int) get_option( 'ls_ticket_view_page_id', 0 ); }
    public static function feedback_maintenance_page_id()   { return (int) get_option( 'ls_feedback_maintenance_page_id', 0 ); }
    public static function feedback_delivery_page_id()      { return (int) get_option( 'ls_feedback_delivery_page_id', 0 ); }
    public static function my_feedback_page_id()            { return (int) get_option( 'ls_my_feedback_page_id', 0 ); }
    public static function feedback_merchant_page_id()      { return (int) get_option( 'ls_feedback_merchant_page_id', 0 ); }
    public static function form_montage_page_id()           { return (int) get_option( 'ls_form_montage_page_id', 0 ); }
    public static function my_interventions_page_id()       { return (int) get_option( 'ls_my_interventions_page_id', 0 ); }

    public static function login_url() {
        $id = self::login_page_id();
        return $id ? get_permalink( $id ) : home_url( '/client-signin/' );
    }
}
