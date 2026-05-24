<?php
/**
 * Invoice model — create invoices with currency conversion.
 *
 * @package LoyalSystem
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LS_Invoice {

    /**
     * Add a new invoice.  If the currency differs from the site default (GNF),
     * the amount is converted to GNF before saving.
     *
     * @param  array $data {
     *   customer_id, branch_id, amount, currency, invoice_ref, invoice_date, notes, created_by
     * }
     * @return int|WP_Error  Invoice ID or error.
     */
    public static function add( array $data ) {
        global $wpdb;
        $p = $wpdb->prefix . 'ls_';

        $customer_id = (int) ( $data['customer_id'] ?? 0 );
        $amount      = (float) ( $data['amount'] ?? 0 );
        $currency    = strtoupper( sanitize_text_field( $data['currency'] ?? '' ) );

        if ( ! $currency ) {
            $currency = self::get_default_currency();
        }

        // ── Currency conversion to base (GNF) ──────────────────────────────
        $base_currency = self::get_default_currency();
        if ( $currency !== $base_currency && $amount > 0 ) {
            $all_currencies = self::get_available_currencies();
            $rate           = isset( $all_currencies[ $currency ]['rate'] ) ? (float) $all_currencies[ $currency ]['rate'] : 0;
            if ( $rate > 0 ) {
                $amount = round( $amount / $rate, 2 );
            }
            $currency = $base_currency;
        }

        // ── Discount (credits redeemed by staff on this invoice) ────────────
        $discount_amount = (float) ( $data['discount_amount'] ?? 0 );

        $invoice_ref = sanitize_text_field( $data['invoice_ref'] ?? '' );

        $inserted = $wpdb->insert( "{$p}invoices", array(
            'customer_id'     => $customer_id,
            'branch_id'       => (int) ( $data['branch_id'] ?? 0 ),
            'amount'          => $amount,
            'currency'        => $currency,
            'discount_amount' => $discount_amount,
            'invoice_ref'     => $invoice_ref,
            'invoice_date'    => $data['invoice_date'] ?? current_time( 'Y-m-d' ),
            'notes'           => sanitize_textarea_field( $data['notes'] ?? '' ),
            'created_by'      => (int) ( $data['created_by'] ?? get_current_user_id() ),
            'status'          => 'active',
        ) );

        if ( ! $inserted ) {
            return new WP_Error( 'ls_invoice_fail', __( 'Failed to save invoice.', 'loyal-system' ) );
        }

        $invoice_id = $wpdb->insert_id;

        // ── Credit ledger entry (% of invoice amount) ───────────────────────
        $points = LS_Settings::invoice_credit_pct() > 0
            ? round( $amount * LS_Settings::invoice_credit_pct() / 100, 2 )
            : 0;
        if ( $points > 0 ) {
            LS_Database::add_ledger_entry(
                $customer_id,
                $invoice_id,
                $points,
                'credit',
                sprintf( __( 'Invoice #%d loyalty credit', 'loyal-system' ), $invoice_id )
            );
        }

        return $invoice_id;
    }

    /**
     * Get all currencies from the WBW Currency Switcher option, including their rate.
     * Rate convention: 1 GNF = rate units of that currency  →  GNF = amount / rate.
     *
     * @return array  [ 'USD' => ['name'=>..., 'symbol'=>..., 'rate'=>...], ... ]
     */
    public static function get_available_currencies() {
        $data = get_option( 'wcu_currencies', array() );
        if ( empty( $data ) || ! is_array( $data ) ) {
            return array();
        }

        $currencies = array();
        foreach ( $data as $code => $info ) {
            if ( ! is_array( $info ) ) {
                continue;
            }
            $currencies[ $code ] = array(
                'name'   => $info['name']   ?? $code,
                'symbol' => $info['symbol'] ?? $code,
                'rate'   => (float) ( $info['rate'] ?? 0 ),
            );
        }

        return $currencies;
    }

    /**
     * Return the default invoice currency (from TS Settings, defaults to 'GNF').
     *
     * @return string
     */
    public static function get_default_currency() {
        return LS_Settings::default_invoice_currency();
    }
}
