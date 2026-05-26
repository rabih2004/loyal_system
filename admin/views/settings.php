<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap ls-admin-wrap">
    <h1><?php esc_html_e( 'TS System Settings', 'loyal-system' ); ?></h1>

    <?php if ( $saved ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
    <?php endif; ?>

    <form method="post" action="">
        <?php wp_nonce_field( 'ls_save_settings', 'ls_settings_nonce' ); ?>

        <!-- ── Portal Pages ───────────────────────────────────────────── -->
        <h2 class="title"><?php esc_html_e( 'Customer Portal Pages', 'loyal-system' ); ?></h2>
        <p class="description"><?php esc_html_e( 'Select which WordPress pages contain each portal shortcode. Links are built from page ID so they survive permalink changes.', 'loyal-system' ); ?></p>
        <p class="description" style="margin-top:4px;"><strong><?php esc_html_e( 'Shortcodes:', 'loyal-system' ); ?></strong>
            <code>[ls_login]</code> &bull; <code>[ls_dashboard]</code> &bull; <code>[ls_submit_ticket]</code> &bull; <code>[ls_my_tickets]</code> &bull; <code>[ls_ticket_detail]</code> &bull; <code>[ls_my_feedback]</code> &bull; <code>[ls_feedback_maintenance]</code> &bull; <code>[ls_feedback_delivery]</code> &bull; <code>[ls_feedback_merchant]</code> &bull; <code>[ls_form_montage]</code> &bull; <code>[ls_my_interventions]</code>
        </p>
        <table class="form-table" role="presentation">
            <?php foreach ( $portal_pages as $option_key => $label ) : ?>
            <tr>
                <th scope="row"><label><?php echo esc_html( $label ); ?></label></th>
                <td>
                    <?php
                    wp_dropdown_pages( array(
                        'name'             => $option_key,
                        'selected'         => (int) get_option( $option_key, 0 ),
                        'show_option_none' => __( '— Not set —', 'loyal-system' ),
                        'option_none_value'=> '0',
                    ) );
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <hr>

        <!-- ── Ticket Notifications ───────────────────────────────────── -->
        <h2 class="title"><?php esc_html_e( 'Ticket Notifications', 'loyal-system' ); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th><label for="ls-support-email"><?php esc_html_e( 'Support Email', 'loyal-system' ); ?></label></th>
                <td>
                    <input type="email" id="ls-support-email" name="support_email" value="<?php echo esc_attr( LS_Settings::support_email() ); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e( 'New ticket notifications are sent to this address. Defaults to the site admin email.', 'loyal-system' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="ls-ticket-sms-message"><?php esc_html_e( 'SMS de confirmation ticket', 'loyal-system' ); ?></label></th>
                <td>
                    <textarea id="ls-ticket-sms-message" name="ticket_sms_message" rows="4" class="large-text"><?php echo esc_textarea( LS_Settings::ticket_sms_message() ); ?></textarea>
                    <p class="description">
                        <?php esc_html_e( 'Message envoyé au client par SMS dès qu\'il soumet un ticket maintenance. Laissez vide pour ne pas envoyer de SMS.', 'loyal-system' ); ?><br>
                        <?php esc_html_e( 'Placeholders disponibles :', 'loyal-system' ); ?>
                        <code>{ticket_id}</code> — <?php esc_html_e( 'numéro du ticket', 'loyal-system' ); ?>,
                        <code>{customer_name}</code> — <?php esc_html_e( 'nom du client', 'loyal-system' ); ?>.
                    </p>
                </td>
            </tr>
        </table>

        <hr>

        <!-- ── Feedback SMS Notifications ────────────────────────────── -->
        <h2 class="title"><?php esc_html_e( 'Feedback SMS Notifications', 'loyal-system' ); ?></h2>
        <p class="description"><?php esc_html_e( 'Message envoyé au client par SMS dès qu\'il soumet un formulaire de feedback. Laissez vide pour ne pas envoyer de SMS. Placeholders : {customer_name}, {phone}.', 'loyal-system' ); ?></p>
        <table class="form-table" role="presentation">
            <tr>
                <th><label for="ls-fb-maintenance-sms"><?php esc_html_e( 'SMS Feedback Maintenance', 'loyal-system' ); ?></label></th>
                <td>
                    <textarea id="ls-fb-maintenance-sms" name="feedback_maintenance_sms_message" rows="3" class="large-text"><?php echo esc_textarea( LS_Settings::feedback_maintenance_sms_message() ); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><label for="ls-fb-delivery-sms"><?php esc_html_e( 'SMS Feedback Livraison', 'loyal-system' ); ?></label></th>
                <td>
                    <textarea id="ls-fb-delivery-sms" name="feedback_delivery_sms_message" rows="3" class="large-text"><?php echo esc_textarea( LS_Settings::feedback_delivery_sms_message() ); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><label for="ls-fb-montage-sms"><?php esc_html_e( 'SMS Feedback Montage', 'loyal-system' ); ?></label></th>
                <td>
                    <textarea id="ls-fb-montage-sms" name="feedback_montage_sms_message" rows="3" class="large-text"><?php echo esc_textarea( LS_Settings::feedback_montage_sms_message() ); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><label for="ls-fb-merchant-sms"><?php esc_html_e( 'SMS Feedback Magasin', 'loyal-system' ); ?></label></th>
                <td>
                    <textarea id="ls-fb-merchant-sms" name="feedback_merchant_sms_message" rows="3" class="large-text"><?php echo esc_textarea( LS_Settings::feedback_merchant_sms_message() ); ?></textarea>
                </td>
            </tr>
        </table>

        <hr>

        <!-- ── SMS Settings ───────────────────────────────────────────── -->
        <h2 class="title"><?php esc_html_e( 'SMS / OTP Settings', 'loyal-system' ); ?></h2>
        <table class="form-table" role="presentation">

            <tr>
                <th><label for="ts-sms-provider"><?php esc_html_e( 'SMS Provider', 'loyal-system' ); ?></label></th>
                <td>
                    <select name="sms_provider" id="ls-sms-provider">
                        <option value="test"          <?php selected( LS_Settings::sms_provider(), 'test' ); ?>><?php esc_html_e( 'Test (log only)', 'loyal-system' ); ?></option>
                        <option value="twilio"        <?php selected( LS_Settings::sms_provider(), 'twilio' ); ?>>Twilio</option>
                        <option value="http"          <?php selected( LS_Settings::sms_provider(), 'http' ); ?>><?php esc_html_e( 'HTTP Gateway', 'loyal-system' ); ?></option>
                        <option value="orangesmspro"  <?php selected( LS_Settings::sms_provider(), 'orangesmspro' ); ?>>Orange SMS Pro</option>
                        <option value="orangeapi"     <?php selected( LS_Settings::sms_provider(), 'orangeapi' ); ?>>Orange Developer API</option>
                    </select>
                </td>
            </tr>

            <!-- Twilio -->
            <tr class="ls-sms-twilio">
                <th><label for="ts-twilio-sid"><?php esc_html_e( 'Account SID', 'loyal-system' ); ?></label></th>
                <td><input type="text" id="ls-twilio-sid" name="twilio_sid" value="<?php echo esc_attr( LS_Settings::twilio_sid() ); ?>" class="regular-text"></td>
            </tr>
            <tr class="ls-sms-twilio">
                <th><label for="ts-twilio-token"><?php esc_html_e( 'Auth Token', 'loyal-system' ); ?></label></th>
                <td><input type="password" id="ls-twilio-token" name="twilio_token" value="<?php echo esc_attr( LS_Settings::twilio_token() ); ?>" class="regular-text"></td>
            </tr>
            <tr class="ls-sms-twilio">
                <th><label for="ts-twilio-from"><?php esc_html_e( 'From Number', 'loyal-system' ); ?></label></th>
                <td><input type="text" id="ls-twilio-from" name="twilio_from" value="<?php echo esc_attr( LS_Settings::twilio_from() ); ?>" class="regular-text" placeholder="+1234567890"></td>
            </tr>

            <!-- Orange SMS Pro -->
            <tr class="ls-sms-orangesmspro">
                <th><label for="ls-orangesmspro-token"><?php esc_html_e( 'Bearer Token', 'loyal-system' ); ?></label></th>
                <td>
                    <textarea id="ls-orangesmspro-token" name="orangesmspro_token" rows="4" class="large-text" style="font-family:monospace;font-size:11px;"><?php echo esc_textarea( LS_Settings::orangesmspro_token() ); ?></textarea>
                    <p class="description"><?php esc_html_e( 'JWT Bearer token from your Orange SMS Pro account.', 'loyal-system' ); ?></p>
                </td>
            </tr>
            <tr class="ls-sms-orangesmspro">
                <th><label for="ls-orangesmspro-sig"><?php esc_html_e( 'Signature ID', 'loyal-system' ); ?></label></th>
                <td>
                    <input type="number" id="ls-orangesmspro-sig" name="orangesmspro_signature_id" min="1" value="<?php echo esc_attr( LS_Settings::orangesmspro_signature_id() ); ?>" style="width:120px;">
                    <p class="description"><?php esc_html_e( 'Numeric signature ID (signatureId) from your Orange SMS Pro account.', 'loyal-system' ); ?></p>
                </td>
            </tr>

            <!-- Orange Developer API -->
            <tr class="ls-sms-orangeapi">
                <th><label for="ls-orangeapi-auth-key"><?php esc_html_e( 'Authorization Key', 'loyal-system' ); ?></label></th>
                <td>
                    <input type="password" id="ls-orangeapi-auth-key" name="orangeapi_auth_key" value="<?php echo esc_attr( LS_Settings::orangeapi_auth_key() ); ?>" class="large-text" autocomplete="new-password">
                    <p class="description"><?php esc_html_e( 'Base64-encoded "client_id:client_secret" from your Orange Developer app (the Authorization header value shown on the app page).', 'loyal-system' ); ?></p>
                </td>
            </tr>
            <tr class="ls-sms-orangeapi">
                <th><label for="ls-orangeapi-sender"><?php esc_html_e( 'Country Sender Number', 'loyal-system' ); ?></label></th>
                <td>
                    <input type="text" id="ls-orangeapi-sender" name="orangeapi_sender" value="<?php echo esc_attr( LS_Settings::orangeapi_sender() ); ?>" class="regular-text" placeholder="+2240000">
                    <p class="description"><?php esc_html_e( 'Orange\'s fixed sender number for your country. Guinea Conakry: +2240000 — Côte d\'Ivoire: +2250000 — Sénégal: +2210000 — Mali: +2230000.', 'loyal-system' ); ?></p>
                </td>
            </tr>
            <tr class="ls-sms-orangeapi">
                <th><label for="ls-orangeapi-sender-name"><?php esc_html_e( 'Sender Name', 'loyal-system' ); ?></label></th>
                <td>
                    <input type="text" id="ls-orangeapi-sender-name" name="orangeapi_sender_name" value="<?php echo esc_attr( LS_Settings::orangeapi_sender_name() ); ?>" class="regular-text" maxlength="11" placeholder="MonService">
                    <p class="description"><?php esc_html_e( 'Text displayed as the SMS sender (max 11 alphanumeric characters, no special chars). Must be pre-approved by Orange. Leave empty to use the default country sender number.', 'loyal-system' ); ?></p>
                </td>
            </tr>

            <!-- HTTP -->
            <tr class="ls-sms-http">
                <th><label for="ts-http-url"><?php esc_html_e( 'Gateway URL', 'loyal-system' ); ?></label></th>
                <td><input type="url" id="ls-http-url" name="http_sms_url" value="<?php echo esc_attr( LS_Settings::http_sms_url() ); ?>" class="regular-text"></td>
            </tr>
            <tr class="ls-sms-http">
                <th><label for="ts-http-params"><?php esc_html_e( 'POST Params', 'loyal-system' ); ?></label></th>
                <td>
                    <input type="text" id="ls-http-params" name="http_sms_params" value="<?php echo esc_attr( LS_Settings::http_sms_params() ); ?>" class="large-text" placeholder="to={phone}&message=Code:{code}">
                    <p class="description"><?php esc_html_e( 'Use {phone}, {code}, {message} as placeholders.', 'loyal-system' ); ?></p>
                </td>
            </tr>

            <tr>
                <th><label for="ts-otp-expiry"><?php esc_html_e( 'OTP Expiry (minutes)', 'loyal-system' ); ?></label></th>
                <td><input type="number" id="ls-otp-expiry" name="otp_expiry_minutes" min="1" max="60" value="<?php echo esc_attr( LS_Settings::otp_expiry() ); ?>" style="width:80px;"></td>
            </tr>
            <tr>
                <th><label for="ts-otp-cooldown"><?php esc_html_e( 'Resend Cooldown (seconds)', 'loyal-system' ); ?></label></th>
                <td><input type="number" id="ls-otp-cooldown" name="otp_resend_cooldown" min="10" max="300" value="<?php echo esc_attr( LS_Settings::otp_resend_cooldown() ); ?>" style="width:80px;"></td>
            </tr>

        </table>

        <hr>

        <!-- ── Invoice / Loyalty settings ────────────────────────────── -->
        <h2 class="title"><?php esc_html_e( 'Invoice / Loyalty', 'loyal-system' ); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th><label for="ts-default-invoice-currency"><?php esc_html_e( 'Default Invoice Currency', 'loyal-system' ); ?></label></th>
                <td>
                    <?php
                    $avail_currencies  = LS_Invoice::get_available_currencies();
                    $current_inv_cur   = LS_Settings::default_invoice_currency();
                    ?>
                    <select name="default_invoice_currency" id="ls-default-invoice-currency">
                        <?php foreach ( $avail_currencies as $code => $info ) : ?>
                            <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $current_inv_cur, $code ); ?>>
                                <?php echo esc_html( $code . ' — ' . $info['name'] ); ?>
                            </option>
                        <?php endforeach; ?>
                        <?php if ( empty( $avail_currencies ) ) : ?>
                            <option value="GNF" selected>GNF</option>
                        <?php endif; ?>
                    </select>
                    <p class="description"><?php esc_html_e( 'All invoice amounts are stored in this currency. Amounts entered in other currencies are auto-converted.', 'loyal-system' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="ts-invoice-credit-pct"><?php esc_html_e( 'Invoice Credit (%)', 'loyal-system' ); ?></label></th>
                <td>
                    <input type="number" id="ls-invoice-credit-pct" name="invoice_credit_pct" min="0" max="100" step="0.01" value="<?php echo esc_attr( LS_Settings::invoice_credit_pct() ); ?>" style="width:100px;">
                    <p class="description"><?php esc_html_e( 'Percentage of each invoice amount credited to the customer\'s loyalty balance. Set to 0 to disable credits.', 'loyal-system' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="ts-discount-rate"><?php esc_html_e( 'Discount Rate (%)', 'loyal-system' ); ?></label></th>
                <td><input type="number" id="ls-discount-rate" name="discount_rate" min="0" max="100" step="0.01" value="<?php echo esc_attr( LS_Settings::discount_rate() ); ?>" style="width:100px;"></td>
            </tr>
        </table>

        <?php submit_button( __( 'Save Settings', 'loyal-system' ) ); ?>
    </form>

    <hr>

    <!-- ── Test SMS ───────────────────────────────────────────────────── -->
    <h2 class="title"><?php esc_html_e( 'Test SMS', 'loyal-system' ); ?></h2>
    <p class="description"><?php esc_html_e( 'Send a test OTP to verify your SMS configuration.', 'loyal-system' ); ?></p>

    <div id="ls-sms-test-msg" style="display:none;" class="notice"></div>

    <table class="form-table" role="presentation">
        <tr>
            <th><label for="ts-sms-test-phone"><?php esc_html_e( 'Phone Number', 'loyal-system' ); ?></label></th>
            <td>
                <input type="tel" id="ls-sms-test-phone" value="+224" placeholder="+224 XXXXXXXXX" class="regular-text">
                <button type="button" id="ls-sms-test-btn" class="button"><?php esc_html_e( 'Send Test SMS', 'loyal-system' ); ?></button>
                <span class="spinner" id="ls-sms-test-spinner"></span>
                <div id="ls-sms-test-code-wrap" style="display:none;margin-top:10px;">
                    <strong><?php esc_html_e( 'Generated OTP:', 'loyal-system' ); ?></strong>
                    <code id="ls-sms-test-code" style="font-size:1.4em;padding:4px 10px;"></code>
                    <p id="ls-sms-test-note" class="description"></p>
                </div>
            </td>
        </tr>
    </table>
</div>

<script>
(function($){
    // Show/hide provider-specific fields
    function toggleProviderFields(){
        var v = $('#ls-sms-provider').val();
        $('.ls-sms-twilio').toggle(v==='twilio');
        $('.ls-sms-http').toggle(v==='http');
        $('.ls-sms-orangesmspro').toggle(v==='orangesmspro');
        $('.ls-sms-orangeapi').toggle(v==='orangeapi');
    }
    $('#ls-sms-provider').on('change', toggleProviderFields);
    toggleProviderFields();

    // Test SMS
    $('#ls-sms-test-btn').on('click', function(){
        var phone    = $.trim($('#ls-sms-test-phone').val());
        var $spinner = $('#ls-sms-test-spinner').addClass('is-active');
        var $msg     = $('#ls-sms-test-msg').hide();
        $('#ls-sms-test-code-wrap').hide();

        $.post(lsAdmin.ajaxUrl, { action:'ls_admin_test_sms', nonce:lsAdmin.nonce, phone:phone })
        .done(function(resp){
            if (resp.success) {
                $msg.attr('class','notice notice-success').text(resp.data.message).show();
                $('#ls-sms-test-code').text(resp.data.code);
                $('#ls-sms-test-note').text(resp.data.message);
                $('#ls-sms-test-code-wrap').show();
            } else {
                var msg = (resp.data&&resp.data.message)?resp.data.message:lsAdmin.i18n.error;
                $msg.attr('class','notice notice-error').text(msg).show();
            }
        })
        .fail(function(jqXHR){
            try { var r=JSON.parse(jqXHR.responseText); $msg.attr('class','notice notice-error').text(r.data&&r.data.message?r.data.message:lsAdmin.i18n.error).show(); }
            catch(e){ $msg.attr('class','notice notice-error').text(lsAdmin.i18n.error).show(); }
        })
        .always(function(){ $spinner.removeClass('is-active'); });
    });
})(jQuery);
</script>
