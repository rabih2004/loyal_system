<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="ls-container ls-my-tickets-container">
    <h2 class="ls-page-title"><?php esc_html_e( 'Mes tickets', 'loyal-system' ); ?></h2>

    <?php if ( empty( $tickets ) ) : ?>
        <div class="ls-card ls-text-center">
            <p class="ls-text-muted"><?php esc_html_e( 'Vous n\'avez pas encore soumis de ticket.', 'loyal-system' ); ?></p>
            <?php
            $submit_url = LS_Settings::submit_ticket_page_id() ? get_permalink( LS_Settings::submit_ticket_page_id() ) : '#';
            ?>
            <a href="<?php echo esc_url( $submit_url ); ?>" class="ls-btn ls-btn-primary">
                <?php esc_html_e( 'Soumettre un ticket', 'loyal-system' ); ?>
            </a>
        </div>
    <?php else : ?>
        <div class="ls-ticket-list">
            <?php foreach ( $tickets as $t ) : ?>
            <div class="ls-ticket-item ls-card ls-status-<?php echo esc_attr( $t->status ); ?>">
                <div class="ls-ticket-header">
                    <span class="ls-ticket-id">#<?php echo (int) $t->id; ?></span>
                    <span class="ls-badge ls-status-<?php echo esc_attr( $t->status ); ?>">
                        <?php echo esc_html( ucfirst( str_replace( '_', ' ', $t->status ) ) ); ?>
                    </span>
                </div>
                <h3 class="ls-ticket-subject"><?php echo esc_html( $t->subject ); ?></h3>
                <p class="ls-ticket-meta ls-text-muted">
                    <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $t->created_at ) ) ); ?>
                    <?php if ( ! empty( $t->category_id ) ) :
                        $cat = get_term( (int) $t->category_id, 'product_cat' );
                        if ( $cat && ! is_wp_error( $cat ) ) : ?>
                            &bull; <?php echo esc_html( $cat->name ); ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </p>
                <?php
                $ticket_view_page = LS_Settings::ticket_view_page_id();
                $view_url = $ticket_view_page
                    ? add_query_arg( 'ticket_id', $t->id, get_permalink( $ticket_view_page ) )
                    : add_query_arg( 'ticket_id', $t->id );
                ?>
                <div class="ls-ticket-actions">
                    <a href="<?php echo esc_url( $view_url ); ?>" class="ls-btn ls-btn-sm ls-btn-outline">
                        <?php esc_html_e( 'Voir les détails', 'loyal-system' ); ?>
                    </a>
                    <button type="button"
                        class="ls-btn ls-btn-sm ls-btn-danger ls-delete-ticket-btn"
                        data-id="<?php echo (int) $t->id; ?>"
                        data-nonce="<?php echo esc_attr( wp_create_nonce( 'ls_public_nonce' ) ); ?>">
                        <?php esc_html_e( 'Supprimer', 'loyal-system' ); ?>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
(function($){
    $(document).on('click', '.ls-delete-ticket-btn', function(){
        if ( ! confirm('<?php echo esc_js( __( 'Supprimer ce ticket définitivement ?', 'loyal-system' ) ); ?>') ) return;
        var $btn  = $(this).prop('disabled', true);
        var $card = $btn.closest('.ls-ticket-item');
        $.post('<?php echo esc_js( admin_url('admin-ajax.php') ); ?>', {
            action:    'ls_delete_ticket',
            nonce:     $btn.data('nonce'),
            ticket_id: $btn.data('id')
        })
        .done(function(resp){
            if ( resp.success ) {
                $card.fadeOut(300, function(){ $(this).remove(); });
            } else {
                alert(resp.data && resp.data.message ? resp.data.message : '<?php echo esc_js( __( 'Une erreur est survenue.', 'loyal-system' ) ); ?>');
                $btn.prop('disabled', false);
            }
        })
        .fail(function(){ alert('<?php echo esc_js( __( 'Une erreur est survenue.', 'loyal-system' ) ); ?>'); $btn.prop('disabled', false); });
    });
})(jQuery);
</script>
