<?php
/**
 * Database schema installation and shared query methods.
 *
 * @package LoyalSystem
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LS_Database {

    // ── Schema ────────────────────────────────────────────────────────────────

    public static function install() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $p       = $wpdb->prefix . 'ls_';

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( "
        CREATE TABLE {$p}customers (
            id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
            phone         VARCHAR(30)  NOT NULL,
            full_name     VARCHAR(150) NOT NULL DEFAULT '',
            email         VARCHAR(150) NOT NULL DEFAULT '',
            address       TEXT         NOT NULL DEFAULT '',
            password_hash VARCHAR(255) NOT NULL DEFAULT '',
            is_verified   TINYINT(1)   NOT NULL DEFAULT 0,
            created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY phone (phone)
        ) $charset;" );

        dbDelta( "
        CREATE TABLE {$p}branches (
            id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name       VARCHAR(150) NOT NULL,
            address    TEXT         NOT NULL DEFAULT '',
            phone      VARCHAR(30)  NOT NULL DEFAULT '',
            created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset;" );

        dbDelta( "
        CREATE TABLE {$p}invoices (
            id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
            customer_id    INT UNSIGNED NOT NULL DEFAULT 0,
            branch_id      INT UNSIGNED NOT NULL DEFAULT 0,
            amount         DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            currency       VARCHAR(10)  NOT NULL DEFAULT 'GNF',
            discount_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            file_path      VARCHAR(500) NOT NULL DEFAULT '',
            invoice_ref    VARCHAR(100) NOT NULL DEFAULT '',
            invoice_date   DATE         DEFAULT NULL,
            notes          TEXT         NOT NULL DEFAULT '',
            created_by     BIGINT       NOT NULL DEFAULT 0,
            status         VARCHAR(20)  NOT NULL DEFAULT 'active',
            created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY customer_id (customer_id)
        ) $charset;" );

        dbDelta( "
        CREATE TABLE {$p}ledger (
            id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
            customer_id   INT UNSIGNED NOT NULL,
            invoice_id    INT UNSIGNED NOT NULL DEFAULT 0,
            amount        DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            type          ENUM('credit','debit') NOT NULL DEFAULT 'credit',
            balance_after DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            description   TEXT         NOT NULL DEFAULT '',
            created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY customer_id (customer_id)
        ) $charset;" );

        dbDelta( "
        CREATE TABLE {$p}otp (
            id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
            phone      VARCHAR(30)  NOT NULL,
            code       VARCHAR(10)  NOT NULL,
            expires_at DATETIME     NOT NULL,
            created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY phone (phone)
        ) $charset;" );

        dbDelta( "
        CREATE TABLE {$p}feedback (
            id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
            type         VARCHAR(50)  NOT NULL DEFAULT 'maintenance',
            customer_id  INT UNSIGNED NOT NULL DEFAULT 0,
            phone        VARCHAR(30)  NOT NULL DEFAULT '',
            full_name    VARCHAR(150) NOT NULL DEFAULT '',
            answers      LONGTEXT     NOT NULL DEFAULT '',
            comment      TEXT         NOT NULL DEFAULT '',
            submitted_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY type (type)
        ) $charset;" );

        dbDelta( "
        CREATE TABLE {$p}feedback_merchant (
            id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
            customer_id  INT UNSIGNED NOT NULL DEFAULT 0,
            phone        VARCHAR(30)  NOT NULL DEFAULT '',
            full_name    VARCHAR(150) NOT NULL DEFAULT '',
            branch_id    INT UNSIGNED NOT NULL DEFAULT 0,
            q_welcoming  VARCHAR(100) NOT NULL DEFAULT '',
            q_fast       VARCHAR(100) NOT NULL DEFAULT '',
            q_quality    VARCHAR(100) NOT NULL DEFAULT '',
            q_value      VARCHAR(100) NOT NULL DEFAULT '',
            q_recommend  VARCHAR(100) NOT NULL DEFAULT '',
            comment      TEXT         NOT NULL DEFAULT '',
            submitted_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY customer_id (customer_id)
        ) $charset;" );

        // Migrate existing installs: widen merchant feedback columns for text answers.
        $wpdb->query( "ALTER TABLE {$p}feedback_merchant MODIFY COLUMN q_welcoming VARCHAR(100) NOT NULL DEFAULT ''" );
        $wpdb->query( "ALTER TABLE {$p}feedback_merchant MODIFY COLUMN q_fast      VARCHAR(100) NOT NULL DEFAULT ''" );
        $wpdb->query( "ALTER TABLE {$p}feedback_merchant MODIFY COLUMN q_quality   VARCHAR(100) NOT NULL DEFAULT ''" );
        $wpdb->query( "ALTER TABLE {$p}feedback_merchant MODIFY COLUMN q_value     VARCHAR(100) NOT NULL DEFAULT ''" );
        $wpdb->query( "ALTER TABLE {$p}feedback_merchant MODIFY COLUMN q_recommend VARCHAR(100) NOT NULL DEFAULT ''" );

        dbDelta( "
        CREATE TABLE {$p}ticket_categories (
            id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name        VARCHAR(150) NOT NULL,
            description TEXT         NOT NULL DEFAULT '',
            created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset;" );

        dbDelta( "
        CREATE TABLE {$p}tickets (
            id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
            customer_id    INT UNSIGNED NOT NULL DEFAULT 0,
            category_id    INT UNSIGNED NOT NULL DEFAULT 0,
            branch_id      INT UNSIGNED NOT NULL DEFAULT 0,
            guest_name     VARCHAR(150) NOT NULL DEFAULT '',
            contact_phone  VARCHAR(30)  NOT NULL DEFAULT '',
            subject        VARCHAR(255) NOT NULL,
            description    TEXT         NOT NULL DEFAULT '',
            invoice_number VARCHAR(100) NOT NULL DEFAULT '',
            invoice_date   DATE         DEFAULT NULL,
            status         VARCHAR(20)  NOT NULL DEFAULT 'open',
            priority       VARCHAR(20)  NOT NULL DEFAULT 'normal',
            admin_notes    TEXT         NOT NULL DEFAULT '',
            created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY customer_id (customer_id),
            KEY status (status)
        ) $charset;" );

        dbDelta( "
        CREATE TABLE {$p}ticket_images (
            id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
            ticket_id  INT UNSIGNED NOT NULL,
            file_name  VARCHAR(255) NOT NULL DEFAULT '',
            file_path  VARCHAR(500) NOT NULL DEFAULT '',
            created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY ticket_id (ticket_id)
        ) $charset;" );

        dbDelta( "
        CREATE TABLE {$p}pickups (
            id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
            category     VARCHAR(50)  NOT NULL DEFAULT '',
            name         VARCHAR(150) NOT NULL,
            phone        VARCHAR(30)  NOT NULL DEFAULT '',
            plate_number VARCHAR(50)  NOT NULL DEFAULT '',
            created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset;" );
        $wpdb->query( "ALTER TABLE {$p}pickups ADD COLUMN IF NOT EXISTS category VARCHAR(50) NOT NULL DEFAULT '' AFTER id" );

        dbDelta( "
        CREATE TABLE {$p}interventions (
            id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
            pickup_id       INT UNSIGNED NOT NULL DEFAULT 0,
            customer_id     INT UNSIGNED NOT NULL DEFAULT 0,
            type            VARCHAR(30)  NOT NULL DEFAULT 'livraison',
            attachment_path VARCHAR(500) NOT NULL DEFAULT '',
            scheduled_at    DATETIME     NOT NULL,
            branch_id       INT UNSIGNED NOT NULL DEFAULT 0,
            status          VARCHAR(30)  NOT NULL DEFAULT 'pending',
            notes           TEXT         NOT NULL DEFAULT '',
            created_by      BIGINT       NOT NULL DEFAULT 0,
            created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY customer_id (customer_id),
            KEY pickup_id   (pickup_id),
            KEY status      (status)
        ) $charset;" );
    }

    // ── Balance ────────────────────────────────────────────────────────────────

    public static function get_balance( $customer_id ) {
        global $wpdb;
        $p = $wpdb->prefix . 'ls_';

        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT SUM( CASE WHEN type='credit' THEN amount ELSE -amount END ) AS balance
               FROM {$p}ledger WHERE customer_id = %d",
            $customer_id
        ) );

        return $row ? (float) $row->balance : 0.0;
    }

    // ── Ledger ────────────────────────────────────────────────────────────────

    public static function add_ledger_entry( $customer_id, $invoice_id, $amount, $type = 'credit', $description = '' ) {
        global $wpdb;
        $p       = $wpdb->prefix . 'ls_';
        $balance = self::get_balance( $customer_id );
        $balance_after = ( $type === 'credit' ) ? $balance + $amount : $balance - $amount;

        $wpdb->insert( "{$p}ledger", array(
            'customer_id'   => $customer_id,
            'invoice_id'    => $invoice_id,
            'amount'        => $amount,
            'type'          => $type,
            'balance_after' => $balance_after,
            'description'   => $description,
        ) );

        return $wpdb->insert_id;
    }

    public static function get_ledger( $customer_id, $limit = 50, $offset = 0 ) {
        global $wpdb;
        $p = $wpdb->prefix . 'ls_';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT l.*, i.file_path AS invoice_file_path
             FROM {$p}ledger l
             LEFT JOIN {$p}invoices i ON i.id = l.invoice_id AND l.invoice_id > 0
             WHERE l.customer_id = %d
             ORDER BY l.created_at DESC
             LIMIT %d OFFSET %d",
            $customer_id, $limit, $offset
        ) );
    }

    // ── Invoices ──────────────────────────────────────────────────────────────

    public static function get_invoices( $args = array() ) {
        global $wpdb;
        $p = $wpdb->prefix . 'ls_';

        $defaults = array( 'customer_id' => 0, 'search' => '', 'limit' => 50, 'offset' => 0, 'created_by' => 0 );
        $args     = wp_parse_args( $args, $defaults );

        $where = '1=1';
        $vals  = array();

        if ( ! empty( $args['customer_id'] ) ) {
            $where .= ' AND i.customer_id = %d';
            $vals[] = $args['customer_id'];
        }

        if ( ! empty( $args['created_by'] ) ) {
            $where .= ' AND i.created_by = %d';
            $vals[] = $args['created_by'];
        }

        if ( ! empty( $args['search'] ) ) {
            $like    = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $where  .= ' AND ( c.phone LIKE %s OR c.full_name LIKE %s OR i.invoice_ref LIKE %s )';
            $vals[]  = $like;
            $vals[]  = $like;
            $vals[]  = $like;
        }

        $vals[] = $args['limit'];
        $vals[] = $args['offset'];

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT i.*, c.full_name, c.phone AS customer_phone, b.name AS branch_name
               FROM {$p}invoices i
          LEFT JOIN {$p}customers c ON c.id = i.customer_id
          LEFT JOIN {$p}branches  b ON b.id = i.branch_id
              WHERE $where
           ORDER BY i.created_at DESC
              LIMIT %d OFFSET %d",
            ...$vals
        ) );
    }

    public static function count_invoices( $args = array() ) {
        global $wpdb;
        $p = $wpdb->prefix . 'ls_';

        $where = '1=1';
        $vals  = array();

        if ( ! empty( $args['customer_id'] ) ) {
            $where .= ' AND i.customer_id = %d';
            $vals[] = $args['customer_id'];
        }

        if ( ! empty( $args['created_by'] ) ) {
            $where .= ' AND i.created_by = %d';
            $vals[] = $args['created_by'];
        }

        if ( ! empty( $args['search'] ) ) {
            $like    = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $where  .= ' AND ( c.phone LIKE %s OR c.full_name LIKE %s OR i.invoice_ref LIKE %s )';
            $vals[]  = $like;
            $vals[]  = $like;
            $vals[]  = $like;
        }

        if ( empty( $vals ) ) {
            return (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM {$p}invoices i LEFT JOIN {$p}customers c ON c.id = i.customer_id WHERE $where"
            );
        }

        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$p}invoices i LEFT JOIN {$p}customers c ON c.id = i.customer_id WHERE $where",
            ...$vals
        ) );
    }

    // ── Tickets ────────────────────────────────────────────────────────────────

    public static function get_all_tickets( $args = array() ) {
        global $wpdb;
        $p = $wpdb->prefix . 'ls_';

        $defaults = array( 'status' => '', 'search' => '', 'limit' => 50, 'offset' => 0, 'customer_id' => 0 );
        $args     = wp_parse_args( $args, $defaults );

        $where = '1=1';
        $vals  = array();

        if ( ! empty( $args['status'] ) ) {
            $where .= ' AND t.status = %s';
            $vals[] = $args['status'];
        }
        if ( ! empty( $args['customer_id'] ) ) {
            $where .= ' AND t.customer_id = %d';
            $vals[] = $args['customer_id'];
        }
        if ( ! empty( $args['search'] ) ) {
            $where .= ' AND (t.subject LIKE %s OR c.full_name LIKE %s OR t.contact_phone LIKE %s)';
            $like   = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $vals[] = $like;
            $vals[] = $like;
            $vals[] = $like;
        }

        $vals[] = $args['limit'];
        $vals[] = $args['offset'];

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT t.*, c.full_name AS customer_name,
                    COALESCE(c.phone, t.contact_phone) AS contact_phone
               FROM {$p}tickets t
          LEFT JOIN {$p}customers c ON (t.customer_id > 0 AND c.id = t.customer_id)
                                    OR (t.customer_id = 0 AND c.phone = t.contact_phone)
              WHERE $where
           ORDER BY t.created_at DESC
              LIMIT %d OFFSET %d",
            ...$vals
        ) );
    }

    public static function get_ticket_with_images( $ticket_id ) {
        global $wpdb;
        $p = $wpdb->prefix . 'ls_';

        $ticket = $wpdb->get_row( $wpdb->prepare(
            "SELECT t.*, c.full_name AS customer_name
               FROM {$p}tickets t
          LEFT JOIN {$p}customers c ON (t.customer_id > 0 AND c.id = t.customer_id)
                                    OR (t.customer_id = 0 AND c.phone = t.contact_phone)
              WHERE t.id = %d",
            $ticket_id
        ) );

        if ( $ticket ) {
            $ticket->images = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$p}ticket_images WHERE ticket_id = %d ORDER BY id ASC",
                $ticket_id
            ) );
        }

        return $ticket;
    }

    public static function get_ticket_images( $ticket_id ) {
        global $wpdb;
        $p = $wpdb->prefix . 'ls_';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$p}ticket_images WHERE ticket_id = %d ORDER BY id ASC",
            $ticket_id
        ) );
    }

    public static function get_all_branches() {
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ls_branches ORDER BY name ASC" );
    }

    public static function get_all_categories() {
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ls_ticket_categories ORDER BY name ASC" );
    }

    // ── Customers ─────────────────────────────────────────────────────────────

    public static function get_all_customers( $args = array() ) {
        global $wpdb;
        $p = $wpdb->prefix . 'ls_';

        $defaults = array( 'search' => '', 'limit' => 50, 'offset' => 0 );
        $args     = wp_parse_args( $args, $defaults );

        $where = '1=1';
        $vals  = array();

        if ( ! empty( $args['search'] ) ) {
            $where .= ' AND (c.phone LIKE %s OR c.full_name LIKE %s OR c.email LIKE %s)';
            $like   = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $vals[] = $like;
            $vals[] = $like;
            $vals[] = $like;
        }

        $vals[] = $args['limit'];
        $vals[] = $args['offset'];

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT c.*,
                    COALESCE(SUM(CASE WHEN l.type='credit' THEN l.amount ELSE -l.amount END),0) AS balance
               FROM {$p}customers c
          LEFT JOIN {$p}ledger l ON l.customer_id = c.id
              WHERE $where
           GROUP BY c.id
           ORDER BY c.created_at DESC
              LIMIT %d OFFSET %d",
            ...$vals
        ) );
    }

    public static function count_customers( $search = '' ) {
        global $wpdb;
        $p = $wpdb->prefix . 'ls_';

        if ( $search ) {
            $like = '%' . $wpdb->esc_like( $search ) . '%';
            return (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$p}customers WHERE phone LIKE %s OR full_name LIKE %s OR email LIKE %s",
                $like, $like, $like
            ) );
        }

        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$p}customers" );
    }

    // ── Feedback ──────────────────────────────────────────────────────────────

    public static function get_feedback( $args = array() ) {
        global $wpdb;
        $p        = $wpdb->prefix . 'ls_';
        $defaults = array( 'type' => '', 'customer_id' => 0, 'phone' => '', 'limit' => 50, 'offset' => 0 );
        $args     = wp_parse_args( $args, $defaults );
        $where    = '1=1';
        $vals     = array();

        if ( ! empty( $args['type'] ) ) {
            $where  .= ' AND type = %s';
            $vals[]  = $args['type'];
        }

        if ( ! empty( $args['customer_id'] ) ) {
            $where  .= ' AND customer_id = %d';
            $vals[]  = $args['customer_id'];
        } elseif ( ! empty( $args['phone'] ) ) {
            $where  .= ' AND phone = %s';
            $vals[]  = $args['phone'];
        }

        $vals[] = $args['limit'];
        $vals[] = $args['offset'];

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$p}feedback WHERE $where ORDER BY submitted_at DESC LIMIT %d OFFSET %d",
            ...$vals
        ) );
    }

    public static function count_feedback( $type = '' ) {
        global $wpdb;
        $p = $wpdb->prefix . 'ls_';
        if ( $type ) {
            return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$p}feedback WHERE type = %s", $type ) );
        }
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$p}feedback" );
    }

    // ── Merchant Feedback ─────────────────────────────────────────────────────

    public static function get_merchant_feedback( $args = array() ) {
        global $wpdb;
        $p        = $wpdb->prefix . 'ls_';
        $defaults = array( 'limit' => 50, 'offset' => 0 );
        $args     = wp_parse_args( $args, $defaults );

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT f.*, b.name AS branch_name
               FROM {$p}feedback_merchant f
          LEFT JOIN {$p}branches b ON b.id = f.branch_id
           ORDER BY f.submitted_at DESC
              LIMIT %d OFFSET %d",
            $args['limit'], $args['offset']
        ) );
    }

    public static function count_merchant_feedback() {
        global $wpdb;
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}ls_feedback_merchant" );
    }

    // ── Pickups ────────────────────────────────────────────────────────────────

    public static function get_all_pickups() {
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ls_pickups ORDER BY name ASC" );
    }

    // ── Interventions ─────────────────────────────────────────────────────────

    public static function get_interventions( $args = array() ) {
        global $wpdb;
        $p = $wpdb->prefix . 'ls_';

        $defaults = array(
            'status'      => '',
            'pickup_id'   => 0,
            'customer_id' => 0,
            'date_from'   => '',
            'date_to'     => '',
            'search'      => '',
            'limit'       => 50,
            'offset'      => 0,
        );
        $args  = wp_parse_args( $args, $defaults );
        $where = '1=1';
        $vals  = array();

        if ( ! empty( $args['status'] ) ) {
            $where .= ' AND i.status = %s';
            $vals[] = $args['status'];
        }
        if ( ! empty( $args['pickup_id'] ) ) {
            $where .= ' AND i.pickup_id = %d';
            $vals[] = (int) $args['pickup_id'];
        }
        if ( ! empty( $args['customer_id'] ) ) {
            $where .= ' AND i.customer_id = %d';
            $vals[] = (int) $args['customer_id'];
        }
        if ( ! empty( $args['date_from'] ) ) {
            $where .= ' AND DATE(i.scheduled_at) >= %s';
            $vals[] = $args['date_from'];
        }
        if ( ! empty( $args['date_to'] ) ) {
            $where .= ' AND DATE(i.scheduled_at) <= %s';
            $vals[] = $args['date_to'];
        }
        if ( ! empty( $args['search'] ) ) {
            $like   = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $where .= ' AND (c.full_name LIKE %s OR c.phone LIKE %s OR p.name LIKE %s)';
            $vals[] = $like;
            $vals[] = $like;
            $vals[] = $like;
        }

        $vals[] = (int) $args['limit'];
        $vals[] = (int) $args['offset'];

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT i.*,
                    c.full_name   AS customer_name,  c.phone    AS customer_phone,
                    c.address     AS customer_address,
                    p.category    AS pickup_category, p.name AS pickup_name, p.plate_number AS pickup_plate,
                    p.phone       AS pickup_phone,
                    b.name        AS branch_name
               FROM {$p}interventions i
          LEFT JOIN {$p}customers c ON c.id = i.customer_id
          LEFT JOIN {$p}pickups   p ON p.id = i.pickup_id
          LEFT JOIN {$p}branches  b ON b.id = i.branch_id
              WHERE $where
           ORDER BY i.scheduled_at DESC
              LIMIT %d OFFSET %d",
            ...$vals
        ) );
    }

    public static function count_interventions( $args = array() ) {
        global $wpdb;
        $p = $wpdb->prefix . 'ls_';

        $defaults = array( 'status' => '', 'pickup_id' => 0, 'customer_id' => 0, 'date_from' => '', 'date_to' => '', 'search' => '' );
        $args  = wp_parse_args( $args, $defaults );
        $where = '1=1';
        $vals  = array();

        if ( ! empty( $args['status'] ) )      { $where .= ' AND i.status = %s';              $vals[] = $args['status']; }
        if ( ! empty( $args['pickup_id'] ) )   { $where .= ' AND i.pickup_id = %d';           $vals[] = (int) $args['pickup_id']; }
        if ( ! empty( $args['customer_id'] ) ) { $where .= ' AND i.customer_id = %d';         $vals[] = (int) $args['customer_id']; }
        if ( ! empty( $args['date_from'] ) )   { $where .= ' AND DATE(i.scheduled_at) >= %s'; $vals[] = $args['date_from']; }
        if ( ! empty( $args['date_to'] ) )     { $where .= ' AND DATE(i.scheduled_at) <= %s'; $vals[] = $args['date_to']; }
        if ( ! empty( $args['search'] ) ) {
            $like   = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $where .= ' AND (c.full_name LIKE %s OR c.phone LIKE %s OR p.name LIKE %s)';
            $vals[] = $like; $vals[] = $like; $vals[] = $like;
        }

        $sql = "SELECT COUNT(*) FROM {$p}interventions i
                LEFT JOIN {$p}customers c ON c.id = i.customer_id
                LEFT JOIN {$p}pickups   p ON p.id = i.pickup_id
                WHERE $where";

        return empty( $vals ) ? (int) $wpdb->get_var( $sql ) : (int) $wpdb->get_var( $wpdb->prepare( $sql, ...$vals ) );
    }

    // ── Analytics ─────────────────────────────────────────────────────────────

    public static function get_dashboard_stats() {
        global $wpdb;
        $p = $wpdb->prefix . 'ls_';

        $today       = current_time( 'Y-m-d' );
        $month_start = current_time( 'Y-m' ) . '-01';
        $prev_month  = date( 'Y-m-01', strtotime( '-1 month', strtotime( $month_start ) ) );
        $prev_end    = date( 'Y-m-t',  strtotime( '-1 month', strtotime( $month_start ) ) );

        return array(
            // Totals
            'total_customers'      => (int)   $wpdb->get_var( "SELECT COUNT(*) FROM {$p}customers" ),
            'total_invoices'       => (int)   $wpdb->get_var( "SELECT COUNT(*) FROM {$p}invoices" ),
            'total_revenue'        => (float) $wpdb->get_var( "SELECT COALESCE(SUM(amount),0) FROM {$p}invoices" ),
            'total_discounts'      => (float) $wpdb->get_var( "SELECT COALESCE(SUM(discount_amount),0) FROM {$p}invoices WHERE discount_amount > 0" ),
            'total_credits_issued' => (float) $wpdb->get_var( "SELECT COALESCE(SUM(amount),0) FROM {$p}ledger WHERE type='credit'" ),
            'total_credits_redeemed'=> (float) $wpdb->get_var( "SELECT COALESCE(SUM(amount),0) FROM {$p}ledger WHERE type='debit'" ),
            'open_tickets'         => (int)   $wpdb->get_var( "SELECT COUNT(*) FROM {$p}tickets WHERE status NOT IN ('closed','resolved')" ),
            'total_tickets'        => (int)   $wpdb->get_var( "SELECT COUNT(*) FROM {$p}tickets" ),

            // This month
            'month_invoices'       => (int)   $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$p}invoices WHERE invoice_date >= %s", $month_start ) ),
            'month_revenue'        => (float) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(amount),0) FROM {$p}invoices WHERE invoice_date >= %s", $month_start ) ),
            'month_customers'      => (int)   $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$p}customers WHERE created_at >= %s", $month_start ) ),

            // Previous month (for trend)
            'prev_month_invoices'  => (int)   $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$p}invoices WHERE invoice_date BETWEEN %s AND %s", $prev_month, $prev_end ) ),
            'prev_month_revenue'   => (float) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(amount),0) FROM {$p}invoices WHERE invoice_date BETWEEN %s AND %s", $prev_month, $prev_end ) ),

            // Today
            'today_invoices'       => (int)   $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$p}invoices WHERE invoice_date = %s", $today ) ),
            'today_revenue'        => (float) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(amount),0) FROM {$p}invoices WHERE invoice_date = %s", $today ) ),

            // Recent invoices (last 30 days per day for chart)
            'revenue_by_day'       => $wpdb->get_results( $wpdb->prepare(
                "SELECT invoice_date AS day, SUM(amount) AS total, COUNT(*) AS count
                   FROM {$p}invoices
                  WHERE invoice_date >= %s AND invoice_date <= %s
               GROUP BY invoice_date
               ORDER BY invoice_date ASC",
                date( 'Y-m-d', strtotime( '-29 days', strtotime( $today ) ) ),
                $today
            ) ),

            // Top 5 customers by revenue
            'top_customers'        => $wpdb->get_results(
                "SELECT c.full_name, c.phone, SUM(i.amount) AS total, COUNT(i.id) AS invoices
                   FROM {$p}invoices i
                   JOIN {$p}customers c ON c.id = i.customer_id
               GROUP BY i.customer_id
               ORDER BY total DESC
                  LIMIT 5"
            ),

            // Tickets by status
            'tickets_by_status'    => $wpdb->get_results(
                "SELECT status, COUNT(*) AS count FROM {$p}tickets GROUP BY status"
            ),
        );
    }
}
