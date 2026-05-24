# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Plugin Overview

**loyal-system** is a fully custom WordPress plugin providing customer loyalty points, invoice management, OTP-based phone login, support tickets, and feedback collection. It is **not** a WooCommerce extension — it operates independently with its own DB tables, session system, and frontend portal. The main plugin file is [loyal-system.php](loyal-system.php).

**DB table prefix:** `wp_ls_*` (not the WordPress `fur_` prefix used by other tables).  
**No build process** — CSS/JS assets in `admin/assets/` and `public/assets/` are edited directly.

---

## Database Tables

| Table | Purpose |
|---|---|
| `wp_ls_customers` | Phone (unique), name, email, address, password hash, is_verified |
| `wp_ls_branches` | Store branches |
| `wp_ls_invoices` | Invoice ref, amount (stored in GNF), currency, discount, file, branch, created_by (WP user) |
| `wp_ls_ledger` | Credits journal: type ENUM(`credit`/`debit`), balance_after, description — balance is computed via SUM, not cached |
| `wp_ls_otp` | 6-digit code, expires_at, phone — no attempt limiting |
| `wp_ls_feedback` | Maintenance/delivery feedback; `answers` column is JSON |
| `wp_ls_feedback_merchant` | 1–10 ratings per dimension (welcoming, fast, quality, value, recommend) + comment |
| `wp_ls_ticket_categories` | Ticket category labels |
| `wp_ls_tickets` | Status ENUM(`open`/`in_progress`/`resolved`/`closed`), priority, admin_notes, invoice_number/date |
| `wp_ls_ticket_images` | File paths per ticket (not WP attachment IDs) |

Schema is created/upgraded in [includes/class-ls-database.php](includes/class-ls-database.php) via `dbDelta`. Activation calls `LS_Database::install()`; version mismatch on `plugins_loaded` triggers auto-upgrade.

---

## Settings Storage

Two distinct storage patterns — do not conflate them:

1. **`ls_settings` WP option** (array) — all scalar plugin settings: SMS credentials, `invoice_credit_pct`, `discount_rate`, `otp_expiry_minutes`, `otp_resend_cooldown`, `default_invoice_currency`, `support_email`. Read via `LS_Settings` static getters.

2. **Individual WP options** (9 separate options) — portal page IDs, stored separately so `get_permalink(get_option('ls_login_page_id'))` survives slug changes:
   - `ls_login_page_id`, `ls_dashboard_page_id`, `ls_submit_ticket_page_id`, `ls_my_tickets_page_id`, `ls_ticket_view_page_id`, `ls_feedback_maintenance_page_id`, `ls_feedback_delivery_page_id`, `ls_my_feedback_page_id`, `ls_feedback_merchant_page_id`

---

## Shortcodes

All shortcodes are registered in [includes/class-ls-shortcodes.php](includes/class-ls-shortcodes.php) and load templates from [public/templates/](public/templates/).

| Shortcode | Template | Purpose |
|---|---|---|
| `[ls_login]` | `login.php` | Multi-step: phone → OTP → optional set-password |
| `[ls_dashboard]` | `dashboard.php` | Balance + ledger history |
| `[ls_invoice_lookup]` | `invoice-lookup.php` | Guest invoice search |
| `[ls_submit_ticket]` | `submit-ticket.php` | Ticket form (guests + logged-in) |
| `[ls_my_tickets]` | `my-tickets.php` | Customer's ticket list |
| `[ls_ticket_detail]` | `ticket-detail.php` | Single ticket (`?ticket_id=N`), ownership verified |
| `[ls_nav]` | `nav.php` | Portal navigation bar |
| `[ls_feedback_maintenance]` | `feedback-maintenance.php` | Maintenance feedback form |
| `[ls_feedback_delivery]` | `feedback-delivery.php` | Delivery feedback form |
| `[ls_feedback_merchant]` | `feedback-merchant.php` | Store/merchant rating form |
| `[ls_my_feedback]` | `my-feedback.php` | Customer's feedback history |

---

## Auth & Session Flow

