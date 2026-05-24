<?php if ( ! defined( 'ABSPATH' ) ) exit;
$categories = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'orderby' => 'name' ) );
if ( is_wp_error( $categories ) ) { $categories = array(); }
?>
<div class="ls-container ls-submit-ticket-container">

    <?php if ( $is_logged_in && $dashboard_url ) : ?>
    <div class="ls-ticket-topbar">
        <a href="<?php echo esc_url( $dashboard_url ); ?>" class="ls-btn ls-btn-outline ls-btn-sm">
            &larr; <?php esc_html_e( 'Retour au tableau de bord', 'loyal-system' ); ?>
        </a>
    </div>
    <?php endif; ?>

    <!-- Success state (hidden until ticket submitted) -->
    <div id="ls-ticket-success" style="display:none;" class="ls-ticket-success-wrap">
        <div class="ls-ticket-success-icon">&#10003;</div>
        <h2 class="ls-ticket-success-title"><?php esc_html_e( 'Ticket envoyé !', 'loyal-system' ); ?></h2>
        <p class="ls-ticket-success-msg"><?php esc_html_e( 'Nous avons bien reçu votre demande et vous répondrons dans les plus brefs délais.', 'loyal-system' ); ?></p>
        <?php if ( $is_logged_in ) : ?>
            <?php $my_tickets_id = LS_Settings::my_tickets_page_id(); ?>
            <?php if ( $my_tickets_id ) : ?>
            <a href="<?php echo esc_url( get_permalink( $my_tickets_id ) ); ?>" class="ls-btn ls-btn-primary" style="margin-right:10px;">
                <?php esc_html_e( 'Voir mes tickets', 'loyal-system' ); ?>
            </a>
            <?php endif; ?>
            <?php if ( $dashboard_url ) : ?>
            <a href="<?php echo esc_url( $dashboard_url ); ?>" class="ls-btn ls-btn-outline">
                <?php esc_html_e( 'Retour au tableau de bord', 'loyal-system' ); ?>
            </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Error message -->
    <div id="ls-ticket-msg" class="ls-message" role="alert" style="display:none;"></div>

    <div id="ls-ticket-form-wrap" class="ls-card">
        <div class="ls-card-header">
            <h2 class="ls-form-title"><?php esc_html_e( 'Soumettre un ticket de support', 'loyal-system' ); ?></h2>
        </div>
        <form id="ls-submit-ticket-form" enctype="multipart/form-data" novalidate>
            <div class="ls-form-group">
                <label for="ls-ticket-subject" class="ls-label"><?php esc_html_e( 'Sujet', 'loyal-system' ); ?> <span class="ls-required">*</span></label>
                <input type="text" id="ls-ticket-subject" name="subject" class="ls-input" required>
            </div>

            <?php if ( ! empty( $categories ) ) : ?>
            <div class="ls-form-group">
                <label for="ls-ticket-category" class="ls-label"><?php esc_html_e( 'Catégorie', 'loyal-system' ); ?></label>
                <select id="ls-ticket-category" name="category_id" class="ls-input">
                    <option value="0"><?php esc_html_e( '— Sélectionner une catégorie —', 'loyal-system' ); ?></option>
                    <?php foreach ( $categories as $cat ) : ?>
                        <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if ( ! $is_logged_in ) : ?>
            <div class="ls-form-group">
                <label for="ls-guest-name" class="ls-label"><?php esc_html_e( 'Votre nom', 'loyal-system' ); ?></label>
                <input type="text" id="ls-guest-name" name="guest_name" class="ls-input" placeholder="<?php esc_attr_e( 'Nom complet', 'loyal-system' ); ?>">
            </div>
            <div class="ls-form-group">
                <label for="ls-guest-phone" class="ls-label"><?php esc_html_e( 'Numéro de téléphone', 'loyal-system' ); ?></label>
                <input type="tel" id="ls-guest-phone" name="phone" class="ls-input" value="+224" placeholder="+224 XXXXXXXXX">
            </div>
            <?php endif; ?>

            <?php if ( ! empty( $branches ) ) : ?>
            <div class="ls-form-group">
                <label for="ls-ticket-branch" class="ls-label"><?php esc_html_e( 'Partenaire (facultatif)', 'loyal-system' ); ?></label>
                <select id="ls-ticket-branch" name="branch_id" class="ls-input">
                    <option value="0"><?php esc_html_e( '— Sélectionner un partenaire —', 'loyal-system' ); ?></option>
                    <?php foreach ( $branches as $branch ) : ?>
                        <option value="<?php echo esc_attr( $branch->id ); ?>"><?php echo esc_html( $branch->name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="ls-form-grid-2">
                <div class="ls-form-group">
                    <label for="ls-ticket-invoice-number" class="ls-label"><?php esc_html_e( 'N° de facture (facultatif)', 'loyal-system' ); ?></label>
                    <input type="text" id="ls-ticket-invoice-number" name="invoice_number" class="ls-input"
                        placeholder="<?php esc_attr_e( 'ex: INV-00123', 'loyal-system' ); ?>">
                </div>
                <div class="ls-form-group">
                    <label for="ls-ticket-invoice-date" class="ls-label"><?php esc_html_e( 'Date de facture (facultatif)', 'loyal-system' ); ?></label>
                    <input type="date" id="ls-ticket-invoice-date" name="invoice_date" class="ls-input">
                </div>
            </div>

            <div class="ls-form-group">
                <label for="ls-ticket-description" class="ls-label"><?php esc_html_e( 'Description', 'loyal-system' ); ?></label>
                <textarea id="ls-ticket-description" name="description" class="ls-input" rows="5" placeholder="<?php esc_attr_e( 'Décrivez votre problème…', 'loyal-system' ); ?>"></textarea>
            </div>

            <div class="ls-form-group">
                <label for="ls-ticket-images" class="ls-label"><?php esc_html_e( 'Pièces jointes (facultatif)', 'loyal-system' ); ?></label>
                <input type="file" id="ls-ticket-images" name="images[]" multiple accept="image/*">
            </div>

            <button type="submit" id="ls-submit-ticket-btn" class="ls-btn ls-btn-primary ls-btn-full">
                <span class="ls-btn-text"><?php esc_html_e( 'Envoyer le ticket', 'loyal-system' ); ?></span>
                <span class="ls-btn-spinner" aria-hidden="true"></span>
            </button>
        </form>
    </div>
</div>
