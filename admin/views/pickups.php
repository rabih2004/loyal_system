<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap ls-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Pickups (Responsables)', 'loyal-system' ); ?></h1>
    <hr class="wp-header-end">

    <div id="ls-pickups-msg" style="display:none;" class="notice"></div>

    <!-- Add Pickup -->
    <div class="postbox" style="max-width:700px;">
        <div class="postbox-header"><h2><?php esc_html_e( 'Ajouter un responsable', 'loyal-system' ); ?></h2></div>
        <div class="inside">
            <form id="ls-add-pickup-form" novalidate>
                <table class="form-table" role="presentation">
                    <tr>
                        <th><label for="ls-pickup-category"><?php esc_html_e( 'Catégorie', 'loyal-system' ); ?> *</label></th>
                        <td>
                            <select id="ls-pickup-category" name="category" class="regular-text" required>
                                <option value=""><?php esc_html_e( '— Choisir —', 'loyal-system' ); ?></option>
                                <option value="Réparateur"><?php esc_html_e( 'Réparateur', 'loyal-system' ); ?></option>
                                <option value="Chauffeur"><?php esc_html_e( 'Chauffeur', 'loyal-system' ); ?></option>
                                <option value="Monteur"><?php esc_html_e( 'Monteur', 'loyal-system' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="ls-pickup-name"><?php esc_html_e( 'Nom du responsable', 'loyal-system' ); ?> *</label></th>
                        <td><input type="text" id="ls-pickup-name" name="name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="ls-pickup-phone"><?php esc_html_e( 'Téléphone', 'loyal-system' ); ?></label></th>
                        <td><input type="tel" id="ls-pickup-phone" name="phone" value="+224" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="ls-pickup-plate"><?php esc_html_e( 'Numéro de plaque', 'loyal-system' ); ?></label></th>
                        <td><input type="text" id="ls-pickup-plate" name="plate_number" class="regular-text" placeholder="ex: RC-1234-A"></td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary" id="ls-add-pickup-btn">
                        <?php esc_html_e( 'Ajouter', 'loyal-system' ); ?>
                    </button>
                    <span class="spinner" id="ls-pickup-spinner"></span>
                </p>
            </form>
        </div>
    </div>

    <!-- Pickup List -->
    <table class="wp-list-table widefat fixed striped" id="ls-pickup-list">
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th><?php esc_html_e( 'Catégorie', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Nom', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Téléphone', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Plaque', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'loyal-system' ); ?></th>
            </tr>
        </thead>
        <tbody id="ls-pickups-tbody">
        <?php if ( empty( $pickups ) ) : ?>
            <tr id="ls-no-pickups"><td colspan="6"><?php esc_html_e( 'Aucun responsable. Ajoutez-en un ci-dessus.', 'loyal-system' ); ?></td></tr>
        <?php else : ?>
            <?php foreach ( $pickups as $pk ) : ?>
            <tr id="ls-pickup-row-<?php echo (int) $pk->id; ?>">
                <td><?php echo (int) $pk->id; ?></td>
                <td class="ls-pk-category-<?php echo (int) $pk->id; ?>"><?php echo esc_html( $pk->category ?: '—' ); ?></td>
                <td><strong class="ls-pk-name-<?php echo (int) $pk->id; ?>"><?php echo esc_html( $pk->name ); ?></strong></td>
                <td class="ls-pk-phone-<?php echo (int) $pk->id; ?>"><?php echo esc_html( $pk->phone ?: '—' ); ?></td>
                <td class="ls-pk-plate-<?php echo (int) $pk->id; ?>"><?php echo esc_html( $pk->plate_number ?: '—' ); ?></td>
                <td>
                    <button type="button" class="button button-small ls-pickup-edit-btn"
                        data-id="<?php echo (int) $pk->id; ?>"
                        data-category="<?php echo esc_attr( $pk->category ); ?>"
                        data-name="<?php echo esc_attr( $pk->name ); ?>"
                        data-phone="<?php echo esc_attr( $pk->phone ); ?>"
                        data-plate="<?php echo esc_attr( $pk->plate_number ); ?>">
                        <?php esc_html_e( 'Modifier', 'loyal-system' ); ?>
                    </button>
                    <button type="button" class="button button-small button-link-delete ls-pickup-delete-btn"
                        data-id="<?php echo (int) $pk->id; ?>">
                        <?php esc_html_e( 'Supprimer', 'loyal-system' ); ?>
                    </button>
                </td>
            </tr>
            <tr class="ls-pickup-edit-row" id="ls-pickup-edit-<?php echo (int) $pk->id; ?>" style="display:none;">
                <td colspan="6" style="background:#f9f9f9;padding:14px 20px;">
                    <form class="ls-pickup-update-form" data-id="<?php echo (int) $pk->id; ?>" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
                        <div>
                            <label style="display:block;font-weight:600;font-size:12px;margin-bottom:3px;"><?php esc_html_e( 'Catégorie', 'loyal-system' ); ?></label>
                            <select name="category" style="width:130px;">
                                <option value=""><?php esc_html_e( '— Choisir —', 'loyal-system' ); ?></option>
                                <option value="Réparateur" <?php selected( $pk->category, 'Réparateur' ); ?>><?php esc_html_e( 'Réparateur', 'loyal-system' ); ?></option>
                                <option value="Chauffeur"  <?php selected( $pk->category, 'Chauffeur' );  ?>><?php esc_html_e( 'Chauffeur', 'loyal-system' ); ?></option>
                                <option value="Monteur"    <?php selected( $pk->category, 'Monteur' );    ?>><?php esc_html_e( 'Monteur', 'loyal-system' ); ?></option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-weight:600;font-size:12px;margin-bottom:3px;"><?php esc_html_e( 'Nom', 'loyal-system' ); ?></label>
                            <input type="text" name="name" value="<?php echo esc_attr( $pk->name ); ?>" class="regular-text">
                        </div>
                        <div>
                            <label style="display:block;font-weight:600;font-size:12px;margin-bottom:3px;"><?php esc_html_e( 'Téléphone', 'loyal-system' ); ?></label>
                            <input type="tel" name="phone" value="<?php echo esc_attr( $pk->phone ); ?>" style="width:130px;">
                        </div>
                        <div>
                            <label style="display:block;font-weight:600;font-size:12px;margin-bottom:3px;"><?php esc_html_e( 'Plaque', 'loyal-system' ); ?></label>
                            <input type="text" name="plate_number" value="<?php echo esc_attr( $pk->plate_number ); ?>" style="width:120px;">
                        </div>
                        <div>
                            <button type="submit" class="button button-primary"><?php esc_html_e( 'Enregistrer', 'loyal-system' ); ?></button>
                            <button type="button" class="button ls-pickup-cancel-btn" data-id="<?php echo (int) $pk->id; ?>"><?php esc_html_e( 'Annuler', 'loyal-system' ); ?></button>
                        </div>
                    </form>
                    <div class="ls-pickup-edit-msg-<?php echo (int) $pk->id; ?>" style="display:none;margin-top:6px;"></div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
(function($){
    // Add pickup
    $('#ls-add-pickup-form').on('submit', function(e){
        e.preventDefault();
        var $btn = $('#ls-add-pickup-btn').prop('disabled', true);
        $('#ls-pickup-spinner').addClass('is-active');
        var $msg = $('#ls-pickups-msg').hide();
        $.post(lsAdmin.ajaxUrl, {
            action:       'ls_admin_add_pickup',
            nonce:        lsAdmin.nonce,
            category:     $('#ls-pickup-category').val(),
            name:         $('#ls-pickup-name').val(),
            phone:        $('#ls-pickup-phone').val(),
            plate_number: $('#ls-pickup-plate').val()
        })
        .done(function(resp){
            if (resp.success) {
                $msg.attr('class','notice notice-success').text(resp.data.message).show();
                window.location.reload();
            } else {
                $msg.attr('class','notice notice-error').text(resp.data&&resp.data.message?resp.data.message:lsAdmin.i18n.error).show();
            }
        })
        .fail(function(){ $msg.attr('class','notice notice-error').text(lsAdmin.i18n.error).show(); })
        .always(function(){ $btn.prop('disabled',false); $('#ls-pickup-spinner').removeClass('is-active'); });
    });

    // Toggle edit row
    $(document).on('click', '.ls-pickup-edit-btn', function(){
        var id = $(this).data('id');
        $('.ls-pickup-edit-row').not('#ls-pickup-edit-'+id).hide();
        $('#ls-pickup-edit-'+id).toggle();
    });
    $(document).on('click', '.ls-pickup-cancel-btn', function(){
        $('#ls-pickup-edit-'+$(this).data('id')).hide();
    });

    // Update pickup
    $(document).on('submit', '.ls-pickup-update-form', function(e){
        e.preventDefault();
        var $form = $(this), id = $form.data('id');
        var $msg  = $('.ls-pickup-edit-msg-'+id);
        var data  = $form.serializeArray();
        data.push({name:'action',value:'ls_admin_update_pickup'},{name:'nonce',value:lsAdmin.nonce},{name:'pickup_id',value:id});
        $.post(lsAdmin.ajaxUrl, data)
        .done(function(resp){
            $msg.attr('class', resp.success ? 'notice notice-success' : 'notice notice-error')
                .text(resp.success ? resp.data.message : (resp.data&&resp.data.message?resp.data.message:lsAdmin.i18n.error))
                .show();
            if (resp.success) {
                $('.ls-pk-category-'+id).text($form.find('[name="category"]').val() || '—');
                $('.ls-pk-name-'+id).text($form.find('[name="name"]').val());
                $('.ls-pk-phone-'+id).text($form.find('[name="phone"]').val() || '—');
                $('.ls-pk-plate-'+id).text($form.find('[name="plate_number"]').val() || '—');
            }
        })
        .fail(function(){ $msg.attr('class','notice notice-error').text(lsAdmin.i18n.error).show(); });
    });

    // Delete pickup
    $(document).on('click', '.ls-pickup-delete-btn', function(){
        if (!confirm(lsAdmin.i18n.confirm_delete)) return;
        var id = $(this).data('id');
        $.post(lsAdmin.ajaxUrl, {action:'ls_admin_delete_pickup',nonce:lsAdmin.nonce,pickup_id:id})
        .done(function(resp){
            if (resp.success) {
                $('#ls-pickup-row-'+id+', #ls-pickup-edit-'+id).fadeOut();
            } else {
                $('#ls-pickups-msg').attr('class','notice notice-error')
                    .text(resp.data&&resp.data.message?resp.data.message:lsAdmin.i18n.error).show();
            }
        });
    });
})(jQuery);
</script>
