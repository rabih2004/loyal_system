<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap ls-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Customers', 'loyal-system' ); ?></h1>
    <hr class="wp-header-end">

    <!-- Search -->
    <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
        <input type="hidden" name="page" value="ls-customers">
        <div class="tablenav top">
            <div class="alignleft actions">
                <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>"
                    placeholder="<?php esc_attr_e( 'Search by phone, name or email…', 'loyal-system' ); ?>"
                    class="regular-text">
                <?php submit_button( __( 'Search', 'loyal-system' ), 'action', '', false ); ?>
                <?php if ( $search ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ls-customers' ) ); ?>" class="button"><?php esc_html_e( 'Clear', 'loyal-system' ); ?></a>
                <?php endif; ?>
            </div>
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php echo esc_html( sprintf( _n( '%s customer', '%s customers', $total, 'loyal-system' ), number_format_i18n( $total ) ) ); ?>
                </span>
            </div>
            <br class="clear">
        </div>
    </form>

    <div id="ls-customers-msg" style="display:none;" class="notice"></div>

    <table class="wp-list-table widefat fixed striped" id="ls-customers-table">
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th><?php esc_html_e( 'Phone', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Name', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Email', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Balance', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Verified', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Registered', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'loyal-system' ); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if ( empty( $customers ) ) : ?>
            <tr><td colspan="8"><?php esc_html_e( 'No customers found.', 'loyal-system' ); ?></td></tr>
        <?php else : ?>
            <?php
            $default_cur = LS_Invoice::get_default_currency();
            foreach ( $customers as $c ) :
            ?>
            <tr id="ls-cust-row-<?php echo (int) $c->id; ?>">
                <td><?php echo (int) $c->id; ?></td>
                <td><strong><?php echo esc_html( $c->phone ); ?></strong></td>
                <td>
                    <span class="ls-cust-name-display-<?php echo (int) $c->id; ?>"><?php echo esc_html( $c->full_name ?: '—' ); ?></span>
                </td>
                <td>
                    <span class="ls-cust-email-display-<?php echo (int) $c->id; ?>"><?php echo esc_html( $c->email ?: '—' ); ?></span>
                </td>
                <td><?php echo esc_html( number_format( (float) $c->balance, 0, '.', ' ' ) . ' ' . $default_cur ); ?></td>
                <td>
                    <?php if ( $c->is_verified ) : ?>
                        <span style="color:#1e7e34;">&#10003;</span>
                    <?php else : ?>
                        <span style="color:#999;">—</span>
                    <?php endif; ?>
                </td>
                <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $c->created_at ) ) ); ?></td>
                <td>
                    <button type="button" class="button button-small ls-cust-edit-btn"
                        data-id="<?php echo (int) $c->id; ?>"
                        data-name="<?php echo esc_attr( $c->full_name ); ?>"
                        data-email="<?php echo esc_attr( $c->email ); ?>"
                        data-address="<?php echo esc_attr( $c->address ?? '' ); ?>">
                        <?php esc_html_e( 'Edit', 'loyal-system' ); ?>
                    </button>
                    <button type="button" class="button button-small ls-cust-ledger-btn" data-id="<?php echo (int) $c->id; ?>">
                        <?php esc_html_e( 'Ledger', 'loyal-system' ); ?>
                    </button>
                    <?php if ( current_user_can( 'manage_options' ) ) : ?>
                    <button type="button" class="button button-small button-link-delete ls-cust-delete-btn"
                        data-id="<?php echo (int) $c->id; ?>">
                        <?php esc_html_e( 'Delete', 'loyal-system' ); ?>
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
            <!-- Edit row -->
            <tr class="ls-cust-edit-row" id="ls-cust-edit-<?php echo (int) $c->id; ?>" style="display:none;">
                <td colspan="8" style="background:#f9f9f9;padding:16px 20px;">
                    <strong><?php esc_html_e( 'Edit Customer', 'loyal-system' ); ?></strong>
                    <div id="ls-cust-edit-msg-<?php echo (int) $c->id; ?>" style="display:none;margin-top:8px;" class="notice"></div>
                    <form class="ls-cust-update-form" data-id="<?php echo (int) $c->id; ?>" style="margin-top:12px;">
                        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
                            <div style="flex:1;min-width:180px;">
                                <label style="display:block;font-weight:600;font-size:12px;margin-bottom:4px;"><?php esc_html_e( 'Full Name', 'loyal-system' ); ?></label>
                                <input type="text" name="full_name" value="<?php echo esc_attr( $c->full_name ); ?>" class="regular-text" style="width:100%;">
                            </div>
                            <div style="flex:1;min-width:180px;">
                                <label style="display:block;font-weight:600;font-size:12px;margin-bottom:4px;"><?php esc_html_e( 'Email', 'loyal-system' ); ?></label>
                                <input type="email" name="email" value="<?php echo esc_attr( $c->email ); ?>" class="regular-text" style="width:100%;">
                            </div>
                        </div>
                        <div style="margin-bottom:10px;">
                            <label style="display:block;font-weight:600;font-size:12px;margin-bottom:4px;"><?php esc_html_e( 'Address', 'loyal-system' ); ?></label>
                            <textarea name="address" rows="2" class="large-text" style="width:100%;max-width:560px;"><?php echo esc_textarea( $c->address ?? '' ); ?></textarea>
                        </div>
                        <div>
                            <button type="submit" class="button button-primary"><?php esc_html_e( 'Save', 'loyal-system' ); ?></button>
                            <button type="button" class="button ls-cust-cancel-btn" data-id="<?php echo (int) $c->id; ?>" style="margin-left:6px;"><?php esc_html_e( 'Cancel', 'loyal-system' ); ?></button>
                        </div>
                    </form>
                </td>
            </tr>
            <!-- Ledger row -->
            <tr class="ls-cust-ledger-row" id="ls-cust-ledger-<?php echo (int) $c->id; ?>" style="display:none;">
                <td colspan="8" style="background:#f0f6fc;padding:14px 20px;">
                    <strong><?php esc_html_e( 'Transaction History', 'loyal-system' ); ?></strong>
                    <div id="ls-cust-ledger-body-<?php echo (int) $c->id; ?>" style="margin-top:10px;">
                        <em><?php esc_html_e( 'Loading…', 'loyal-system' ); ?></em>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <?php
    // Pagination
    $total_pages = (int) ceil( $total / 50 );
    if ( $total_pages > 1 ) :
        $base_url = add_query_arg( array( 'page' => 'ls-customers', 's' => $search ), admin_url( 'admin.php' ) );
    ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <?php
            echo paginate_links( array(
                'base'    => add_query_arg( 'paged', '%#%', $base_url ),
                'format'  => '',
                'current' => $paged,
                'total'   => $total_pages,
            ) );
            ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
