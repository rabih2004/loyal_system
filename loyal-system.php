<?php

/**
 * Plugin Name: Loyal System
 * Plugin URI:  #
 * Description: Customer loyalty points, invoice management, OTP phone login, and maintenance tickets.
 * Version:     1.2.1
 * Author:      Custom
 * Text Domain: loyal-system
 *
 * @package LoyalSystem
 */

if (! defined('ABSPATH')) {
    exit;
}

define('LS_VERSION',    '1.2.1');
define('LS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LS_PLUGIN_URL', plugin_dir_url(__FILE__));

// ── Core includes ────────────────────────────────────────────────────────────
foreach (
    array(
        'class-ls-database',
        'class-ls-session',
        'class-ls-settings',
        'class-ls-otp',
        'class-ls-sms',
        'class-ls-customer',
        'class-ls-invoice',
        'class-ls-roles',
        'class-ls-shortcodes',
    ) as $file
) {
    require_once LS_PLUGIN_DIR . 'includes/' . $file . '.php';
}

require_once LS_PLUGIN_DIR . 'admin/class-ls-admin.php';
require_once LS_PLUGIN_DIR . 'admin/class-ls-admin-ajax.php';
require_once LS_PLUGIN_DIR . 'public/class-ls-public.php';
require_once LS_PLUGIN_DIR . 'public/class-ls-public-ajax.php';

// ── Activation ───────────────────────────────────────────────────────────────
register_activation_hook(__FILE__, function () {
    LS_Database::install();
    update_option('ls_db_version', LS_VERSION);
    LS_Roles::create_roles();
});

// ── DB upgrade on load ────────────────────────────────────────────────────────
add_action('plugins_loaded', function () {
    if (get_option('ls_db_version') !== LS_VERSION) {
        LS_Database::install();
        update_option('ls_db_version', LS_VERSION);
    }
});


// ── Bootstrap ────────────────────────────────────────────────────────────────
add_action('init', array('LS_Session',    'start'));
add_action('init', array('LS_Shortcodes', 'register'));
add_action('init', array('LS_Public',     'handle_logout'));

add_action('admin_menu',             array('LS_Admin', 'register_menus'));
add_action('admin_enqueue_scripts',  array('LS_Admin', 'enqueue_assets'));
add_action('wp_enqueue_scripts',     array('LS_Public', 'enqueue_assets'));

LS_Admin_Ajax::init();
LS_Public_Ajax::init();

// ── Invoice staff: redirect to admin on login (both WP and WooCommerce forms) ──
function ls_staff_redirect_url($redirect_to, $user_or_second = null, $user_or_third = null)
{
    // login_redirect passes ( $redirect_to, $requested, $user )
    // woocommerce_login_redirect passes ( $redirect_to, $user )
    $user = $user_or_third instanceof WP_User ? $user_or_third : $user_or_second;
    if ($user instanceof WP_User && in_array('invoice_staff', (array) $user->roles, true)) {
        return admin_url('admin.php?page=ls-invoices');
    }
    return $redirect_to;
}
add_filter('login_redirect',              'ls_staff_redirect_url', 999, 3);
add_filter('woocommerce_login_redirect',  'ls_staff_redirect_url', 999, 2);

// ── Invoice staff: allow wp-admin access (bypass WooCommerce block) ───────────
add_filter('woocommerce_prevent_admin_access', function ($prevent) {
    if (current_user_can('ls_manage_invoices')) {
        return false;
    }
    return $prevent;
});

// ── Portal pages: add body class + hide page title ────────────────────────────
add_action('wp', function () {
    $portal_ids = array_filter([
        LS_Settings::login_page_id(),
        LS_Settings::dashboard_page_id(),
        LS_Settings::submit_ticket_page_id(),
        LS_Settings::my_tickets_page_id(),
        LS_Settings::ticket_view_page_id(),
        LS_Settings::feedback_maintenance_page_id(),
        LS_Settings::feedback_delivery_page_id(),
        LS_Settings::my_feedback_page_id(),
        LS_Settings::feedback_merchant_page_id(),
        LS_Settings::form_montage_page_id(),
        LS_Settings::my_interventions_page_id(),
    ]);
    if (is_page($portal_ids)) {
        add_filter('body_class', function ($classes) {
            $classes[] = 'ls-portal-page';
            return $classes;
        });
        // Hide the WordPress page title in the loop.
        add_filter('the_title', function ($title, $id) {
            if (in_the_loop() && is_main_query() && $id === get_queried_object_id()) {
                return '';
            }
            return $title;
        }, 10, 2);
    }
});