`LS_Session` ([includes/class-ls-session.php](includes/class-ls-session.php)) maintains a PHP session **independent of WordPress user accounts** — keys `ls_customer_id` and `ls_customer_phone` in `$_SESSION`. Customers do not need a WP user account.

OTP login path:
1. `ls_request_otp` AJAX → find-or-create customer → generate 6-digit OTP → store in `wp_ls_otp` → send via configured SMS provider
2. `ls_verify_otp` AJAX → verify code + expiry → start session → if no password set, return `needs_password: true` → JS shows set-password step → then redirect
3. `ls_set_password` AJAX → `wp_hash_password()` → sets `is_verified = 1`

Login redirect order: `?redirect_to=` URL param → `lsLoginData.redirectUrl` (PHP-localized) → page reload.

All phone inputs default to `+224` (Guinea country code).

---

## Points & Invoice Credits

- Invoices store amounts in **GNF** (Guinean franc). Multi-currency: amounts in other currencies are converted via `GNF = raw_amount / rate`, where `rate` comes from the **WBW Currency Switcher** (`wcu_currencies` WP option).
- On invoice creation, a ledger `credit` entry is auto-created for `invoice_amount_gnf × invoice_credit_pct / 100`.
- Staff can also apply a redemption (debit) when creating/updating an invoice.
- Customer balance = `SUM(amount WHERE type='credit') - SUM(amount WHERE type='debit')` — computed live, never cached.
- Invoice files uploaded to `/wp-content/uploads/ls-invoices/{invoice_id}/`.

---

## SMS Providers

Configured via `ls_settings.sms_provider`. Routing in [includes/class-ls-sms.php](includes/class-ls-sms.php):

| Provider key | Auth method | Notes |
|---|---|---|
| `twilio` | Basic auth (SID:token) | Standard REST |
| `http` | POST params | Custom URL; supports `{phone}`, `{code}`, `{message}` placeholders |
| `orangesmspro` | Bearer token | `orangesmspro.sn`; strips leading `+` from phone |
| `orangeapi` | OAuth2 client-credentials | Token cached as WP transient; expects HTTP 201; sender name max 11 chars |
| `test` | — | Logs OTP via `error_log`, no actual send (default) |

---

## AJAX Handlers & Nonces

**Admin** ([admin/class-ls-admin-ajax.php](admin/class-ls-admin-ajax.php)):
- Nonce: `ls_admin_nonce`, localized as `lsAdmin.nonce`
- Handles: invoices CRUD, customer search/update/delete, branches CRUD, ticket categories CRUD, ticket status updates, test SMS, ledger queries

**Public** ([public/class-ls-public-ajax.php](public/class-ls-public-ajax.php)):
- Nonce: `ls_public_nonce`, localized as `lsPublic.nonce` / `lsLoginData.nonce`
- No-priv: `ls_request_otp`, `ls_verify_otp`, `ls_customer_login`, `ls_set_password`, `ls_customer_logout`, `ls_submit_ticket`, `ls_submit_feedback`, `ls_submit_merchant_feedback`
- Session-gated: `ls_get_balance`, `ls_get_ledger`, `ls_get_tickets`, `ls_update_profile`

---

## Custom Role & Admin Access

**`invoice_staff` role** — capability: `ls_manage_invoices`. Staff users:
- See only their own admin menu items (Invoices, Credits)
- Can only edit invoices they created (`created_by` ownership check)
- Bypass WooCommerce's admin access block (`woocommerce_prevent_admin_access` filter)
- Redirect to `admin.php?page=ls-invoices` on WP login

`LS_Roles::current_user_can_access()` — true for admins (`manage_options`) or staff (`ls_manage_invoices`).  
`LS_Roles::is_staff_only()` — true for staff without admin capabilities.

---

## Integration Points

- **WBW Currency Switcher** — reads `wcu_currencies` WP option for exchange rates (required for multi-currency invoices)
- **WooCommerce** — hooks `woocommerce_prevent_admin_access` and `woocommerce_login_redirect` for staff role; no other WooCommerce dependency
- **WordPress mail** — `wp_mail()` for ticket email notifications to `support_email`
- **WordPress `wp_hash_password()`** — used for customer password hashing (not `password_hash()`)
