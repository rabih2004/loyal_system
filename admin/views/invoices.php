<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap ls-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Invoices', 'loyal-system' ); ?></h1>

    <button type="button" id="ls-toggle-add-invoice"
        class="page-title-action"
        style="white-space:nowrap;display:inline-flex;align-items:center;gap:6px;">
        <span class="dashicons dashicons-plus-alt2"></span>
        <?php esc_html_e( 'Add New Invoice', 'loyal-system' ); ?>
    </button>
    <hr class="wp-header-end">

    <!-- ── Add Invoice Form ────────────────────────────────────────────── -->
    <div id="ls-add-invoice-wrap" style="display:none;">
        <div class="ls-card postbox">
            <div class="postbox-header">
                <h2><?php esc_html_e( 'New Invoice', 'loyal-system' ); ?></h2>
            </div>
            <div class="inside">
                <div id="ls-add-invoice-msg" style="display:none;" class="notice"></div>

                <form id="ls-add-invoice-form" enctype="multipart/form-data" novalidate>
                    <?php wp_nonce_field( 'ls_admin_nonce', 'nonce' ); ?>
                    <input type="hidden" name="action" value="ls_admin_add_invoice">

                    <table class="form-table ls-form-table" role="presentation">

                        <tr>
                            <th><label for="ts-invoice-phone"><?php esc_html_e( 'Customer Phone', 'loyal-system' ); ?> <span class="required">*</span></label></th>
                            <td>
                                <input type="tel" id="ls-invoice-phone" name="phone" value="+224" placeholder="+224 XXXXXXXXX" class="regular-text" required>
                                <p class="description"><?php esc_html_e( 'Customer is found or created automatically by phone number.', 'loyal-system' ); ?></p>
                            </td>
                        </tr>
                        <tr id="ls-customer-balance-row" style="display:none;">
                            <th><?php esc_html_e( 'Customer Balance', 'loyal-system' ); ?></th>
                            <td>
                                <span id="ls-customer-name" style="font-weight:600;margin-right:12px;"></span>
                                <span id="ls-customer-balance-display" style="color:#2271b1;font-weight:600;"></span>
                            </td>
                        </tr>
                        <tr id="ls-redeem-row" style="display:none;">
                            <th><label for="ls-redeem-amount"><?php esc_html_e( 'Apply Credits', 'loyal-system' ); ?></label></th>
                            <td>
                                <input type="number" id="ls-redeem-amount" name="redeem_amount" value="0" min="0" step="any" style="width:160px;">
                                <span id="ls-redeem-max-hint" style="margin-left:8px;color:#666;font-size:0.9em;"></span>
                                <p class="description"><?php esc_html_e( 'Amount to deduct from customer loyalty balance. Enter 0 to skip.', 'loyal-system' ); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th><label for="ts-branch-select"><?php esc_html_e( 'Branch', 'loyal-system' ); ?></label></th>
                            <td>
                                <select id="ls-branch-select" name="branch_id" class="regular-text">
                                    <option value="0"><?php esc_html_e( '— No branch —', 'loyal-system' ); ?></option>
                                    <?php foreach ( $branches as $b ) : ?>
                                        <option value="<?php echo esc_attr( $b->id ); ?>"><?php echo esc_html( $b->name ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th><label for="ts-invoice-amount"><?php esc_html_e( 'Amount', 'loyal-system' ); ?> <span class="required">*</span></label></th>
                            <td>
                                <input type="number" id="ls-invoice-amount" name="amount" min="0" step="any" style="width:160px;" placeholder="0" required>
                                <select id="ls-invoice-currency" name="currency" style="margin-left:6px;">
                                    <option value="<?php echo esc_attr( $default_cur ); ?>"><?php echo esc_html( $default_cur ); ?></option>
                                    <?php foreach ( $currencies as $code => $info ) : ?>
                                        <?php if ( $code !== $default_cur ) : ?>
                                            <option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $code . ' — ' . $info['name'] ); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <span id="ls-gnf-equiv" style="display:none;margin-left:8px;color:#666;font-size:0.9em;"></span>
                            </td>
                        </tr>

                        <?php if ( $discount_rate > 0 ) : ?>
                        <tr>
                            <th><?php esc_html_e( 'Discount Preview', 'loyal-system' ); ?></th>
                            <td>
                                <span id="ls-discount-preview" class="description"></span>
                                <em class="description"> (<?php echo esc_html( $discount_rate ); ?>%)</em>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <tr>
                            <th><label for="ts-invoice-ref"><?php esc_html_e( 'Invoice Ref.', 'loyal-system' ); ?></label></th>
                            <td><input type="text" id="ls-invoice-ref" name="invoice_ref" class="regular-text" placeholder="INV-001"></td>
                        </tr>

                        <tr>
                            <th><label for="ts-invoice-date"><?php esc_html_e( 'Invoice Date', 'loyal-system' ); ?></label></th>
                            <td><input type="date" id="ls-invoice-date" name="invoice_date" value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>" style="width:160px;"></td>
                        </tr>

                        <tr>
                            <th><label for="ts-invoice-notes"><?php esc_html_e( 'Notes', 'loyal-system' ); ?></label></th>
                            <td><textarea id="ls-invoice-notes" name="notes" rows="3" class="large-text"></textarea></td>
                        </tr>

                        <tr>
                            <th><label for="ls-invoice-file"><?php esc_html_e( 'Invoice File', 'loyal-system' ); ?></label></th>
                            <td>
                                <input type="file" id="ls-invoice-file" name="invoice_file" accept="image/*,.pdf">
                                <p class="description"><?php esc_html_e( 'Optional — attach image or PDF of the invoice.', 'loyal-system' ); ?></p>
                            </td>
                        </tr>

                    </table>

                    <p class="submit">
                        <button type="submit" class="button button-primary" id="ls-submit-invoice-btn">
                            <?php esc_html_e( 'Add Invoice', 'loyal-system' ); ?>
                        </button>
                        <span class="spinner" id="ls-invoice-spinner"></span>
                    </p>
                </form>
            </div>
        </div>
    </div><!-- #ls-add-invoice-wrap -->

    <!-- ── Generate Verification Code ───────────────────────────────────── -->
    <div class="ls-card postbox" style="margin-top:16px;">
        <div class="postbox-header">
            <h2><?php esc_html_e( 'Generate Verification Code', 'loyal-system' ); ?></h2>
        </div>
        <div class="inside">
            <p class="description"><?php esc_html_e( 'Enter the customer\'s phone number to generate a one-time code. Read the code to the customer directly.', 'loyal-system' ); ?></p>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:8px;">
                <input type="tel" id="ls-otp-phone" value="+224" placeholder="+224 XXXXXXXXX" class="regular-text" style="width:220px;">
                <button type="button" id="ls-generate-otp-btn" class="button button-secondary"><?php esc_html_e( 'Generate Code', 'loyal-system' ); ?></button>
                <span class="spinner" id="ls-otp-spinner"></span>
            </div>
            <div id="ls-otp-result" style="display:none;margin-top:12px;padding:12px 16px;background:#fff3cd;border-left:4px solid #f0ad4e;font-size:1.3em;font-weight:700;letter-spacing:4px;"></div>
            <div id="ls-otp-error" style="display:none;" class="notice notice-error"></div>
        </div>
    </div>

    <!-- ── Search ─────────────────────────────────────────────────────── -->
    <form method="get" style="margin:12px 0;">
        <input type="hidden" name="page" value="ls-invoices">
        <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>"
               placeholder="<?php esc_attr_e( 'Search by phone, name, or ref…', 'loyal-system' ); ?>"
               style="width:280px;" class="regular-text">
        <button type="submit" class="button"><?php esc_html_e( 'Search', 'loyal-system' ); ?></button>
        <?php if ( $search ) : ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=ls-invoices' ) ); ?>" class="button"><?php esc_html_e( 'Clear', 'loyal-system' ); ?></a>
            <span class="description" style="margin-left:8px;">
                <?php printf( esc_html__( '%d result(s) for "%s"', 'loyal-system' ), $total_invoices, esc_html( $search ) ); ?>
            </span>
        <?php endif; ?>
    </form>

    <!-- ── Invoice List ────────────────────────────────────────────────── -->
    <table class="wp-list-table widefat fixed striped" id="ls-invoice-table">
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th><?php esc_html_e( 'Customer', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Branch', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Amount', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Discount', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Net Total', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Ref', 'loyal-system' ); ?></th>
                <th><?php esc_html_e( 'Date', 'loyal-system' ); ?></th>
                <th style="width:80px;"></th>
            </tr>
        </thead>
        <tbody>
        <?php if ( empty( $invoices ) ) : ?>
            <tr><td colspan="9"><?php esc_html_e( 'No invoices yet.', 'loyal-system' ); ?></td></tr>
        <?php else : ?>
            <?php foreach ( $invoices as $inv ) : ?>
            <tr class="ls-invoice-row" data-id="<?php echo (int) $inv->id; ?>">
                <td><?php echo (int) $inv->id; ?></td>
                <td>
                    <?php echo esc_html( $inv->full_name ?: '—' ); ?><br>
                    <small class="ls-text-muted"><?php echo esc_html( $inv->customer_phone ); ?></small>
                </td>
                <td><?php echo esc_html( $inv->branch_name ?: '—' ); ?></td>
                <td><?php echo esc_html( number_format( $inv->amount, 0, '.', ' ' ) . ' ' . $inv->currency ); ?></td>
                <td>
                    <?php if ( $inv->discount_amount > 0 ) : ?>
                        <span style="color:#c0392b;">−<?php echo esc_html( number_format( $inv->discount_amount, 0, '.', ' ' ) . ' ' . $inv->currency ); ?></span>
                    <?php else : ?>
                        <span style="color:#999;">—</span>
                    <?php endif; ?>
                </td>
                <td style="font-weight:600;">
                    <?php echo esc_html( number_format( $inv->amount - $inv->discount_amount, 0, '.', ' ' ) . ' ' . $inv->currency ); ?>
                </td>
                <td><?php echo esc_html( $inv->invoice_ref ?: '—' ); ?></td>
                <td><?php echo esc_html( $inv->invoice_date ?: date_i18n( get_option( 'date_format' ), strtotime( $inv->created_at ) ) ); ?></td>
                <td style="white-space:nowrap;">
                    <button type="button" class="button button-small ls-edit-invoice-btn"
                        data-id="<?php echo (int) $inv->id; ?>"
                        data-branch="<?php echo (int) $inv->branch_id; ?>"
                        data-amount="<?php echo esc_attr( $inv->amount ); ?>"
                        data-currency="<?php echo esc_attr( $inv->currency ); ?>"
                        data-ref="<?php echo esc_attr( $inv->invoice_ref ); ?>"
                        data-date="<?php echo esc_attr( $inv->invoice_date ); ?>"
                        data-notes="<?php echo esc_attr( $inv->notes ); ?>">
                        <?php esc_html_e( 'Edit', 'loyal-system' ); ?>
                    </button>
                    <?php if ( ! empty( $inv->file_path ) ) : ?>
                        <a href="<?php echo esc_url( wp_upload_dir()['baseurl'] . $inv->file_path ); ?>"
                           target="_blank"
                           class="button button-small"
                           style="margin-left:4px;"
                           title="<?php esc_attr_e( 'View attached file', 'loyal-system' ); ?>">
                            <span class="dashicons dashicons-media-default" style="line-height:26px;font-size:14px;"></span>
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="ls-invoice-edit-row" id="ls-edit-row-<?php echo (int) $inv->id; ?>" style="display:none;">
                <td colspan="9" style="background:#f9f9f9;padding:16px 20px;">
                    <div class="ls-edit-invoice-msg notice" style="display:none;"></div>
                    <table class="form-table ls-form-table" style="margin:0;" role="presentation">
                        <tr>
                            <th style="width:160px;"><?php esc_html_e( 'Branch', 'loyal-system' ); ?></th>
                            <td>
                                <select class="ls-edit-branch regular-text">
                                    <option value="0"><?php esc_html_e( '— No branch —', 'loyal-system' ); ?></option>
                                    <?php foreach ( $branches as $b ) : ?>
                                        <option value="<?php echo esc_attr( $b->id ); ?>"><?php echo esc_html( $b->name ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Amount', 'loyal-system' ); ?></th>
                            <td>
                                <input type="number" class="ls-edit-amount" min="0" step="any" style="width:140px;">
                                <select class="ls-edit-currency" style="margin-left:6px;">
                                    <option value="<?php echo esc_attr( $default_cur ); ?>"><?php echo esc_html( $default_cur ); ?></option>
                                    <?php foreach ( $currencies as $code => $info ) : ?>
                                        <?php if ( $code !== $default_cur ) : ?>
                                            <option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $code ); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Invoice Ref.', 'loyal-system' ); ?></th>
                            <td><input type="text" class="ls-edit-ref regular-text"></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Date', 'loyal-system' ); ?></th>
                            <td><input type="date" class="ls-edit-date" style="width:160px;"></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Notes', 'loyal-system' ); ?></th>
                            <td><textarea class="ls-edit-notes large-text" rows="2"></textarea></td>
                        </tr>
                    </table>
                    <p style="margin:12px 0 0;">
                        <button type="button" class="button button-primary ls-save-invoice-btn" data-id="<?php echo (int) $inv->id; ?>"><?php esc_html_e( 'Save', 'loyal-system' ); ?></button>
                        <button type="button" class="button ls-cancel-edit-btn" style="margin-left:6px;"><?php esc_html_e( 'Cancel', 'loyal-system' ); ?></button>
                        <span class="spinner ls-edit-spinner"></span>
                    </p>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <?php
    $num_pages = ceil( $total_invoices / $per_page );
    if ( $num_pages > 1 ) {
        echo '<div class="tablenav bottom"><div class="tablenav-pages">';
        echo paginate_links( array(
            'base'    => add_query_arg( 'paged', '%#%' ),
            'format'  => '',
            'current' => $paged,
            'total'   => $num_pages,
        ) );
        echo '</div></div>';
    }
    ?>
</div>

<script>
(function($){
    var tsCurrencies  = <?php echo wp_json_encode( $currencies ); ?>;
    var tsDefaultCur  = <?php echo wp_json_encode( $default_cur ); ?>;
    var tsDiscountRate = <?php echo (float) $discount_rate; ?>;

    function toBaseCurrency(amount, currency) {
        if (currency === tsDefaultCur || !tsCurrencies[currency] || !tsCurrencies[currency].rate) return amount;
        return amount / tsCurrencies[currency].rate;
    }

    function updateAddPreviews() {
        var amount   = parseFloat($('#ls-invoice-amount').val()) || 0;
        var currency = $('#ls-invoice-currency').val();
        var gnf      = toBaseCurrency(amount, currency);

        if (currency !== tsDefaultCur && amount > 0) {
            $('#ls-gnf-equiv').text('≈ ' + Math.round(gnf).toLocaleString() + ' ' + tsDefaultCur).show();
        } else {
            $('#ls-gnf-equiv').hide();
        }

        if (tsDiscountRate > 0 && gnf > 0) {
            var disc = Math.round(gnf * tsDiscountRate / 100);
            $('#ls-discount-preview').text(disc.toLocaleString() + ' ' + tsDefaultCur);
        }
    }

    // Phone lookup — show customer balance when phone is entered
    var phoneTimer = null;
    var customerBalance = 0;

    $('#ls-invoice-phone').on('input', function() {
        clearTimeout(phoneTimer);
        var phone = $(this).val().trim();
        $('#ls-customer-balance-row, #ls-redeem-row').hide();
        customerBalance = 0;
        $('#ls-redeem-amount').val(0).attr('max', 0);

        if (phone.length < 8) return;

        phoneTimer = setTimeout(function() {
            $.post(lsAdmin.ajaxUrl, { action: 'ls_admin_lookup_phone', nonce: lsAdmin.nonce, phone: phone })
            .done(function(resp) {
                if (resp.success && resp.data.found && resp.data.balance > 0) {
                    customerBalance = parseFloat(resp.data.balance) || 0;
                    $('#ls-customer-name').text(resp.data.name || '');
                    $('#ls-customer-balance-display').text(customerBalance.toLocaleString() + ' <?php echo esc_js( LS_Settings::default_invoice_currency() ); ?>');
                    $('#ls-redeem-amount').attr('max', customerBalance).val(0);
                    $('#ls-redeem-max-hint').text('<?php esc_html_e( 'Max:', 'loyal-system' ); ?> ' + customerBalance.toLocaleString());
                    $('#ls-customer-balance-row, #ls-redeem-row').show();
                } else if (resp.success && resp.data.found) {
                    $('#ls-customer-name').text(resp.data.name || '');
                    $('#ls-customer-balance-display').text('<?php esc_html_e( 'No credits', 'loyal-system' ); ?>');
                    $('#ls-customer-balance-row').show();
                }
            });
        }, 600);
    });

    // Cap redeem input at actual balance
    $('#ls-redeem-amount').on('input', function() {
        var val = parseFloat($(this).val()) || 0;
        if (val > customerBalance) $(this).val(customerBalance);
        if (val < 0) $(this).val(0);
    });

    // Toggle add form
    $('#ls-toggle-add-invoice').on('click', function() {
        $('#ls-add-invoice-wrap').slideToggle();
    });

    // Amount/currency change preview
    $('#ls-invoice-amount, #ls-invoice-currency').on('input change', updateAddPreviews);

    // Add invoice form submit
    $('#ls-add-invoice-form').on('submit', function(e){
        e.preventDefault();
        var $btn     = $('#ls-submit-invoice-btn').prop('disabled', true);
        var $spinner = $('#ls-invoice-spinner').addClass('is-active');
        var $msg     = $('#ls-add-invoice-msg').hide();

        var formData = new FormData( document.getElementById('ls-add-invoice-form') );

        $.ajax({
            url         : lsAdmin.ajaxUrl,
            type        : 'POST',
            data        : formData,
            processData : false,
            contentType : false
        })
        .done(function(resp){
            if (resp.success) {
                $msg.attr('class','notice notice-success').text(resp.data.message).show();
                document.getElementById('ls-add-invoice-form').reset();
                $('#ls-invoice-phone').val('+224');
                $('#ls-invoice-date').val(new Date().toISOString().split('T')[0]);
                $('#ls-gnf-equiv').hide();
                $('#ls-discount-preview').text('');
                $('#ls-customer-balance-row, #ls-redeem-row').hide();
                customerBalance = 0;
                // Reload to show new row
                setTimeout(function(){ window.location.reload(); }, 800);
            } else {
                var msg = (resp.data && resp.data.message) ? resp.data.message : lsAdmin.i18n.error;
                $msg.attr('class','notice notice-error').text(msg).show();
            }
        })
        .fail(function(jqXHR){
            try { var r=JSON.parse(jqXHR.responseText); $msg.attr('class','notice notice-error').text(r.data&&r.data.message?r.data.message:lsAdmin.i18n.error).show(); }
            catch(e){ $msg.attr('class','notice notice-error').text(lsAdmin.i18n.error).show(); }
        })
        .always(function(){ $btn.prop('disabled',false); $spinner.removeClass('is-active'); });
    });

    // Open edit row
    $(document).on('click', '.ls-edit-invoice-btn', function(){
        var $btn = $(this);
        var id   = $btn.data('id');

        // Close any other open rows
        $('.ls-invoice-edit-row').not('#ls-edit-row-' + id).hide();

        var $row = $('#ls-edit-row-' + id);
        if ($row.is(':visible')) {
            $row.hide();
            return;
        }

        // Populate fields
        $row.find('.ls-edit-branch').val($btn.data('branch'));
        $row.find('.ls-edit-amount').val($btn.data('amount'));
        $row.find('.ls-edit-currency').val($btn.data('currency'));
        $row.find('.ls-edit-ref').val($btn.data('ref'));
        $row.find('.ls-edit-date').val($btn.data('date'));
        $row.find('.ls-edit-notes').val($btn.data('notes'));
        $row.find('.ls-edit-invoice-msg').hide();
        $row.show();
    });

    // Cancel edit
    $(document).on('click', '.ls-cancel-edit-btn', function(){
        $(this).closest('.ls-invoice-edit-row').hide();
    });

    // Save edit
    $(document).on('click', '.ls-save-invoice-btn', function(){
        var $btn     = $(this).prop('disabled', true);
        var $row     = $(this).closest('.ls-invoice-edit-row');
        var $spinner = $row.find('.ls-edit-spinner').addClass('is-active');
        var $msg     = $row.find('.ls-edit-invoice-msg').hide();
        var id       = $btn.data('id');

        $.post(lsAdmin.ajaxUrl, {
            action       : 'ls_admin_update_invoice',
            nonce        : lsAdmin.nonce,
            invoice_id   : id,
            branch_id    : $row.find('.ls-edit-branch').val(),
            amount       : $row.find('.ls-edit-amount').val(),
            currency     : $row.find('.ls-edit-currency').val(),
            invoice_ref  : $row.find('.ls-edit-ref').val(),
            invoice_date : $row.find('.ls-edit-date').val(),
            notes        : $row.find('.ls-edit-notes').val()
        })
        .done(function(resp){
            if (resp.success) {
                window.location.reload();
            } else {
                var msg = (resp.data && resp.data.message) ? resp.data.message : lsAdmin.i18n.error;
                $msg.attr('class','notice notice-error').text(msg).show();
            }
        })
        .fail(function(jqXHR){
            try { var r=JSON.parse(jqXHR.responseText); $msg.attr('class','notice notice-error').text(r.data&&r.data.message?r.data.message:lsAdmin.i18n.error).show(); }
            catch(e){ $msg.attr('class','notice notice-error').text(lsAdmin.i18n.error).show(); }
        })
        .always(function(){ $btn.prop('disabled',false); $spinner.removeClass('is-active'); });
    });
    // ── Generate OTP ─────────────────────────────────────────────────────
    $('#ls-generate-otp-btn').on('click', function() {
        var phone = $('#ls-otp-phone').val().trim();
        if (!phone || phone === '+224') {
            $('#ls-otp-error').text('<?php esc_html_e( 'Please enter a phone number.', 'loyal-system' ); ?>').show();
            return;
        }
        $('#ls-otp-result, #ls-otp-error').hide();
        var $spinner = $('#ls-otp-spinner').addClass('is-active');
        var $btn = $(this).prop('disabled', true);

        $.post(lsAdmin.ajaxUrl, { action: 'ls_admin_generate_otp_for_phone', nonce: lsAdmin.nonce, phone: phone })
        .done(function(resp) {
            if (resp.success) {
                $('#ls-otp-result').text(resp.data.code).show();
            } else {
                $('#ls-otp-error').text(resp.data && resp.data.message ? resp.data.message : lsAdmin.i18n.error).show();
            }
        })
        .fail(function() { $('#ls-otp-error').text(lsAdmin.i18n.error).show(); })
        .always(function() { $btn.prop('disabled', false); $spinner.removeClass('is-active'); });
    });
})(jQuery);
</script>
