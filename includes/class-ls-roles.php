<?php
/**
 * Custom WordPress roles and capability checks.
 *
 * @package LoyalSystem
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LS_Roles {

    public static function create_roles() {
        remove_role( 'invoice_staff' );

        add_role( 'invoice_staff', __( 'Invoice Staff', 'loyal-system' ), array(
            'read'                 => true,
            'view_admin_dashboard' => true,
            'ls_manage_invoices'   => true,
        ) );
    }

    /**
     * Can the current WP user access the TS admin pages?
     */
    public static function current_user_can_access() {
        return current_user_can( 'manage_options' )
            || current_user_can( 'ls_manage_invoices' );
    }

    public static function is_staff_only() {
        return ! current_user_can( 'manage_options' )
            && current_user_can( 'ls_manage_invoices' );
    }
}
