<?php
/**
 * SMS sending — routes to the configured provider.
 *
 * @package LoyalSystem
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LS_SMS {

    /**
     * Send an OTP code via SMS.
     *
     * @param  string $phone
     * @param  string $code
     * @return true|WP_Error
     */
    public static function send_otp( $phone, $code ) {
        $provider = LS_Settings::sms_provider();
        $message  = sprintf( __( 'Your verification code is: %s', 'loyal-system' ), $code );

        switch ( $provider ) {
            case 'twilio':
                return self::send_twilio( $phone, $message );
            case 'http':
                return self::send_http( $phone, $message, $code );
            case 'orangesmspro':
                return self::send_orangesmspro( $phone, $message );
            case 'orangeapi':
                return self::send_orangeapi( $phone, $message );
            case 'test':
            default:
                return self::send_test( $phone, $code );
        }
    }

    /**
     * Send a plain text message via the configured provider.
     *
     * @param  string $phone
     * @param  string $message
     * @return true|WP_Error
     */
    public static function send( $phone, $message ) {
        $provider = LS_Settings::sms_provider();

        switch ( $provider ) {
            case 'twilio':
                return self::send_twilio( $phone, $message );
            case 'http':
                return self::send_http( $phone, $message, '' );
            case 'orangesmspro':
                return self::send_orangesmspro( $phone, $message );
            case 'orangeapi':
                return self::send_orangeapi( $phone, $message );
            case 'test':
            default:
                error_log( "[LS SMS TEST] Phone: {$phone} | Message: {$message}" );
                return true;
        }
    }

    /**
     * Send via Twilio REST API.
     */
    private static function send_twilio( $phone, $message ) {
        $sid   = LS_Settings::twilio_sid();
        $token = LS_Settings::twilio_token();
        $from  = LS_Settings::twilio_from();

        if ( empty( $sid ) || empty( $token ) || empty( $from ) ) {
            return new WP_Error( 'ls_sms_config', __( 'Twilio is not fully configured.', 'loyal-system' ) );
        }

        $url  = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
        $resp = wp_remote_post( $url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( "{$sid}:{$token}" ),
            ),
            'body'    => array(
                'From' => $from,
                'To'   => $phone,
                'Body' => $message,
            ),
            'timeout' => 15,
        ) );

        if ( is_wp_error( $resp ) ) {
            return $resp;
        }

        $code = wp_remote_retrieve_response_code( $resp );
        if ( $code < 200 || $code >= 300 ) {
            $body = json_decode( wp_remote_retrieve_body( $resp ), true );
            $msg  = isset( $body['message'] ) ? $body['message'] : __( 'Twilio request failed.', 'loyal-system' );
            return new WP_Error( 'ls_sms_twilio', $msg );
        }

        return true;
    }

    /**
     * Send via generic HTTP gateway.
     * Params string supports {phone} and {code} placeholders.
     */
    private static function send_http( $phone, $message, $code ) {
        $url    = LS_Settings::http_sms_url();
        $params = LS_Settings::http_sms_params();

        if ( empty( $url ) ) {
            return new WP_Error( 'ls_sms_config', __( 'HTTP SMS gateway URL is not configured.', 'loyal-system' ) );
        }

        $params = str_replace( array( '{phone}', '{code}', '{message}' ), array( rawurlencode( $phone ), $code, rawurlencode( $message ) ), $params );
        parse_str( $params, $body );

        $resp = wp_remote_post( $url, array(
            'body'    => $body,
            'timeout' => 15,
        ) );

        if ( is_wp_error( $resp ) ) {
            return $resp;
        }

        return true;
    }

    /**
     * Send via Orange SMS Pro (orangesmspro.sn).
     */
    private static function send_orangesmspro( $phone, $message ) {
        $token        = LS_Settings::orangesmspro_token();
        $signature_id = LS_Settings::orangesmspro_signature_id();

        if ( empty( $token ) || empty( $signature_id ) ) {
            return new WP_Error( 'ls_sms_config', __( 'Orange SMS Pro is not fully configured.', 'loyal-system' ) );
        }

        // Strip leading + for the API (it expects digits only)
        $recipient = ltrim( $phone, '+' );

        $body = array(
            'sendingDate' => '',
            'sendingTime' => '',
            'sendingType' => 'I',
            'message'     => array(
                'subject'     => 'OTP',
                'signatureId' => $signature_id,
                'recipients'  => $recipient,
                'groupes'     => '',
                'groupNames'  => '',
                'content'     => $message,
            ),
            'msgAction' => 'START',
        );

        $resp = wp_remote_post(
            'https://orangesmspro.sn/apisms/websms/api/my-messages/send-simple-message',
            array(
                'headers'   => array(
                    'Content-Type'    => 'application/json',
                    'Accept'          => 'application/json, text/plain, */*',
                    'Accept-Language' => 'fr',
                    'Authorization'   => 'Bearer ' . $token,
                    'Origin'          => 'https://www.orangesmspro.sn',
                    'Referer'         => 'https://www.orangesmspro.sn/',
                    'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:149.0) Gecko/20100101 Firefox/149.0',
                ),
                'body'      => wp_json_encode( $body ),
                'timeout'   => 20,
                'sslverify' => false,
            )
        );

        if ( is_wp_error( $resp ) ) {
            return $resp;
        }

        $http_code = wp_remote_retrieve_response_code( $resp );
        if ( $http_code < 200 || $http_code >= 300 ) {
            $parsed = json_decode( wp_remote_retrieve_body( $resp ), true );
            $msg    = isset( $parsed['message'] ) ? $parsed['message'] : sprintf( __( 'Orange SMS Pro request failed (HTTP %d).', 'loyal-system' ), $http_code );
            return new WP_Error( 'ls_sms_orangesmspro', $msg );
        }

        return true;
    }

    /**
     * Send via Orange Developer API (developer.orange.com).
     * Uses OAuth2 client-credentials flow; access token is cached for its TTL.
     * Docs: https://developer.orange.com/apis/sms/getting-started
     */
    private static function send_orangeapi( $phone, $message ) {
        $auth_key    = LS_Settings::orangeapi_auth_key();
        $sender_num  = LS_Settings::orangeapi_sender();      // country sender number, e.g. +2240000
        $sender_name = LS_Settings::orangeapi_sender_name(); // optional text name, max 11 chars

        if ( empty( $auth_key ) || empty( $sender_num ) ) {
            return new WP_Error( 'ls_sms_config', __( 'Orange API is not fully configured.', 'loyal-system' ) );
        }

        $token = self::orangeapi_get_token( $auth_key );
        if ( is_wp_error( $token ) ) {
            return $token;
        }

        // Normalise to tel:+XXXXXXX format.
        $to   = 'tel:' . ( str_starts_with( $phone,      'tel:' ) ? substr( $phone,      4 ) : $phone );
        $from = 'tel:' . ( str_starts_with( $sender_num, 'tel:' ) ? substr( $sender_num, 4 ) : $sender_num );

        $endpoint = 'https://api.orange.com/smsmessaging/v1/outbound/' . rawurlencode( $from ) . '/requests';

        $payload = array(
            'address'                => $to,
            'senderAddress'          => $from,
            'outboundSMSTextMessage' => array( 'message' => $message ),
        );

        if ( ! empty( $sender_name ) ) {
            $payload['senderName'] = substr( $sender_name, 0, 11 ); // API limit: 11 chars
        }

        $resp = wp_remote_post( $endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ),
            'body'    => wp_json_encode( array( 'outboundSMSMessageRequest' => $payload ) ),
            'timeout' => 20,
        ) );

        if ( is_wp_error( $resp ) ) {
            return $resp;
        }

        $http_code = wp_remote_retrieve_response_code( $resp );
        if ( 201 !== $http_code ) {
            $parsed = json_decode( wp_remote_retrieve_body( $resp ), true );
            $msg    = isset( $parsed['requestError']['serviceException']['text'] )
                ? $parsed['requestError']['serviceException']['text']
                : sprintf( __( 'Orange API request failed (HTTP %d).', 'loyal-system' ), $http_code );
            return new WP_Error( 'ls_sms_orangeapi', $msg );
        }

        return true;
    }

    /**
     * Fetch (or return cached) OAuth2 access token for the Orange Developer API.
     *
     * @param  string $auth_key  Base64(client_id:client_secret)
     * @return string|WP_Error
     */
    private static function orangeapi_get_token( $auth_key ) {
        $transient_key = 'ls_orangeapi_token_' . substr( md5( $auth_key ), 0, 8 );
        $cached        = get_transient( $transient_key );
        if ( $cached ) {
            return $cached;
        }

        $resp = wp_remote_post( 'https://api.orange.com/oauth/v3/token', array(
            'headers' => array(
                'Authorization' => 'Basic ' . $auth_key,
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'Accept'        => 'application/json',
            ),
            'body'    => 'grant_type=client_credentials',
            'timeout' => 15,
        ) );

        if ( is_wp_error( $resp ) ) {
            return $resp;
        }

        $http_code = wp_remote_retrieve_response_code( $resp );
        $parsed    = json_decode( wp_remote_retrieve_body( $resp ), true );

        if ( $http_code < 200 || $http_code >= 300 || empty( $parsed['access_token'] ) ) {
            $msg = isset( $parsed['error_description'] ) ? $parsed['error_description'] : __( 'Orange API token request failed.', 'loyal-system' );
            return new WP_Error( 'ls_sms_orangeapi_auth', $msg );
        }

        $ttl = isset( $parsed['expires_in'] ) ? max( (int) $parsed['expires_in'] - 60, 60 ) : 3540;
        set_transient( $transient_key, $parsed['access_token'], $ttl );

        return $parsed['access_token'];
    }

    /**
     * Test mode — log the code instead of sending it.
     */
    private static function send_test( $phone, $code ) {
        error_log( "[TS OTP TEST] Phone: {$phone} | Code: {$code}" );
        return true;
    }
}
