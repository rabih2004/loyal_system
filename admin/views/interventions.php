<?php if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Admin view: Interventions (create + manage + filter + report)
 *
 * Available vars: $interventions, $total_interventions, $pickups, $branches,
 *                 $statuses, $types, $status_filter, $pickup_filter,
 *                 $date_from, $date_to, $search, $paged, $per_page
 */
$status_labels = array(
    'pending'   => 'En attente',
    'confirmed' => 'Confirmé',
    'en_route'  => 'En route',
    'completed' => 'Complété',
    'cancelled' => 'Annulé',
);
$type_labels = array(
    'livraison'   => 'Livraison',
    'montage'     => 'Montage',
    'maintenance' => 'Maintenance',
);
$base_url = admin_url( 'admin.php?page=ls-interventions' );
?>
<div class="wrap ls-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Interventions', 'loyal-system' ); ?></h1>
    <hr class="wp-header-end">

    <div id="ls-int-msg" style="display:none;" class="notice"></div>

    <!-- ── Add form ────────────────────────────────────────────────────────── -->
    <div class="postbox" id="ls-add-int-box">
        <div class="postbox-header" style="cursor:pointer;" id="ls-add-int-toggle">
            <h2><?php esc_html_e( 'Nouvelle intervention', 'loyal-system' ); ?> <span id="ls-add-int-arrow">&#9660;</span></h2>
        </div>
        <div class="inside" id="ls-add-int-body" style="display:none;">
            <form id="ls-add-int-form" enctype="multipart/form-data" novalidate>
                <table class="form-table" role="presentation">
                    <tr>
                        <th><label><?php esc_html_e( 'Client *', 'loyal-system' ); ?></label></th>
                        <td>
                            <input type="text" id="ls-int-cust-search" class="regular-text" placeholder="<?php esc_attr_e( 'Rechercher par téléphone ou nom…', 'loyal-system' ); ?>" autocomplete="off">
                            <input type="hidden" name="customer_id" id="ls-int-customer-id">
                            <div id="ls-int-cust-results" style="background:#fff;border:1px solid #ccc;max-width:340px;display:none;position:absolute;z-index:100;"></div>
                            <p id="ls-int-cust-selected" class="description" style="color:#2271b1;margin-top:4px;"></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="ls-int-type"><?php esc_html_e( 'Type *', 'loyal-system' ); ?></label></th>
                        <td>
                            <select name="type" id="ls-int-type" class="regular-text">
                                <?php foreach ( $types as $val => $label ) : ?>
                                    <option value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="ls-int-pickup"><?php esc_html_e( 'Chauffeur (Pickup)', 'loyal-system' ); ?></label></th>
                        <td>
                            <select name="pickup_id" id="ls-int-pickup" class="regular-text">
                                <option value="0"><?php esc_html_e( '— Aucun —', 'loyal-system' ); ?></option>
                                <?php foreach ( $pickups as $pk ) : ?>
                                    <option value="<?php echo (int) $pk->id; ?>"><?php echo esc_html( $pk->name . ( $pk->plate_number ? ' (' . $pk->plate_number . ')' : '' ) ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="ls-int-branch"><?php esc_html_e( 'Dépôt de départ', 'loyal-system' ); ?></label></th>
                        <td>
                            <select name="branch_id" id="ls-int-branch" class="regular-text">
                                <option value="0"><?php esc_html_e( '— Aucun —', 'loyal-system' ); ?></option>
                                <?php foreach ( $branches as $b ) : ?>
                                    <option value="<?php echo (int) $b->id; ?>"><?php echo esc_html( $b->name ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="ls-int-scheduled"><?php esc_html_e( 'Date et heure *', 'loyal-system' ); ?></label></th>
                        <td><input type="datetime-local" name="scheduled_at" id="ls-int-scheduled" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="ls-int-status"><?php esc_html_e( 'Statut', 'loyal-system' ); ?></label></th>
                        <td>
                            <select name="status" id="ls-int-status" class="regular-text">
                                <?php foreach ( $statuses as $val => $label ) : ?>
                                    <option value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="ls-int-attachment"><?php esc_html_e( 'Pièce jointe', 'loyal-system' ); ?></label></th>
                        <td><input type="file" name="attachment" id="ls-int-attachment"></td>
                    </tr>
                    <tr>
                        <th><label for="ls-int-notes"><?php esc_html_e( 'Notes', 'loyal-system' ); ?></label></th>
                        <td><textarea name="notes" id="ls-int-notes" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Instructions ou remarques…', 'loyal-system' ); ?>"></textarea></td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary" id="ls-add-int-btn"><?php esc_html_e( 'Créer l\'intervention', 'loyal-system' ); ?></button>
                    <span class="spinner" id="ls-int-spinner"></span>
                </p>
            </form>
        </div>
    </div>

    <!-- ── Filters ──────────────────────────────────────────────────────────── -->
    <form method="get" action="<?php echo esc_url( $base_url ); ?>" style="margin-bottom:16px;display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;">
        <input type="hidden" name="page" value="ls-interventions">
        <div>
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:2px;"><?php esc_html_e( 'Statut', 'loyal-system' ); ?></label>
            <select name="status" class="regular-text" style="min-width:130px;">
                <option value=""><?php esc_html_e( 'Tous', 'loyal-system' ); ?></option>
                <?php foreach ( $statuses as $val => $label ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $status_filter, $val ); ?>><?php echo esc_html( $label ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:2px;"><?php esc_html_e( 'Chauffeur', 'loyal-system' ); ?></label>
            <select name="pickup_id" class="regular-text" style="min-width:140px;">
                <option value="0"><?php esc_html_e( 'Tous', 'loyal-system' ); ?></option>
                <?php foreach ( $pickups as $pk ) : ?>
                    <option value="<?php echo (int) $pk->id; ?>" <?php selected( $pickup_filter, $pk->id ); ?>><?php echo esc_html( $pk->name ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:2px;"><?php esc_html_e( 'Du', 'loyal-system' ); ?></label>
            <input type="date" name="date_from" value="<?php echo esc_attr( $date_from ); ?>" class="regular-text" style="width:140px;">
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:2px;"><?php esc_html_e( 'Au', 'loyal-system' ); ?></label>
            <input type="date" name="date_to" value="<?php echo esc_attr( $date_to ); ?>" class="regular-text" style="width:140px;">
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:2px;"><?php esc_html_e( 'Recherche', 'loyal-system' ); ?></label>
            <input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Client ou chauffeur…', 'loyal-system' ); ?>">
        </div>
        <div style="padding-top:18px;">
            <button type="submit" class="button"><?php esc_html_e( 'Filtrer', 'loyal-system' ); ?></button>
            <a href="<?php echo esc_url( $base_url ); ?>" class="button"><?php esc_html_e( 'Réinitialiser', 'loyal-system' ); ?></a>
        </div>
    </form>

    <!-- ── Summary counts ──────────────────────────────────────────────────── -->
    <p style="color:#555;margin-bottom:12px;">
        <?php echo esc_html( sprintf( _n( '%d intervention trouvée', '%d interventions trouvées', $total_interventions, 'loyal-system' ), $total_interventions ) ); ?>
    </p>

    <!-- ── Table ────────────────────────────────────────────────────────────── -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th><?php esc_html_e( 'Client', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Type', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Chauffeur', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Date prévue', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Dépôt', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Statut', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Pièce jointe', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'loyal-system' ); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if ( empty( $interventions ) ) : ?>
            <tr><td colspan="9"><?php esc_html_e( 'Aucune intervention pour ces critères.', 'loyal-system' ); ?></td></tr>
        <?php else : ?>
            <?php foreach ( $interventions as $iv ) :
                $iv_id     = (int) $iv->id;
                $cust_name = $iv->customer_name ?: $iv->customer_phone ?: '—';
                $type_lbl  = $type_labels[ $iv->type ] ?? $iv->type;
                $stat_lbl  = $status_labels[ $iv->status ] ?? $iv->status;
                $sched     = date_i18n( 'd/m/Y H:i', strtotime( $iv->scheduled_at ) );
            ?>
            <tr id="ls-iv-row-<?php echo $iv_id; ?>">
                <td><?php echo $iv_id; ?></td>
                <td>
                    <strong><?php echo esc_html( $cust_name ); ?></strong><br>
                    <small><?php echo esc_html( $iv->customer_phone ?: '' ); ?></small>
                    <?php if ( $iv->customer_address ) : ?>
                        <br><small style="color:#666;"><?php echo esc_html( $iv->customer_address ); ?></small>
                    <?php endif; ?>
                </td>
                <td><span class="ls-badge ls-type-<?php echo esc_attr( $iv->type ); ?>"><?php echo esc_html( $type_lbl ); ?></span></td>
                <td>
                    <?php if ( $iv->pickup_name ) : ?>
                        <?php echo esc_html( $iv->pickup_name ); ?><br>
                        <small><?php echo esc_html( $iv->pickup_plate ?: '' ); ?></small>
                    <?php else : ?>
                        —
                    <?php endif; ?>
                </td>
                <td><?php echo esc_html( $sched ); ?></td>
                <td><?php echo esc_html( $iv->branch_name ?: '—' ); ?></td>
                <td>
                    <span class="ls-badge ls-status-<?php echo esc_attr( $iv->status ); ?>">
                        <?php echo esc_html( $stat_lbl ); ?>
                    </span>
                </td>
                <td>
                    <?php if ( $iv->attachment_path ) : ?>
                        <a href="<?php echo esc_url( wp_upload_dir()['baseurl'] . $iv->attachment_path ); ?>"
                           target="_blank" rel="noopener"
                           class="button button-small">
                            &#128206; <?php esc_html_e( 'Voir', 'loyal-system' ); ?>
                        </a>
                    <?php else : ?>
                        <span style="color:#999;">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button type="button" class="button button-small ls-iv-edit-btn"
                        data-id="<?php echo $iv_id; ?>"
                        data-status="<?php echo esc_attr( $iv->status ); ?>"
                        data-notes="<?php echo esc_attr( $iv->notes ); ?>">
                        <?php esc_html_e( 'Modifier', 'loyal-system' ); ?>
                    </button>
                    <button type="button" class="button button-small ls-iv-print-btn"
                        data-id="<?php echo $iv_id; ?>"
                        data-type="<?php echo esc_attr( $type_lbl ); ?>"
                        data-status="<?php echo esc_attr( $stat_lbl ); ?>"
                        data-scheduled="<?php echo esc_attr( $sched ); ?>"
                        data-branch="<?php echo esc_attr( $iv->branch_name ?: '—' ); ?>"
                        data-notes="<?php echo esc_attr( $iv->notes ?: '' ); ?>"
                        data-cust-name="<?php echo esc_attr( $cust_name ); ?>"
                        data-cust-phone="<?php echo esc_attr( $iv->customer_phone ?: '' ); ?>"
                        data-cust-address="<?php echo esc_attr( $iv->customer_address ?: '' ); ?>"
                        data-pickup-name="<?php echo esc_attr( $iv->pickup_name ?: '' ); ?>"
                        data-pickup-plate="<?php echo esc_attr( $iv->pickup_plate ?: '' ); ?>"
                        data-pickup-phone="<?php echo esc_attr( $iv->pickup_phone ?: '' ); ?>"
                        data-attachment="<?php echo $iv->attachment_path ? esc_attr( wp_upload_dir()['baseurl'] . $iv->attachment_path ) : ''; ?>"
                        data-attachment-name="<?php echo $iv->attachment_path ? esc_attr( basename( $iv->attachment_path ) ) : ''; ?>">
                        &#128424; <?php esc_html_e( 'Imprimer', 'loyal-system' ); ?>
                    </button>
                    <button type="button" class="button button-small button-link-delete ls-iv-delete-btn"
                        data-id="<?php echo $iv_id; ?>">
                        <?php esc_html_e( 'Supprimer', 'loyal-system' ); ?>
                    </button>
                </td>
            </tr>
            <tr class="ls-iv-edit-row" id="ls-iv-edit-<?php echo $iv_id; ?>" style="display:none;">
                <td colspan="9" style="background:#f9f9f9;padding:14px 20px;">
                    <form class="ls-iv-update-form" data-id="<?php echo $iv_id; ?>" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                        <div>
                            <label style="display:block;font-weight:600;font-size:12px;margin-bottom:3px;"><?php esc_html_e( 'Statut', 'loyal-system' ); ?></label>
                            <select name="status" class="regular-text">
                                <?php foreach ( $statuses as $val => $label ) : ?>
                                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $iv->status, $val ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="flex:1;min-width:200px;">
                            <label style="display:block;font-weight:600;font-size:12px;margin-bottom:3px;"><?php esc_html_e( 'Notes', 'loyal-system' ); ?></label>
                            <textarea name="notes" rows="2" class="large-text" style="width:100%;"><?php echo esc_textarea( $iv->notes ); ?></textarea>
                        </div>
                        <div style="padding-top:18px;">
                            <button type="submit" class="button button-primary"><?php esc_html_e( 'Enregistrer', 'loyal-system' ); ?></button>
                            <button type="button" class="button ls-iv-cancel-btn" data-id="<?php echo $iv_id; ?>"><?php esc_html_e( 'Annuler', 'loyal-system' ); ?></button>
                        </div>
                    </form>
                    <div class="ls-iv-edit-msg-<?php echo $iv_id; ?>" style="display:none;margin-top:6px;"></div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <?php
    // Pagination
    $total_pages = ceil( $total_interventions / $per_page );
    if ( $total_pages > 1 ) :
        $page_links = paginate_links( array(
            'base'      => add_query_arg( 'paged', '%#%' ),
            'format'    => '',
            'current'   => $paged,
            'total'     => $total_pages,
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
        ) );
        echo '<div class="tablenav bottom" style="margin-top:12px;"><div class="tablenav-pages">' . $page_links . '</div></div>';
    endif;
    ?>

</div><!-- .wrap -->

<script>
(function($){
    // Toggle add form
    $('#ls-add-int-toggle').on('click', function(){
        var $body  = $('#ls-add-int-body');
        var $arrow = $('#ls-add-int-arrow');
        $body.toggle();
        $arrow.html($body.is(':visible') ? '&#9660;' : '&#9658;');
    });

    // Customer search autocomplete
    var custTimer;
    $('#ls-int-cust-search').on('input', function(){
        clearTimeout(custTimer);
        var q = $(this).val().trim();
        if (q.length < 2) { $('#ls-int-cust-results').hide(); return; }
        custTimer = setTimeout(function(){
            $.post(lsAdmin.ajaxUrl, {action:'ls_admin_get_customers',nonce:lsAdmin.nonce,search:q,limit:8})
            .done(function(resp){
                var $r = $('#ls-int-cust-results').empty();
                var customers = resp.success && resp.data && resp.data.customers ? resp.data.customers : [];
                if (customers.length) {
                    customers.forEach(function(c){
                        $('<div>').css({padding:'6px 10px',cursor:'pointer',borderBottom:'1px solid #eee'})
                            .text((c.full_name||'') + ' — ' + c.phone)
                            .on('click', function(){
                                $('#ls-int-customer-id').val(c.id);
                                $('#ls-int-cust-search').val((c.full_name||'') + ' (' + c.phone + ')');
                                $('#ls-int-cust-selected').text('Client sélectionné : ' + (c.full_name||c.phone));
                                $r.hide();
                            }).appendTo($r);
                    });
                    $r.show();
                } else {
                    $('<div>').css({padding:'6px 10px',color:'#888'}).text('Aucun client trouvé.').appendTo($r);
                    $r.show();
                }
            });
        }, 300);
    });
    $(document).on('click', function(e){
        if (!$(e.target).closest('#ls-int-cust-search, #ls-int-cust-results').length) {
            $('#ls-int-cust-results').hide();
        }
    });

    // Add intervention
    $('#ls-add-int-form').on('submit', function(e){
        e.preventDefault();
        if (!$('#ls-int-customer-id').val()) {
            alert('Veuillez sélectionner un client.');
            return;
        }
        var $btn = $('#ls-add-int-btn').prop('disabled', true);
        $('#ls-int-spinner').addClass('is-active');
        var $msg = $('#ls-int-msg').hide();

        var fd = new FormData(this);
        fd.append('action', 'ls_admin_add_intervention');
        fd.append('nonce',  lsAdmin.nonce);

        $.ajax({url:lsAdmin.ajaxUrl,type:'POST',data:fd,processData:false,contentType:false})
        .done(function(resp){
            if (resp.success) {
                $msg.attr('class','notice notice-success').text(resp.data.message).show();
                window.location.reload();
            } else {
                $msg.attr('class','notice notice-error').text(resp.data&&resp.data.message?resp.data.message:lsAdmin.i18n.error).show();
            }
        })
        .fail(function(){ $msg.attr('class','notice notice-error').text(lsAdmin.i18n.error).show(); })
        .always(function(){ $btn.prop('disabled',false); $('#ls-int-spinner').removeClass('is-active'); });
    });

    // Toggle edit row
    $(document).on('click', '.ls-iv-edit-btn', function(){
        var id = $(this).data('id');
        $('.ls-iv-edit-row').not('#ls-iv-edit-'+id).hide();
        $('#ls-iv-edit-'+id).toggle();
    });
    $(document).on('click', '.ls-iv-cancel-btn', function(){
        $('#ls-iv-edit-'+$(this).data('id')).hide();
    });

    // Update intervention
    $(document).on('submit', '.ls-iv-update-form', function(e){
        e.preventDefault();
        var $form = $(this), id = $form.data('id');
        var $msg  = $('.ls-iv-edit-msg-'+id);
        var data  = $form.serializeArray();
        data.push({name:'action',value:'ls_admin_update_intervention'},{name:'nonce',value:lsAdmin.nonce},{name:'intervention_id',value:id});
        $.post(lsAdmin.ajaxUrl, data)
        .done(function(resp){
            $msg.attr('class', resp.success ? 'notice notice-success' : 'notice notice-error')
                .text(resp.success ? resp.data.message : (resp.data&&resp.data.message?resp.data.message:lsAdmin.i18n.error))
                .show();
            if (resp.success) { setTimeout(function(){ window.location.reload(); }, 800); }
        })
        .fail(function(){ $msg.attr('class','notice notice-error').text(lsAdmin.i18n.error).show(); });
    });

    // Print intervention
    $(document).on('click', '.ls-iv-print-btn', function(){
        var d = $(this).data();
        var siteName = '<?php echo esc_js( get_bloginfo('name') ); ?>';
        var logoUrl  = '<?php echo esc_js( get_site_icon_url(64) ); ?>';

        var isImg = d.attachment && /\.(jpg|jpeg|png|gif|webp)$/i.test(d.attachmentName);
        var isPdf = d.attachment && /\.pdf$/i.test(d.attachmentName);

        var attachHtml = '';
        if ( d.attachment ) {
            if ( isImg ) {
                attachHtml = '<div class="iv-section"><div class="iv-section-title">Pièce jointe</div>'
                           + '<img src="' + d.attachment + '" style="max-width:100%;max-height:300px;border:1px solid #ddd;border-radius:4px;margin-top:8px;" />'
                           + '</div>';
            } else {
                attachHtml = '<div class="iv-section"><div class="iv-section-title">Pièce jointe</div>'
                           + '<p style="margin:6px 0 0;"><a href="' + d.attachment + '" style="color:#2563eb;">' + (d.attachmentName || d.attachment) + '</a></p>'
                           + '</div>';
            }
        }

        var notesHtml = d.notes
            ? '<div class="iv-section"><div class="iv-section-title">Notes</div><p class="iv-notes">' + d.notes.replace(/\n/g,'<br>') + '</p></div>'
            : '';

        var html = '<!DOCTYPE html><html><head><meta charset="UTF-8">'
            + '<title>Intervention #' + d.id + '</title>'
            + '<style>'
            + 'body{font-family:Arial,sans-serif;font-size:13px;color:#111;margin:0;padding:24px;}'
            + '.iv-header{display:flex;align-items:center;gap:14px;border-bottom:2px solid #1e3a5f;padding-bottom:12px;margin-bottom:20px;}'
            + '.iv-logo{width:48px;height:48px;object-fit:contain;}'
            + '.iv-header-text h1{margin:0;font-size:18px;color:#1e3a5f;}'
            + '.iv-header-text p{margin:2px 0 0;font-size:12px;color:#555;}'
            + '.iv-badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;background:#e2e8f0;color:#374151;}'
            + '.iv-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;}'
            + '.iv-section{background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:12px 14px;break-inside:avoid;}'
            + '.iv-section-title{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:8px;}'
            + '.iv-row{display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #f1f5f9;font-size:12px;}'
            + '.iv-row:last-child{border-bottom:none;}'
            + '.iv-row-label{color:#6b7280;font-weight:600;}'
            + '.iv-row-val{color:#111;text-align:right;max-width:60%;}'
            + '.iv-notes{margin:4px 0 0;font-size:12px;color:#374151;line-height:1.5;white-space:pre-wrap;}'
            + '.iv-footer{margin-top:24px;padding-top:10px;border-top:1px solid #e2e8f0;font-size:10px;color:#9ca3af;display:flex;justify-content:space-between;}'
            + '@media print{'
            + '  body{padding:10px;}'
            + '  .iv-grid{grid-template-columns:1fr 1fr;}'
            + '  a{color:#111!important;text-decoration:none!important;}'
            + '}'
            + '</style></head><body>'

            + '<div class="iv-header">'
            + (logoUrl ? '<img class="iv-logo" src="' + logoUrl + '" />' : '')
            + '<div class="iv-header-text">'
            + '<h1>' + siteName + '</h1>'
            + '<p>Fiche d\'intervention &nbsp;|&nbsp; <strong>#' + d.id + '</strong> &nbsp;&mdash;&nbsp; ' + d.type + ' &nbsp;|&nbsp; <span class="iv-badge">' + d.status + '</span></p>'
            + '</div></div>'

            + '<div class="iv-grid">'

            + '<div class="iv-section"><div class="iv-section-title">&#128100; Client</div>'
            + '<div class="iv-row"><span class="iv-row-label">Nom</span><span class="iv-row-val">' + (d.custName||'—') + '</span></div>'
            + '<div class="iv-row"><span class="iv-row-label">Téléphone</span><span class="iv-row-val">' + (d.custPhone||'—') + '</span></div>'
            + '<div class="iv-row"><span class="iv-row-label">Adresse</span><span class="iv-row-val">' + (d.custAddress||'—') + '</span></div>'
            + '</div>'

            + '<div class="iv-section"><div class="iv-section-title">&#128666; Chauffeur</div>'
            + '<div class="iv-row"><span class="iv-row-label">Nom</span><span class="iv-row-val">' + (d.pickupName||'—') + '</span></div>'
            + '<div class="iv-row"><span class="iv-row-label">Téléphone</span><span class="iv-row-val">' + (d.pickupPhone||'—') + '</span></div>'
            + '<div class="iv-row"><span class="iv-row-label">Plaque</span><span class="iv-row-val">' + (d.pickupPlate||'—') + '</span></div>'
            + '</div>'

            + '<div class="iv-section"><div class="iv-section-title">&#128197; Intervention</div>'
            + '<div class="iv-row"><span class="iv-row-label">Date prévue</span><span class="iv-row-val">' + d.scheduled + '</span></div>'
            + '<div class="iv-row"><span class="iv-row-label">Type</span><span class="iv-row-val">' + d.type + '</span></div>'
            + '<div class="iv-row"><span class="iv-row-label">Statut</span><span class="iv-row-val">' + d.status + '</span></div>'
            + '</div>'

            + '<div class="iv-section"><div class="iv-section-title">&#127970; Dépôt de départ</div>'
            + '<div class="iv-row"><span class="iv-row-label">Branche</span><span class="iv-row-val">' + (d.branch||'—') + '</span></div>'
            + '</div>'

            + '</div>'

            + notesHtml
            + attachHtml

            + '<div class="iv-footer">'
            + '<span>Imprimé le <?php echo esc_js( date_i18n('d/m/Y H:i') ); ?></span>'
            + '<span><?php echo esc_js( home_url() ); ?></span>'
            + '</div>'
            + '</body></html>';

        var win = window.open('', '_blank', 'width=820,height=700');
        win.document.write(html);
        win.document.close();
        win.onload = function(){ win.focus(); win.print(); };
    });

    // Delete intervention
    $(document).on('click', '.ls-iv-delete-btn', function(){
        if (!confirm(lsAdmin.i18n.confirm_delete)) return;
        var id = $(this).data('id');
        $.post(lsAdmin.ajaxUrl, {action:'ls_admin_delete_intervention',nonce:lsAdmin.nonce,intervention_id:id})
        .done(function(resp){
            if (resp.success) {
                $('#ls-iv-row-'+id+', #ls-iv-edit-'+id).fadeOut();
            } else {
                $('#ls-int-msg').attr('class','notice notice-error')
                    .text(resp.data&&resp.data.message?resp.data.message:lsAdmin.i18n.error).show();
            }
        });
    });
})(jQuery);
</script>
