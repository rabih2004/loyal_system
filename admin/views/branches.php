<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap ls-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Branches', 'loyal-system' ); ?></h1>
    <hr class="wp-header-end">

    <div id="ls-branches-msg" style="display:none;" class="notice"></div>

    <!-- Add Branch -->
    <div class="postbox" style="max-width:700px;">
        <div class="postbox-header"><h2><?php esc_html_e( 'Add New Branch', 'loyal-system' ); ?></h2></div>
        <div class="inside">
            <form id="ls-add-branch-form" novalidate>
                <table class="form-table" role="presentation">
                    <tr>
                        <th><label for="ts-branch-name"><?php esc_html_e( 'Name', 'loyal-system' ); ?> *</label></th>
                        <td><input type="text" id="ls-branch-name" name="name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="ts-branch-address"><?php esc_html_e( 'Address', 'loyal-system' ); ?></label></th>
                        <td><textarea id="ls-branch-address" name="address" rows="2" class="large-text"></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="ts-branch-phone"><?php esc_html_e( 'Phone', 'loyal-system' ); ?></label></th>
                        <td><input type="tel" id="ls-branch-phone" name="phone" value="+224" class="regular-text"></td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary" id="ls-add-branch-btn">
                        <?php esc_html_e( 'Add Branch', 'loyal-system' ); ?>
                    </button>
                    <span class="spinner" id="ls-branch-spinner"></span>
                </p>
            </form>
        </div>
    </div>

    <!-- Branch List -->
    <table class="wp-list-table widefat fixed striped" id="ls-branch-list">
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th><?php esc_html_e( 'Name', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Address', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Phone', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'loyal-system' ); ?></th>
            </tr>
        </thead>
        <tbody id="ls-branches-tbody">
        <?php if ( empty( $branches ) ) : ?>
            <tr id="ls-no-branches"><td colspan="5"><?php esc_html_e( 'No branches yet. Add one above.', 'loyal-system' ); ?></td></tr>
        <?php else : ?>
            <?php foreach ( $branches as $b ) : ?>
            <tr id="ls-branch-row-<?php echo (int) $b->id; ?>">
                <td><?php echo (int) $b->id; ?></td>
                <td><strong class="ls-branch-name-<?php echo (int) $b->id; ?>"><?php echo esc_html( $b->name ); ?></strong></td>
                <td class="ls-branch-addr-<?php echo (int) $b->id; ?>"><?php echo esc_html( $b->address ?: '—' ); ?></td>
                <td class="ls-branch-phone-<?php echo (int) $b->id; ?>"><?php echo esc_html( $b->phone ?: '—' ); ?></td>
                <td>
                    <button type="button" class="button button-small ls-branch-edit-btn"
                        data-id="<?php echo (int) $b->id; ?>"
                        data-name="<?php echo esc_attr( $b->name ); ?>"
                        data-address="<?php echo esc_attr( $b->address ); ?>"
                        data-phone="<?php echo esc_attr( $b->phone ); ?>">
                        <?php esc_html_e( 'Edit', 'loyal-system' ); ?>
                    </button>
                    <?php if ( current_user_can( 'manage_options' ) ) : ?>
                    <button type="button" class="button button-small button-link-delete ls-branch-delete-btn"
                        data-id="<?php echo (int) $b->id; ?>">
                        <?php esc_html_e( 'Delete', 'loyal-system' ); ?>
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="ls-branch-edit-row" id="ls-branch-edit-<?php echo (int) $b->id; ?>" style="display:none;">
                <td colspan="5" style="background:#f9f9f9;padding:14px 20px;">
                    <form class="ls-branch-update-form" data-id="<?php echo (int) $b->id; ?>" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
                        <div>
                            <label style="display:block;font-weight:600;font-size:12px;margin-bottom:3px;"><?php esc_html_e( 'Name', 'loyal-system' ); ?></label>
                            <input type="text" name="name" value="<?php echo esc_attr( $b->name ); ?>" class="regular-text">
                        </div>
                        <div>
                            <label style="display:block;font-weight:600;font-size:12px;margin-bottom:3px;"><?php esc_html_e( 'Address', 'loyal-system' ); ?></label>
                            <input type="text" name="address" value="<?php echo esc_attr( $b->address ); ?>" class="regular-text">
                        </div>
                        <div>
                            <label style="display:block;font-weight:600;font-size:12px;margin-bottom:3px;"><?php esc_html_e( 'Phone', 'loyal-system' ); ?></label>
                            <input type="tel" name="phone" value="<?php echo esc_attr( $b->phone ); ?>" style="width:130px;">
                        </div>
                        <div>
                            <button type="submit" class="button button-primary"><?php esc_html_e( 'Save', 'loyal-system' ); ?></button>
                            <button type="button" class="button ls-branch-cancel-btn" data-id="<?php echo (int) $b->id; ?>"><?php esc_html_e( 'Cancel', 'loyal-system' ); ?></button>
                        </div>
                    </form>
                    <div class="ls-branch-edit-msg-<?php echo (int) $b->id; ?>" style="display:none;margin-top:6px;" class="notice"></div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
