<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="ls-container ls-ticket-detail-container">

    <div class="ls-ticket-detail-header">
        <h2 class="ls-page-title">
            <?php esc_html_e( 'Ticket', 'loyal-system' ); ?> n°<?php echo (int) $ticket->id; ?>
        </h2>
        <span class="ls-badge ls-status-<?php echo esc_attr( $ticket->status ); ?>">
            <?php echo esc_html( ucfirst( str_replace( '_', ' ', $ticket->status ) ) ); ?>
        </span>
    </div>

    <div class="ls-card">
        <table class="ls-detail-table">
            <tr>
                <th><?php esc_html_e( 'Sujet',      'loyal-system' ); ?></th>
                <td><?php echo esc_html( $ticket->subject ); ?></td>
            </tr>
            <?php if ( ! empty( $ticket->category_id ) ) :
                $cat = get_term( (int) $ticket->category_id, 'product_cat' );
                if ( $cat && ! is_wp_error( $cat ) ) : ?>
            <tr>
                <th><?php esc_html_e( 'Catégorie', 'loyal-system' ); ?></th>
                <td><?php echo esc_html( $cat->name ); ?></td>
            </tr>
            <?php endif; endif; ?>
            <tr>
                <th><?php esc_html_e( 'Date',      'loyal-system' ); ?></th>
                <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $ticket->created_at ) ) ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Priorité',  'loyal-system' ); ?></th>
                <td><?php echo esc_html( ucfirst( $ticket->priority ) ); ?></td>
            </tr>
            <?php if ( ! empty( $ticket->invoice_number ) ) : ?>
            <tr>
                <th><?php esc_html_e( 'N° de facture', 'loyal-system' ); ?></th>
                <td><?php echo esc_html( $ticket->invoice_number ); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ( ! empty( $ticket->invoice_date ) ) : ?>
            <tr>
                <th><?php esc_html_e( 'Date de facture', 'loyal-system' ); ?></th>
                <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $ticket->invoice_date ) ) ); ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <div class="ls-card">
        <h3><?php esc_html_e( 'Description', 'loyal-system' ); ?></h3>
        <div class="ls-ticket-description"><?php echo nl2br( esc_html( $ticket->description ) ); ?></div>
    </div>

    <?php if ( ! empty( $ticket->images ) ) : ?>
    <div class="ls-card">
        <h3><?php esc_html_e( 'Pièces jointes', 'loyal-system' ); ?></h3>
        <?php $upload_url = wp_upload_dir()['baseurl']; ?>
        <div class="ls-image-gallery">
            <?php foreach ( $ticket->images as $img ) : ?>
                <a href="<?php echo esc_url( $upload_url . $img->file_path ); ?>" target="_blank" class="ls-image-thumb-link">
                    <img src="<?php echo esc_url( $upload_url . $img->file_path ); ?>" alt="<?php esc_attr_e( 'Image du ticket', 'loyal-system' ); ?>" class="ls-image-thumb">
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ( ! empty( $ticket->admin_notes ) ) : ?>
    <div class="ls-card ls-admin-reply">
        <h3><?php esc_html_e( 'Réponse de l\'administrateur', 'loyal-system' ); ?></h3>
        <div class="ls-admin-notes"><?php echo nl2br( esc_html( $ticket->admin_notes ) ); ?></div>
    </div>
    <?php endif; ?>

    <?php
    $back_url = LS_Settings::my_tickets_page_id() ? get_permalink( LS_Settings::my_tickets_page_id() ) : '';
    if ( $back_url ) :
    ?>
    <p><a href="<?php echo esc_url( $back_url ); ?>" class="ls-btn ls-btn-outline">&larr; <?php esc_html_e( 'Retour à mes tickets', 'loyal-system' ); ?></a></p>
    <?php endif; ?>

</div>
