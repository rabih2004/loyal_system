<?php if ( ! defined( 'ABSPATH' ) ) exit;

$questions = array(
    'q_on_time'        => array( 'text' => 'La livraison a-t-elle été faite à temps ?',                          'type' => 'yesno' ),
    'q_fast_unload'    => array( 'text' => 'Le débarquement a-t-il été rapide ?',                                'type' => 'yesno' ),
    'q_placed'         => array( 'text' => 'Les employés ont-ils déposé les meubles à leur place ?',             'type' => 'yesno' ),
    'q_driver_present' => array( 'text' => 'Le chauffeur était-il présent avant le débarquement ?',              'type' => 'yesno' ),
    'q_cooperated'     => array( 'text' => 'Les employés ont-ils coopéré avec vous lors du débarquement ?',      'type' => 'yesno' ),
    'q_verified'       => array( 'text' => 'Avez-vous vérifié les meubles avec les employés ?',                  'type' => 'yesno' ),
    'q_match_order'    => array( 'text' => 'Les meubles livrés correspondent-ils exactement à votre commande ?', 'type' => 'yesno' ),
    'q_contacted'      => array( 'text' => 'Avez-vous été contacté avant la livraison ?',                        'type' => 'yesno' ),
    'q_protected'      => array( 'text' => 'Les articles étaient-ils bien protégés pendant le transport ?',      'type' => 'yesno' ),
    'q_no_damage'      => array( 'text' => 'Aucun dommage n\'a-t-il été causé à votre domicile ?',               'type' => 'yesno' ),
    'q_satisfied'      => array( 'text' => 'Êtes-vous satisfait(e) de notre service de livraison ?',             'type' => 'yesno' ),
);
?>
<div class="ls-container ls-feedback-container">

    <?php if ( $dashboard_url ) : ?>
    <div class="ls-feedback-topbar">
        <a href="<?php echo esc_url( $dashboard_url ); ?>" class="ls-btn ls-btn-outline ls-btn-sm">&larr; <?php esc_html_e( 'Retour au tableau de bord', 'loyal-system' ); ?></a>
    </div>
    <?php endif; ?>

    <!-- Success state -->
    <div id="ls-feedback-success" style="display:none;" class="ls-ticket-success-wrap">
        <div class="ls-ticket-success-icon">&#10003;</div>
        <h2 class="ls-ticket-success-title"><?php esc_html_e( 'Merci pour votre avis !', 'loyal-system' ); ?></h2>
        <p class="ls-ticket-success-msg"><?php esc_html_e( 'Votre évaluation a bien été enregistrée.', 'loyal-system' ); ?></p>
        <?php if ( $dashboard_url ) : ?>
        <a href="<?php echo esc_url( $dashboard_url ); ?>" class="ls-btn ls-btn-outline"><?php esc_html_e( 'Retour au tableau de bord', 'loyal-system' ); ?></a>
        <?php endif; ?>
    </div>

    <div id="ls-feedback-form-wrap">
        <!-- Header -->
        <div class="ls-fb-header">
            <div class="ls-fb-header-icon">&#128666;</div>
            <h2 class="ls-fb-header-title"><?php esc_html_e( 'Feedback Livraison', 'loyal-system' ); ?></h2>
            <p class="ls-fb-header-sub">Veuillez évaluer le service de livraison reçu.</p>
        </div>

        <div id="ls-feedback-msg" class="ls-message" role="alert" style="display:none;"></div>

        <form id="ls-feedback-form" novalidate>
            <input type="hidden" name="feedback_type" value="delivery">

            <!-- Contact info -->
            <div class="ls-card ls-fb-contact-card">
                <h3 class="ls-fb-section-label"><?php esc_html_e( 'Vos informations', 'loyal-system' ); ?></h3>
                <div class="ls-fb-contact-row">
                    <div class="ls-form-group">
                        <label class="ls-label"><?php esc_html_e( 'Numéro de téléphone', 'loyal-system' ); ?> <span class="ls-required">*</span></label>
                        <input type="tel" name="phone" class="ls-input"
                            value="<?php echo esc_attr( $is_logged_in && $customer ? $customer->phone : '+224' ); ?>"
                            placeholder="+224 XXXXXXXXX" required>
                    </div>
                    <div class="ls-form-group">
                        <label class="ls-label"><?php esc_html_e( 'Nom complet', 'loyal-system' ); ?></label>
                        <input type="text" name="full_name" class="ls-input"
                            value="<?php echo esc_attr( $is_logged_in && $customer ? $customer->full_name : '' ); ?>"
                            placeholder="<?php esc_attr_e( 'Votre nom', 'loyal-system' ); ?>">
                    </div>
                </div>
            </div>

            <!-- Questions -->
            <div class="ls-card ls-fb-questions-card">
                <h3 class="ls-fb-section-label"><?php esc_html_e( 'Évaluation', 'loyal-system' ); ?></h3>
                <?php $i = 1; foreach ( $questions as $key => $q ) : ?>
                <div class="ls-fb-question-card">
                    <div class="ls-fb-question-body">
                        <span class="ls-fb-qnum"><?php echo $i++; ?></span>
                        <p class="ls-fb-question-text"><?php echo esc_html( $q['text'] ); ?></p>
                    </div>
                    <div class="ls-fb-choices">
                        <input type="radio" id="<?php echo esc_attr( $key ); ?>_oui" name="<?php echo esc_attr( $key ); ?>" value="OUI" class="ls-fb-radio">
                        <label for="<?php echo esc_attr( $key ); ?>_oui" class="ls-fb-pill ls-fb-pill--oui">OUI</label>
                        <input type="radio" id="<?php echo esc_attr( $key ); ?>_non" name="<?php echo esc_attr( $key ); ?>" value="NON" class="ls-fb-radio">
                        <label for="<?php echo esc_attr( $key ); ?>_non" class="ls-fb-pill ls-fb-pill--non">NON</label>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Comment -->
            <div class="ls-card ls-fb-comment-card">
                <div class="ls-form-group" style="margin:0;">
                    <label class="ls-label"><?php esc_html_e( 'Commentaire (facultatif)', 'loyal-system' ); ?></label>
                    <textarea name="comment" class="ls-input" rows="3"
                        placeholder="<?php esc_attr_e( 'Avez-vous quelque chose à ajouter ?', 'loyal-system' ); ?>"></textarea>
                </div>
            </div>

            <button type="submit" id="ls-feedback-btn" class="ls-btn ls-btn-primary ls-btn-full">
                <span class="ls-btn-text"><?php esc_html_e( 'Envoyer l\'évaluation', 'loyal-system' ); ?></span>
                <span class="ls-btn-spinner" aria-hidden="true"></span>
            </button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/feedback-js.php'; ?>
