<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'lsPrintCss' ) ) {
    function lsPrintCss() {
        return '
body { font-family: Arial, sans-serif; font-size: 13px; color: #111; line-height: 1.6; margin: 24px 36px; background:#fff; }
.ls-po-header { text-align: center; border-bottom: 2px solid #111; padding-bottom: 10px; margin-bottom: 18px; }
.ls-po-header h1 { font-size: 18px; margin: 0 0 4px; letter-spacing: 1px; }
.ls-po-header p  { margin: 2px 0 0; font-size: 12px; color: #555; }
.ls-po-section { margin-bottom: 14px; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
.ls-po-section:last-child { border-bottom: none; }
.ls-po-section h2 { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #666; margin: 0 0 8px; }
.ls-po-row { display: flex; gap: 8px; margin-bottom: 5px; }
.ls-po-label { font-weight: 700; min-width: 150px; flex-shrink: 0; }
.ls-po-value { flex: 1; }
.ls-po-pill { display: inline-block; padding: 1px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; border: 1px solid; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.ls-po-pill-oui { background: #dcfce7; border-color: #16a34a; color: #15803d; }
.ls-po-pill-non { background: #fee2e2; border-color: #dc2626; color: #b91c1c; }
.ls-po-answers-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.ls-po-answers-table td { padding: 5px 10px 5px 0; border-bottom: 1px solid #eee; vertical-align: top; }
.ls-po-answers-table td:first-child { font-weight: 600; width: 65%; }
.ls-po-text-block { background: #f9fafb; border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px 12px; white-space: pre-wrap; font-size: 12px; margin-top: 6px; }
.ls-po-footer { margin-top: 20px; text-align: right; font-size: 11px; color: #999; border-top: 1px solid #e2e8f0; padding-top: 8px; }
        ';
    }
}

if ( ! function_exists( 'lsOpenPrintJs' ) ) {
    function lsOpenPrintJs() {
        return "
function lsOpenPrint(html, css) {
    var win = window.open('', '_blank', 'width=820,height=700,scrollbars=yes');
    if (!win) { alert('Veuillez autoriser les popups pour imprimer.'); return; }
    win.document.write('<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>Impression</title><style>' + css + '</style></head><body>' + html + '</body></html>');
    win.document.close();
    win.focus();
    setTimeout(function(){ win.print(); win.close(); }, 400);
}
        ";
    }
}

// Pre-resolve branch names for all tickets that have one.
$_branch_cache = array();
if ( ! empty( $tickets ) ) {
    global $wpdb;
    $branch_ids = array_filter( array_unique( array_column( (array) $tickets, 'branch_id' ) ) );
    if ( $branch_ids ) {
        $placeholders = implode( ',', array_fill( 0, count( $branch_ids ), '%d' ) );
        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT id, name FROM {$wpdb->prefix}ls_branches WHERE id IN ($placeholders)",
            ...$branch_ids
        ) );
        foreach ( $rows as $b ) { $_branch_cache[ $b->id ] = $b->name; }
    }
}

// Build print-data object for JS.
$print_data = array();
foreach ( (array) $tickets as $t ) {
    $category_name = '';
    if ( ! empty( $t->category_id ) ) {
        $cat = get_term( (int) $t->category_id, 'product_cat' );
        if ( $cat && ! is_wp_error( $cat ) ) {
            $category_name = $cat->name;
        }
    }
    $print_data[ $t->id ] = array(
        'id'             => (int) $t->id,
        'subject'        => $t->subject,
        'customer'       => $t->customer_name ?: ( $t->guest_name ?: '' ),
        'phone'          => $t->contact_phone,
        'status'         => $statuses[ $t->status ] ?? ucfirst( $t->status ),
        'priority'       => ucfirst( $t->priority ),
        'date'           => date_i18n( get_option( 'date_format' ), strtotime( $t->created_at ) ),
        'category'       => $category_name,
        'partner'        => ! empty( $t->branch_id ) ? ( $_branch_cache[ $t->branch_id ] ?? '' ) : '',
        'invoice_number' => $t->invoice_number ?? '',
        'invoice_date'   => ! empty( $t->invoice_date ) ? date_i18n( get_option( 'date_format' ), strtotime( $t->invoice_date ) ) : '',
        'description'    => $t->description,
        'admin_notes'    => $t->admin_notes,
    );
}
?>
<div class="wrap ls-admin-wrap">
    <h1><?php esc_html_e( 'Support Tickets', 'loyal-system' ); ?></h1>
    <hr class="wp-header-end">

    <!-- Filter bar -->
    <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
        <input type="hidden" name="page" value="ls-tickets">
        <div class="tablenav top">
            <div class="alignleft actions">
                <select name="status">
                    <option value=""><?php esc_html_e( 'All Statuses', 'loyal-system' ); ?></option>
                    <?php foreach ( $statuses as $slug => $label ) : ?>
                        <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $slug, $status ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search…', 'loyal-system' ); ?>" class="regular-text">
                <?php submit_button( __( 'Filter', 'loyal-system' ), 'action', '', false ); ?>
            </div>
            <br class="clear">
        </div>
    </form>

    <table class="wp-list-table widefat fixed striped" id="ls-ticket-list">
        <thead>
            <tr>
                <th>#</th>
                <th><?php esc_html_e( 'Subject',   'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Customer',  'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Status',    'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Priority',  'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Date',      'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Actions',   'loyal-system' ); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if ( empty( $tickets ) ) : ?>
            <tr><td colspan="7"><?php esc_html_e( 'No tickets found.', 'loyal-system' ); ?></td></tr>
        <?php else : ?>
            <?php foreach ( $tickets as $t ) : ?>
            <tr id="ls-ticket-row-<?php echo (int) $t->id; ?>">
                <td>#<?php echo (int) $t->id; ?></td>
                <td><strong><?php echo esc_html( $t->subject ); ?></strong></td>
                <td>
                    <?php echo esc_html( $t->customer_name ?: ( $t->guest_name ?: '—' ) ); ?><br>
                    <small class="ls-text-muted"><?php echo esc_html( $t->contact_phone ); ?></small>
                </td>
                <td><span class="ls-badge ls-status-<?php echo esc_attr( $t->status ); ?>"><?php echo esc_html( $statuses[ $t->status ] ?? ucfirst( $t->status ) ); ?></span></td>
                <td><?php echo esc_html( ucfirst( $t->priority ) ); ?></td>
                <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $t->created_at ) ) ); ?></td>
                <td>
                    <button type="button" class="button button-small ls-ticket-view-btn" data-ticket-id="<?php echo (int) $t->id; ?>"
                        data-description="<?php echo esc_attr( $t->description ); ?>"
                        data-status="<?php echo esc_attr( $t->status ); ?>"
                        data-notes="<?php echo esc_attr( $t->admin_notes ); ?>">
                        <?php esc_html_e( 'Manage', 'loyal-system' ); ?>
                    </button>
                    <button type="button" class="button button-small ls-ticket-print-btn"
                        data-ticket-id="<?php echo (int) $t->id; ?>">
                        &#128438; <?php esc_html_e( 'Print', 'loyal-system' ); ?>
                    </button>
                    <button type="button" class="button button-small button-link-delete ls-ticket-delete-btn"
                        data-ticket-id="<?php echo (int) $t->id; ?>">
                        <?php esc_html_e( 'Delete', 'loyal-system' ); ?>
                    </button>
                </td>
            </tr>
            <tr class="ls-ticket-detail-row" id="ls-ticket-detail-<?php echo (int) $t->id; ?>" style="display:none;">
                <td colspan="7" class="ls-detail-cell">
                    <div class="ls-detail-inner">

                        <?php if ( ! empty( $t->invoice_number ) || ! empty( $t->invoice_date ) || ! empty( $t->branch_id ) ) : ?>
                        <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:12px;font-size:13px;">
                            <?php if ( ! empty( $t->branch_id ) ) : ?>
                            <span><strong><?php esc_html_e( 'Partenaire:', 'loyal-system' ); ?></strong> <?php echo esc_html( $_branch_cache[ $t->branch_id ] ?? '—' ); ?></span>
                            <?php endif; ?>
                            <?php if ( ! empty( $t->invoice_number ) ) : ?>
                            <span><strong><?php esc_html_e( 'N° facture:', 'loyal-system' ); ?></strong> <?php echo esc_html( $t->invoice_number ); ?></span>
                            <?php endif; ?>
                            <?php if ( ! empty( $t->invoice_date ) ) : ?>
                            <span><strong><?php esc_html_e( 'Date facture:', 'loyal-system' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $t->invoice_date ) ) ); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <p style="margin:0 0 6px;font-weight:600;"><?php esc_html_e( 'Description:', 'loyal-system' ); ?></p>
                        <div class="ls-description" style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:10px 14px;margin-bottom:14px;white-space:pre-wrap;font-size:13px;"><?php echo esc_html( $t->description ); ?></div>

                        <!-- Attachments -->
                        <div id="ls-ticket-imgs-<?php echo (int) $t->id; ?>" style="margin-bottom:14px;"></div>

                        <div id="ls-update-msg-<?php echo (int) $t->id; ?>" style="display:none;" class="notice"></div>
                        <form class="ls-update-ticket-form" data-ticket-id="<?php echo (int) $t->id; ?>">
                            <table class="form-table ls-form-table" role="presentation">
                                <tr>
                                    <th><label><?php esc_html_e( 'Status', 'loyal-system' ); ?></label></th>
                                    <td>
                                        <select name="status">
                                            <?php foreach ( $statuses as $s => $l ) : ?>
                                                <option value="<?php echo esc_attr( $s ); ?>" <?php selected( $s, $t->status ); ?>><?php echo esc_html( $l ); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label><?php esc_html_e( 'Admin Notes', 'loyal-system' ); ?></label></th>
                                    <td><textarea name="admin_notes" rows="3" class="large-text"><?php echo esc_textarea( $t->admin_notes ); ?></textarea></td>
                                </tr>
                            </table>
                            <input type="hidden" name="ticket_id" value="<?php echo (int) $t->id; ?>">
                            <input type="hidden" name="action" value="ls_admin_update_ticket">
                            <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'ls_admin_nonce' ) ); ?>">
                            <button type="submit" class="button button-primary"><?php esc_html_e( 'Save', 'loyal-system' ); ?></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>


<script>
<?php echo lsOpenPrintJs(); ?>
(function($){
    var printData = <?php echo wp_json_encode( $print_data ); ?>;
    var siteName  = <?php echo wp_json_encode( get_bloginfo( 'name' ) ); ?>;

    // ── Build ticket print HTML ────────────────────────────────────────────
    function buildTicketHtml( d, images ) {
        var html = '<div class="ls-po-header">'
            + '<h1>' + esc(siteName) + '</h1>'
            + '<p>TICKET DE SUPPORT &mdash; #' + d.id + '</p>'
            + '</div>';

        // Status / info row
        html += '<div class="ls-po-section"><h2>Informations</h2>'
            + row('Statut', d.status)
            + row('Priorité', d.priority)
            + row('Date', d.date);
        if ( d.category ) html += row('Catégorie', d.category);
        html += '</div>';

        // Customer
        html += '<div class="ls-po-section"><h2>Client</h2>'
            + row('Nom', d.customer || '—')
            + row('Téléphone', d.phone || '—')
            + '</div>';

        // Invoice info
        if ( d.partner || d.invoice_number || d.invoice_date ) {
            html += '<div class="ls-po-section"><h2>Référence</h2>';
            if ( d.partner )        html += row('Partenaire',     d.partner);
            if ( d.invoice_number ) html += row('N° de facture',  d.invoice_number);
            if ( d.invoice_date )   html += row('Date de facture', d.invoice_date);
            html += '</div>';
        }

        // Description
        html += '<div class="ls-po-section"><h2>Description</h2>'
            + '<div class="ls-po-text-block">' + esc(d.description || '—') + '</div>'
            + '</div>';

        // Attachments
        if ( images && images.length ) {
            html += '<div class="ls-po-section"><h2>Pièces jointes</h2>'
                + '<div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:6px;">';
            $.each(images, function(i, url){
                var ext = url.split('.').pop().toLowerCase();
                if ( ['jpg','jpeg','png','gif','webp'].indexOf(ext) !== -1 ) {
                    html += '<img src="' + url + '" style="width:160px;height:120px;object-fit:cover;border:1px solid #ddd;border-radius:4px;">';
                } else {
                    html += '<a href="' + url + '">' + esc(url.split('/').pop()) + '</a>';
                }
            });
            html += '</div></div>';
        }

        // Admin notes
        if ( d.admin_notes ) {
            html += '<div class="ls-po-section"><h2>Notes administrateur</h2>'
                + '<div class="ls-po-text-block">' + esc(d.admin_notes) + '</div>'
                + '</div>';
        }

        html += '<div class="ls-po-footer">Imprimé le ' + new Date().toLocaleString('fr-FR') + '</div>';
        return html;
    }

    function row(label, value) {
        return '<div class="ls-po-row"><span class="ls-po-label">' + esc(label) + ' :</span>'
            + '<span class="ls-po-value">' + esc(String(value)) + '</span></div>';
    }

    function esc(s) { return $('<span>').text(String(s)).html(); }

    var printCSS = <?php echo wp_json_encode( lsPrintCss() ); ?>;

    // ── Print ticket ──────────────────────────────────────────────────────
    $(document).on('click', '.ls-ticket-print-btn', function(){
        var id  = $(this).data('ticket-id');
        var d   = printData[id];
        var $btn = $(this);
        if ( ! d ) return;
        $btn.prop('disabled', true).text('…');
        $.post(lsAdmin.ajaxUrl, { action: 'ls_admin_get_ticket_images', nonce: lsAdmin.nonce, ticket_id: id })
        .always(function(resp){
            var images = (resp && resp.success && resp.data && resp.data.images) ? resp.data.images : [];
            lsOpenPrint( buildTicketHtml(d, images), printCSS );
            $btn.prop('disabled', false).html('&#128438; <?php echo esc_js( __( 'Print', 'loyal-system' ) ); ?>');
        });
    });

    // ── Track which ticket images have already been loaded ─────────────────
    var imgsLoaded = {};

    $(document).on('click', '.ls-ticket-view-btn', function(){
        var id   = $(this).data('ticket-id');
        var $row = $('#ls-ticket-detail-' + id);
        $('.ls-ticket-detail-row').not($row).hide();
        $row.toggle();

        if ( $row.is(':visible') && ! imgsLoaded[id] ) {
            imgsLoaded[id] = true;
            var $imgs = $('#ls-ticket-imgs-' + id);
            $imgs.html('<em style="color:#999;font-size:12px;"><?php echo esc_js( __( 'Loading attachments…', 'loyal-system' ) ); ?></em>');

            $.post(lsAdmin.ajaxUrl, { action: 'ls_admin_get_ticket_images', nonce: lsAdmin.nonce, ticket_id: id })
            .done(function(resp){
                if ( resp.success && resp.data.images && resp.data.images.length ) {
                    var html = '<p style="margin:0 0 8px;font-weight:600;font-size:13px;"><?php echo esc_js( __( 'Attachments:', 'loyal-system' ) ); ?></p>'
                             + '<div style="display:flex;flex-wrap:wrap;gap:10px;">';
                    $.each(resp.data.images, function(i, url){
                        var ext = url.split('.').pop().toLowerCase();
                        if ( ['jpg','jpeg','png','gif','webp'].indexOf(ext) !== -1 ) {
                            html += '<a href="'+url+'" target="_blank" style="display:block;border:2px solid #e2e8f0;border-radius:6px;overflow:hidden;">'
                                  + '<img src="'+url+'" style="width:120px;height:90px;object-fit:cover;display:block;" loading="lazy">'
                                  + '</a>';
                        } else {
                            var name = url.split('/').pop();
                            html += '<a href="'+url+'" target="_blank" class="button button-small" style="display:inline-flex;align-items:center;gap:5px;">'
                                  + '<span class="dashicons dashicons-media-default" style="line-height:26px;font-size:14px;"></span>'
                                  + esc(name) + '</a>';
                        }
                    });
                    html += '</div>';
                    $imgs.html(html);
                } else {
                    $imgs.html('');
                }
            })
            .fail(function(){ $imgs.html(''); });
        }
    });

    $(document).on('submit', '.ls-update-ticket-form', function(e){
        e.preventDefault();
        var $form = $(this);
        var id    = $form.data('ticket-id');
        var $msg  = $('#ls-update-msg-' + id);
        $.post(lsAdmin.ajaxUrl, $form.serialize())
        .done(function(resp){
            $msg.attr('class', resp.success ? 'notice notice-success' : 'notice notice-error')
                .text(resp.success ? resp.data.message : (resp.data&&resp.data.message?resp.data.message:lsAdmin.i18n.error))
                .show();
        })
        .fail(function(){ $msg.attr('class','notice notice-error').text(lsAdmin.i18n.error).show(); });
    });

    $(document).on('click', '.ls-ticket-delete-btn', function(){
        if (!confirm(lsAdmin.i18n.confirm_delete)) return;
        var id = $(this).data('ticket-id');
        $.post(lsAdmin.ajaxUrl, { action:'ls_admin_delete_ticket', nonce:lsAdmin.nonce, ticket_id:id })
        .done(function(resp){
            if (resp.success) {
                $('#ls-ticket-row-' + id).fadeOut();
                $('#ls-ticket-detail-' + id).fadeOut();
            } else {
                alert(resp.data&&resp.data.message?resp.data.message:lsAdmin.i18n.error);
            }
        });
    });
})(jQuery);
</script>
