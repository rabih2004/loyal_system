<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="ls-container ls-invoice-lookup-container">

    <div id="ls-lookup-msg" class="ls-message" role="alert" style="display:none;"></div>

    <div class="ls-card">
        <div class="ls-card-header">
            <h2 class="ls-form-title"><?php esc_html_e( 'Check Your Balance', 'loyal-system' ); ?></h2>
        </div>
        <form id="ls-invoice-lookup-form" novalidate>
            <div class="ls-form-group">
                <label for="ts-inv-phone" class="ls-label"><?php esc_html_e( 'Phone Number', 'loyal-system' ); ?></label>
                <input type="tel" id="ls-inv-phone" name="phone" class="ls-input"
                    value="+224" placeholder="+224 XXXXXXXXX" autocomplete="tel" required>
            </div>
            <div class="ls-form-group">
                <label for="ts-inv-amount" class="ls-label"><?php esc_html_e( 'Invoice Amount', 'loyal-system' ); ?></label>
                <?php
                $currencies  = LS_Invoice::get_available_currencies();
                $default_cur = LS_Invoice::get_default_currency();
                ?>
                <input type="number" id="ls-inv-amount" name="amount" class="ls-input"
                    style="min-width:160px;" min="0" step="any" placeholder="0">
                <select id="ls-inv-currency" name="currency" style="margin-left:6px;">
                    <option value="<?php echo esc_attr( $default_cur ); ?>"><?php echo esc_html( $default_cur ); ?></option>
                    <?php foreach ( $currencies as $code => $info ) : ?>
                        <?php if ( $code !== $default_cur ) : ?>
                            <option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $code ); ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <span id="ls-inv-gnf-equiv" style="display:none;margin-left:8px;color:#666;font-size:0.9em;"></span>
            </div>
            <button type="submit" id="ls-lookup-btn" class="ls-btn ls-btn-primary ls-btn-full">
                <span class="ls-btn-text"><?php esc_html_e( 'Check Balance', 'loyal-system' ); ?></span>
                <span class="ls-btn-spinner" aria-hidden="true"></span>
            </button>
        </form>

        <div id="ls-lookup-result" style="display:none;" class="ls-lookup-result">
            <div class="ls-balance-display">
                <span class="ls-balance-label"><?php esc_html_e( 'Current Balance:', 'loyal-system' ); ?></span>
                <strong class="ls-balance-amount" id="ls-inv-balance"></strong>
            </div>
            <p id="ls-inv-customer-name" class="ls-text-muted"></p>
        </div>
    </div>
</div>

<script>
(function($){
    var tsCurrencies = <?php echo wp_json_encode( $currencies ); ?>;
    var tsDefaultCur = <?php echo wp_json_encode( $default_cur ); ?>;

    function toGNF(amount, currency) {
        if (currency === tsDefaultCur || !tsCurrencies[currency] || !tsCurrencies[currency].rate) return amount;
        return amount / tsCurrencies[currency].rate;
    }

    $('#ls-inv-amount, #ls-inv-currency').on('input change', function(){
        var amount   = parseFloat($('#ls-inv-amount').val()) || 0;
        var currency = $('#ls-inv-currency').val();
        if (currency !== tsDefaultCur && amount > 0) {
            var gnf = toGNF(amount, currency);
            $('#ls-inv-gnf-equiv').text('≈ ' + Math.round(gnf).toLocaleString() + ' ' + tsDefaultCur).show();
        } else {
            $('#ls-inv-gnf-equiv').hide();
        }
    });

    $('#ls-invoice-lookup-form').on('submit', function(e){
        e.preventDefault();
        var phone    = $.trim($('#ls-inv-phone').val());
        var $btn     = $('#ls-lookup-btn').find('.ls-btn-text');
        var $spinner = $('.ls-btn-spinner');
        var $msg     = $('#ls-lookup-msg').hide();
        var $result  = $('#ls-lookup-result').hide();

        $.post(lsPublic.ajaxUrl, {
            action : 'ls_admin_search_customer',
            nonce  : lsPublic.nonce,
            phone  : phone
        })
        .done(function(resp){
            if (resp.success && resp.data.customer) {
                var c = resp.data.customer;
                $('#ls-inv-balance').text(parseFloat(c.balance).toLocaleString() + ' ' + tsDefaultCur);
                $('#ls-inv-customer-name').text(c.full_name || '');
                $result.show();
                $('#ls-inv-phone').val('+224');
                $('#ls-inv-gnf-equiv').hide();
            } else {
                var msg = (resp.data&&resp.data.message)?resp.data.message:lsPublic.i18n.error;
                $msg.attr('class','ts-message ls-message--error').text(msg).show();
            }
        })
        .fail(function(){ $msg.attr('class','ts-message ls-message--error').text(lsPublic.i18n.error).show(); });
    });
})(jQuery);
</script>
