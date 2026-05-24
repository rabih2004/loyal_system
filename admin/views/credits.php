<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap ls-admin-wrap">
    <h1><?php esc_html_e( 'Customer Credits / Balance', 'loyal-system' ); ?></h1>
    <hr class="wp-header-end">

    <!-- ── Customer lookup ─────────────────────────────────────────────── -->
    <div class="ls-card postbox">
        <div class="postbox-header"><h2><?php esc_html_e( 'Look Up Customer', 'loyal-system' ); ?></h2></div>
        <div class="inside">
            <div id="ls-credits-msg" style="display:none;" class="notice"></div>
            <table class="form-table" role="presentation">
                <tr>
                    <th><label for="ls-credits-phone"><?php esc_html_e( 'Phone Number', 'loyal-system' ); ?></label></th>
                    <td>
                        <input type="tel" id="ls-credits-phone" value="+224" placeholder="+224 XXXXXXXXX" class="regular-text">
                        <button type="button" id="ls-credits-search-btn" class="button"><?php esc_html_e( 'Find', 'loyal-system' ); ?></button>
                        <span class="spinner" id="ls-credits-spinner"></span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- ── Results ────────────────────────────────────────────────────── -->
    <div id="ls-credits-result" style="display:none;">
        <div class="ls-card postbox">
            <div class="postbox-header"><h2><?php esc_html_e( 'Customer Details', 'loyal-system' ); ?></h2></div>
            <div class="inside">
                <table class="form-table" role="presentation">
                    <tr><th><?php esc_html_e( 'Name',    'loyal-system' ); ?></th><td id="ls-cred-name"></td></tr>
                    <tr><th><?php esc_html_e( 'Phone',   'loyal-system' ); ?></th><td id="ls-cred-phone"></td></tr>
                    <tr><th><?php esc_html_e( 'Balance', 'loyal-system' ); ?></th><td><strong id="ls-cred-balance"></strong></td></tr>
                </table>
            </div>
        </div>

        <div class="ls-card postbox">
            <div class="postbox-header"><h2><?php esc_html_e( 'Transaction History', 'loyal-system' ); ?></h2></div>
            <div class="inside">
                <table class="wp-list-table widefat fixed striped" id="ls-ledger-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Date',        'loyal-system' ); ?></th>
                            <th><?php esc_html_e( 'Type',        'loyal-system' ); ?></th>
                            <th><?php esc_html_e( 'Amount',      'loyal-system' ); ?></th>
                            <th><?php esc_html_e( 'Balance After','loyal-system' ); ?></th>
                            <th><?php esc_html_e( 'Description', 'loyal-system' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="ls-ledger-body">
                        <tr><td colspan="5"><?php esc_html_e( 'No transactions yet.', 'loyal-system' ); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function($){
    var defaultCur = <?php echo wp_json_encode( LS_Invoice::get_default_currency() ); ?>;

    $('#ls-credits-search-btn').on('click', function(){
        var phone    = $.trim($('#ls-credits-phone').val());
        var $spinner = $('#ls-credits-spinner').addClass('is-active');
        var $msg     = $('#ls-credits-msg').hide();

        $.post(lsAdmin.ajaxUrl, { action:'ls_admin_search_customer', nonce:lsAdmin.nonce, phone:phone })
        .done(function(resp){
            if (resp.success && resp.data.customer) {
                var c = resp.data.customer;
                $('#ls-cred-name').text(c.full_name || '—');
                $('#ls-cred-phone').text(c.phone);
                $('#ls-cred-balance').text(parseFloat(c.balance).toLocaleString() + ' ' + defaultCur);
                loadLedger(c.id);
                $('#ls-credits-result').show();
            } else {
                var msg = (resp.data && resp.data.message) ? resp.data.message : lsAdmin.i18n.error;
                $msg.attr('class','notice notice-error').text(msg).show();
                $('#ls-credits-result').hide();
            }
        })
        .fail(function(){ $msg.attr('class','notice notice-error').text(lsAdmin.i18n.error).show(); })
        .always(function(){ $spinner.removeClass('is-active'); });
    });

    function loadLedger(customerId) {
        // Simple inline load via get_balance — ledger loaded server-side would require another AJAX action
        // For now show a placeholder; extend with ls_admin_get_ledger if needed
        $('#ls-ledger-body').html('<tr><td colspan="5"><?php echo esc_js( __( 'Fetching…', 'loyal-system' ) ); ?></td></tr>');
        $.post(lsAdmin.ajaxUrl, { action:'ls_admin_get_ledger', nonce:lsAdmin.nonce, customer_id:customerId })
        .done(function(resp){
            if (resp.success && resp.data && resp.data.length) {
                var html='';
                $.each(resp.data, function(i,row){
                    html += '<tr>'
                        + '<td>' + esc(row.created_at) + '</td>'
                        + '<td>' + esc(row.type) + '</td>'
                        + '<td>' + parseFloat(row.amount).toLocaleString() + ' ' + defaultCur + '</td>'
                        + '<td>' + parseFloat(row.balance_after).toLocaleString() + ' ' + defaultCur + '</td>'
                        + '<td>' + esc(row.description) + '</td>'
                        + '</tr>';
                });
                $('#ls-ledger-body').html(html);
            } else {
                $('#ls-ledger-body').html('<tr><td colspan="5"><?php echo esc_js( __( 'No transactions yet.', 'loyal-system' ) ); ?></td></tr>');
            }
        })
        .fail(function(){ $('#ls-ledger-body').html('<tr><td colspan="5"><?php echo esc_js( __( 'Could not load.', 'loyal-system' ) ); ?></td></tr>'); });
    }

    function esc(s){ return $('<span>').text(String(s)).html(); }
})(jQuery);
</script>
