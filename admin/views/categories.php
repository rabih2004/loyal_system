<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap ls-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Ticket Categories', 'loyal-system' ); ?></h1>
    <hr class="wp-header-end">

    <div id="ls-categories-msg" style="display:none;" class="notice"></div>

    <div style="display:flex;gap:24px;flex-wrap:wrap;align-items:flex-start;">

        <!-- Add Category -->
        <div class="postbox" style="min-width:320px;flex:0 0 auto;">
            <div class="postbox-header"><h2><?php esc_html_e( 'Add New Category', 'loyal-system' ); ?></h2></div>
            <div class="inside">
                <form id="ls-add-category-form" novalidate>
                    <div style="margin-bottom:10px;">
                        <label style="display:block;font-weight:600;margin-bottom:4px;"><?php esc_html_e( 'Name', 'loyal-system' ); ?> *</label>
                        <input type="text" id="ls-cat-name" name="name" class="regular-text" required>
                    </div>
                    <div style="margin-bottom:10px;">
                        <label style="display:block;font-weight:600;margin-bottom:4px;"><?php esc_html_e( 'Description', 'loyal-system' ); ?></label>
                        <textarea id="ls-cat-desc" name="description" rows="2" class="large-text"></textarea>
                    </div>
                    <button type="submit" class="button button-primary" id="ls-add-cat-btn">
                        <?php esc_html_e( 'Add Category', 'loyal-system' ); ?>
                    </button>
                    <span class="spinner" id="ls-cat-spinner"></span>
                </form>
            </div>
        </div>

        <!-- Category List -->
        <div style="flex:1;min-width:300px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th><?php esc_html_e( 'Name', 'loyal-system' ); ?></th>
                        <th><?php esc_html_e( 'Description', 'loyal-system' ); ?></th>
                        <th style="width:140px;"><?php esc_html_e( 'Actions', 'loyal-system' ); ?></th>
                    </tr>
                </thead>
                <tbody id="ls-categories-tbody">
                <?php if ( empty( $categories ) ) : ?>
                    <tr id="ls-no-cats"><td colspan="4"><?php esc_html_e( 'No categories yet.', 'loyal-system' ); ?></td></tr>
                <?php else : ?>
                    <?php foreach ( $categories as $cat ) : ?>
                    <tr id="ls-cat-row-<?php echo (int) $cat->id; ?>">
                        <td><?php echo (int) $cat->id; ?></td>
                        <td><strong class="ls-cat-name-<?php echo (int) $cat->id; ?>"><?php echo esc_html( $cat->name ); ?></strong></td>
                        <td class="ls-cat-desc-<?php echo (int) $cat->id; ?>"><?php echo esc_html( $cat->description ?: '—' ); ?></td>
                        <td>
                            <button type="button" class="button button-small ls-cat-edit-btn"
                                data-id="<?php echo (int) $cat->id; ?>"
                                data-name="<?php echo esc_attr( $cat->name ); ?>"
                                data-desc="<?php echo esc_attr( $cat->description ); ?>">
                                <?php esc_html_e( 'Edit', 'loyal-system' ); ?>
                            </button>
                            <?php if ( current_user_can( 'manage_options' ) ) : ?>
                            <button type="button" class="button button-small button-link-delete ls-cat-delete-btn"
                                data-id="<?php echo (int) $cat->id; ?>">
                                <?php esc_html_e( 'Delete', 'loyal-system' ); ?>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr class="ls-cat-edit-row" id="ls-cat-edit-<?php echo (int) $cat->id; ?>" style="display:none;">
                        <td colspan="4" style="background:#f9f9f9;padding:10px 16px;">
                            <form class="ls-cat-update-form" data-id="<?php echo (int) $cat->id; ?>" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;">
                                <input type="text" name="name" value="<?php echo esc_attr( $cat->name ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Name', 'loyal-system' ); ?>">
                                <input type="text" name="description" value="<?php echo esc_attr( $cat->description ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Description', 'loyal-system' ); ?>">
                                <button type="submit" class="button button-primary"><?php esc_html_e( 'Save', 'loyal-system' ); ?></button>
                                <button type="button" class="button ls-cat-cancel-btn" data-id="<?php echo (int) $cat->id; ?>"><?php esc_html_e( 'Cancel', 'loyal-system' ); ?></button>
                            </form>
                            <div class="ls-cat-edit-msg-<?php echo (int) $cat->id; ?>" style="display:none;margin-top:6px;" class="notice"></div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function($){
    // Add category
    $('#ls-add-category-form').on('submit', function(e){
        e.preventDefault();
        var $btn     = $('#ls-add-cat-btn').prop('disabled',true);
        var $spinner = $('#ls-cat-spinner').addClass('is-active');
        var $msg     = $('#ls-categories-msg').hide();
        $.post(lsAdmin.ajaxUrl, {
            action:      'ls_admin_add_category',
            nonce:       lsAdmin.nonce,
            name:        $('#ls-cat-name').val(),
            description: $('#ls-cat-desc').val()
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
        .always(function(){ $btn.prop('disabled',false); $spinner.removeClass('is-active'); });
    });

    // Toggle edit
    $(document).on('click', '.ls-cat-edit-btn', function(){
        var id = $(this).data('id');
        $('.ls-cat-edit-row').not('#ls-cat-edit-'+id).hide();
        $('#ls-cat-edit-'+id).toggle();
    });

    $(document).on('click', '.ls-cat-cancel-btn', function(){
        $('#ls-cat-edit-'+$(this).data('id')).hide();
    });

    // Update
    $(document).on('submit', '.ls-cat-update-form', function(e){
        e.preventDefault();
        var $form = $(this);
        var id    = $form.data('id');
        var $msg  = $('.ls-cat-edit-msg-'+id);
        var data  = $form.serializeArray();
        data.push({name:'action',value:'ls_admin_update_category'},{name:'nonce',value:lsAdmin.nonce},{name:'category_id',value:id});
        $.post(lsAdmin.ajaxUrl, data)
        .done(function(resp){
            $msg.attr('class',resp.success?'notice notice-success':'notice notice-error')
                .text(resp.success ? resp.data.message : (resp.data&&resp.data.message?resp.data.message:lsAdmin.i18n.error))
                .show();
            if (resp.success) {
                $('.ls-cat-name-'+id).text($form.find('[name="name"]').val());
                $('.ls-cat-desc-'+id).text($form.find('[name="description"]').val() || '—');
            }
        })
        .fail(function(){ $msg.attr('class','notice notice-error').text(lsAdmin.i18n.error).show(); });
    });

    // Delete
    $(document).on('click', '.ls-cat-delete-btn', function(){
        if (!confirm(lsAdmin.i18n.confirm_delete)) return;
        var id   = $(this).data('id');
        var $msg = $('#ls-categories-msg');
        $.post(lsAdmin.ajaxUrl, {action:'ls_admin_delete_category',nonce:lsAdmin.nonce,category_id:id})
        .done(function(resp){
            if (resp.success) {
                $('#ls-cat-row-'+id+', #ls-cat-edit-'+id).fadeOut();
            } else {
                $msg.attr('class','notice notice-error').text(resp.data&&resp.data.message?resp.data.message:lsAdmin.i18n.error).show();
            }
        });
    });
})(jQuery);
</script>
