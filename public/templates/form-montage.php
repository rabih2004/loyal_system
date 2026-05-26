<?php if ( ! defined( 'ABSPATH' ) ) exit;

$questions = array(
    'q_presentable'     => array( 'text' => 'Le technicien était-il présentable et en uniforme ?',                     'type' => 'yesno' ),
    'q_verified_before' => array( 'text' => 'Le technicien a-t-il vérifié les meubles avant de commencer le montage ?', 'type' => 'yesno' ),
    'q_worked_fast'     => array( 'text' => 'Durant le montage, le technicien a-t-il travaillé rapidement ?',           'type' => 'yesno' ),
    'q_tightened'       => array( 'text' => 'Le technicien a-t-il bien serré les meubles ?',                            'type' => 'yesno' ),
    'q_verified_after'  => array( 'text' => 'Après le montage, avez-vous tout vérifié avec le technicien ?',            'type' => 'yesno' ),
    'q_collaborated'    => array( 'text' => 'Le technicien a-t-il bien collaboré avec vous ?',                          'type' => 'yesno' ),
    'q_on_time'         => array( 'text' => "Le technicien est-il arrivé à l'heure prévue ?",                           'type' => 'yesno' ),
    'q_finish_time'     => array( 'text' => 'À quelle heure le technicien a-t-il terminé le montage ?',                 'type' => 'text',  'placeholder' => 'ex: 14h30' ),
    'q_rating'          => array( 'text' => 'Quelle note sur 10 attribuez-vous au travail du technicien ?',             'type' => 'number','min' => 0, 'max' => 10 ),
    'q_satisfied'       => array( 'text' => 'Êtes-vous satisfait(e) du service de montage ?',                          'type' => 'yesno' ),
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
            <div class="ls-fb-header-icon">&#128295;</div>
            <h2 class="ls-fb-header-title"><?php esc_html_e( 'Formulaire Montage', 'loyal-system' ); ?></h2>
            <p class="ls-fb-header-sub"><?php esc_html_e( 'Veuillez évaluer le service de montage reçu.', 'loyal-system' ); ?></p>
        </div>

        <div id="ls-feedback-msg" class="ls-message" role="alert" style="display:none;"></div>

        <form id="ls-feedback-form" novalidate>

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
                        <?php if ( $q['type'] === 'yesno' ) : ?>
                            <input type="radio" id="<?php echo esc_attr( $key ); ?>_oui" name="<?php echo esc_attr( $key ); ?>" value="OUI" class="ls-fb-radio">
                            <label for="<?php echo esc_attr( $key ); ?>_oui" class="ls-fb-pill ls-fb-pill--oui">OUI</label>
                            <input type="radio" id="<?php echo esc_attr( $key ); ?>_non" name="<?php echo esc_attr( $key ); ?>" value="NON" class="ls-fb-radio">
                            <label for="<?php echo esc_attr( $key ); ?>_non" class="ls-fb-pill ls-fb-pill--non">NON</label>
                        <?php elseif ( $q['type'] === 'number' ) : ?>
                            <input type="number" name="<?php echo esc_attr( $key ); ?>" class="ls-input ls-fb-number-input"
                                min="<?php echo esc_attr( $q['min'] ?? 0 ); ?>"
                                max="<?php echo esc_attr( $q['max'] ?? 10 ); ?>"
                                placeholder="0–10">
                        <?php else : ?>
                            <input type="text" name="<?php echo esc_attr( $key ); ?>" class="ls-input ls-fb-text-input"
                                placeholder="<?php echo esc_attr( $q['placeholder'] ?? '' ); ?>">
                        <?php endif; ?>
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
                <span class="ls-btn-text"><?php esc_html_e( "Envoyer l'évaluation", 'loyal-system' ); ?></span>
                <span class="ls-btn-spinner" aria-hidden="true"></span>
            </button>
        </form>
    </div>
</div>
<script>
(function($){
    $('#ls-feedback-form').on('submit', function(e){
        e.preventDefault();
        var $btn = $('#ls-feedback-btn');
        var $msg = $('#ls-feedback-msg');
        $msg.hide();

        var phone = $(this).find('[name="phone"]').val().trim();
        if ( ! phone || phone === '+224' ) {
            $msg.attr('class','ls-message ls-message-error').text('<?php esc_html_e( 'Veuillez saisir votre numéro de téléphone.', 'loyal-system' ); ?>').show();
            return;
        }

        $btn.prop('disabled', true).find('.ls-btn-spinner').show();

        var data = $(this).serialize();
        data += '&action=ls_submit_montage&nonce=<?php echo esc_js( wp_create_nonce( 'ls_public_nonce' ) ); ?>';

        $.post('<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>', data)
        .done(function(resp){
            if ( resp.success ) {
                $('#ls-feedback-form-wrap, .ls-feedback-topbar').hide();
                $('#ls-feedback-success').show();
                $('html,body').animate({ scrollTop: $('#ls-feedback-success').offset().top - 60 }, 300);
            } else {
                var msg = resp.data && resp.data.message ? resp.data.message : '<?php esc_html_e( 'Une erreur est survenue. Veuillez réessayer.', 'loyal-system' ); ?>';
                $msg.attr('class','ls-message ls-message-error').text(msg).show();
            }
        })
        .fail(function(){
            $msg.attr('class','ls-message ls-message-error').text('<?php esc_html_e( 'Une erreur est survenue. Veuillez réessayer.', 'loyal-system' ); ?>').show();
        })
        .always(function(){
            $btn.prop('disabled', false).find('.ls-btn-spinner').hide();
        });
    });
})(jQuery);
</script>