(function($){
    var defaultCur = <?php echo wp_json_encode( LS_Invoice::get_default_currency() ); ?>;

    // Edit button
    $(document).on('click', '.ls-cust-edit-btn', function(){
        var id = $(this).data('id');
        $('.ls-cust-edit-row').not('#ls-cust-edit-'+id).hide();
        $('#ls-cust-edit-'+id).toggle();
    });

    // Cancel
    $(document).on('click', '.ls-cust-cancel-btn', function(){
        $('#ls-cust-edit-'+$(this).data('id')).hide();
    });

    // Save
    $(document).on('submit', '.ls-cust-update-form', function(e){
        e.preventDefault();
        var $form = $(this);
        var id    = $form.data('id');
        var $msg  = $('#ls-cust-edit-msg-'+id);
        var data  = $form.serializeArray();
        data.push({name:'action',value:'ls_admin_update_customer'},{name:'nonce',value:lsAdmin.nonce},{name:'customer_id',value:id});
        $.post(lsAdmin.ajaxUrl, data)
        .done(function(resp){
            $msg.attr('class',resp.success?'notice notice-success':'notice notice-error')
                .text(resp.success ? resp.data.message : (resp.data&&resp.data.message?resp.data.message:lsAdmin.i18n.error))
                .show();
            if (resp.success) {
                var newName  = $form.find('[name="full_name"]').val();
                var newEmail = $form.find('[name="email"]').val();
                $('.ls-cust-name-display-'+id).text(newName || '—');
                $('.ls-cust-email-display-'+id).text(newEmail || '—');
            }
        })
        .fail(function(){ $msg.attr('class','notice notice-error').text(lsAdmin.i18n.error).show(); });
    });

    // Delete
    $(document).on('click', '.ls-cust-delete-btn', function(){
        if (!confirm(lsAdmin.i18n.confirm_delete)) return;
        var id  = $(this).data('id');
        var $msg = $('#ls-customers-msg');
        $.post(lsAdmin.ajaxUrl, {action:'ls_admin_delete_customer',nonce:lsAdmin.nonce,customer_id:id})
        .done(function(resp){
            if (resp.success) {
                $('#ls-cust-row-'+id+', #ls-cust-edit-'+id+', #ls-cust-ledger-'+id).fadeOut();
            } else {
                $msg.attr('class','notice notice-error').text(resp.data&&resp.data.message?resp.data.message:lsAdmin.i18n.error).show();
            }
        });
    });

    // Ledger
    $(document).on('click', '.ls-cust-ledger-btn', function(){
        var id    = $(this).data('id');
        var $row  = $('#ls-cust-ledger-'+id);
        var $body = $('#ls-cust-ledger-body-'+id);
        $('.ls-cust-ledger-row').not($row).hide();
        if ($row.is(':visible')) { $row.hide(); return; }
        $body.html('<em><?php echo esc_js( __( 'Loading…', 'loyal-system' ) ); ?></em>');
        $row.show();
        $.post(lsAdmin.ajaxUrl, {action:'ls_admin_get_ledger',nonce:lsAdmin.nonce,customer_id:id})
        .done(function(resp){
            if (resp.success && resp.data && resp.data.length) {
                var html='<table class="wp-list-table widefat striped" style="max-width:800px;"><thead><tr>'
                    +'<th><?php echo esc_js( __( 'Date', 'loyal-system' ) ); ?></th>'
                    +'<th><?php echo esc_js( __( 'Type', 'loyal-system' ) ); ?></th>'
                    +'<th><?php echo esc_js( __( 'Amount', 'loyal-system' ) ); ?></th>'
                    +'<th><?php echo esc_js( __( 'Balance After', 'loyal-system' ) ); ?></th>'
                    +'<th><?php echo esc_js( __( 'Description', 'loyal-system' ) ); ?></th>'
                    +'</tr></thead><tbody>';
                $.each(resp.data, function(i,row){
                    html += '<tr><td>'+esc(row.created_at)+'</td>'
                        +'<td>'+esc(row.type)+'</td>'
                        +'<td>'+parseFloat(row.amount).toLocaleString()+' '+defaultCur+'</td>'
                        +'<td>'+parseFloat(row.balance_after).toLocaleString()+' '+defaultCur+'</td>'
                        +'<td>'+esc(row.description)+'</td></tr>';
                });
                html += '</tbody></table>';
                $body.html(html);
            } else {
                $body.html('<em><?php echo esc_js( __( 'No transactions.', 'loyal-system' ) ); ?></em>');
            }
        })
        .fail(function(){ $body.html('<em><?php echo esc_js( __( 'Could not load.', 'loyal-system' ) ); ?></em>'); });
    });

    function esc(s){ return $('<span>').text(String(s)).html(); }
})(jQuery);
</script>
