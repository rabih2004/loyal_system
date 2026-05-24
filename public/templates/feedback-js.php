<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<script>
(function($){
    $('#ls-feedback-form').on('submit', function(e){
        e.preventDefault();
        var $btn = $('#ls-feedback-btn');
        var $msg = $('#ls-feedback-msg');
        $msg.hide();

        // Basic validation
        var phone = $(this).find('[name="phone"]').val().trim();
        if ( ! phone || phone === '+224' ) {
            $msg.attr('class','ls-message ls-message-error').text('<?php esc_html_e( 'Please enter your phone number.', 'loyal-system' ); ?>').show();
            return;
        }

        $btn.prop('disabled', true).find('.ls-btn-spinner').show();

        var data = $(this).serialize();
        data += '&action=ls_submit_feedback&nonce=<?php echo esc_js( wp_create_nonce( 'ls_public_nonce' ) ); ?>';

        $.post('<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>', data)
        .done(function(resp){
            if ( resp.success ) {
                $('#ls-feedback-form-wrap, .ls-feedback-topbar').hide();
                $('#ls-feedback-success').show();
                $('html,body').animate({ scrollTop: $('#ls-feedback-success').offset().top - 60 }, 300);
            } else {
                var msg = resp.data && resp.data.message ? resp.data.message : '<?php esc_html_e( 'An error occurred. Please try again.', 'loyal-system' ); ?>';
                $msg.attr('class','ls-message ls-message-error').text(msg).show();
            }
        })
        .fail(function(){
            $msg.attr('class','ls-message ls-message-error').text('<?php esc_html_e( 'An error occurred. Please try again.', 'loyal-system' ); ?>').show();
        })
        .always(function(){
            $btn.prop('disabled', false).find('.ls-btn-spinner').hide();
        });
    });
})(jQuery);
</script>