(function($){
    // Add branch
    $('#ls-add-branch-form').on('submit', function(e){
        e.preventDefault();
        var $btn     = $('#ls-add-branch-btn').prop('disabled',true);
        var $spinner = $('#ls-branch-spinner').addClass('is-active');
        var $msg     = $('#ls-branches-msg').hide();
        $.post(lsAdmin.ajaxUrl, {
            action:  'ls_admin_add_branch',
            nonce:   lsAdmin.nonce,
            name:    $('#ls-branch-name').val(),
            address: $('#ls-branch-address').val(),
            phone:   $('#ls-branch-phone').val()
        })
        .done(function(resp){
            if (resp.success) {
                $msg.attr('class','notice notice-success').text(resp.data.message).show();
                // Reload page to show new branch in table
                window.location.reload();
            } else {
                $msg.attr('class','notice notice-error').text(resp.data&&resp.data.message?resp.data.message:lsAdmin.i18n.error).show();
            }
        })
        .fail(function(){ $msg.attr('class','notice notice-error').text(lsAdmin.i18n.error).show(); })
        .always(function(){ $btn.prop('disabled',false); $spinner.removeClass('is-active'); });
    });

    // Toggle edit row
    $(document).on('click', '.ls-branch-edit-btn', function(){
        var id = $(this).data('id');
        $('.ls-branch-edit-row').not('#ls-branch-edit-'+id).hide();
        $('#ls-branch-edit-'+id).toggle();
    });

    $(document).on('click', '.ls-branch-cancel-btn', function(){
        $('#ls-branch-edit-'+$(this).data('id')).hide();
    });

    // Update branch
    $(document).on('submit', '.ls-branch-update-form', function(e){
        e.preventDefault();
        var $form = $(this);
        var id    = $form.data('id');
        var $msg  = $('.ls-branch-edit-msg-'+id);
        var data  = $form.serializeArray();
        data.push({name:'action',value:'ls_admin_update_branch'},{name:'nonce',value:lsAdmin.nonce},{name:'branch_id',value:id});
        $.post(lsAdmin.ajaxUrl, data)
        .done(function(resp){
            $msg.attr('class',resp.success?'notice notice-success':'notice notice-error')
                .text(resp.success ? resp.data.message : (resp.data&&resp.data.message?resp.data.message:lsAdmin.i18n.error))
                .show();
            if (resp.success) {
                $('.ls-branch-name-'+id).text($form.find('[name="name"]').val());
                $('.ls-branch-addr-'+id).text($form.find('[name="address"]').val() || '—');
                $('.ls-branch-phone-'+id).text($form.find('[name="phone"]').val() || '—');
            }
        })
        .fail(function(){ $msg.attr('class','notice notice-error').text(lsAdmin.i18n.error).show(); });
    });

    // Delete branch
    $(document).on('click', '.ls-branch-delete-btn', function(){
        if (!confirm(lsAdmin.i18n.confirm_delete)) return;
        var id   = $(this).data('id');
        var $msg = $('#ls-branches-msg');
        $.post(lsAdmin.ajaxUrl, {action:'ls_admin_delete_branch',nonce:lsAdmin.nonce,branch_id:id})
        .done(function(resp){
            if (resp.success) {
                $('#ls-branch-row-'+id+', #ls-branch-edit-'+id).fadeOut();
            } else {
                $msg.attr('class','notice notice-error').text(resp.data&&resp.data.message?resp.data.message:lsAdmin.i18n.error).show();
            }
        });
    });
})(jQuery);
</script>
