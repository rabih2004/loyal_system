<?php if ( ! defined( 'ABSPATH' ) ) exit;

$dashboard_url = $dashboard_url ?? '';
$is_logged_in  = $is_logged_in  ?? false;
$customer      = $customer      ?? null;

if ( ! function_exists( 'ls_fb_pill_choices' ) ) :
function ls_fb_pill_choices( string $name, array $options ) {
    foreach ( $options as $value => $color_class ) :
        $id = esc_attr( $name . '_' . preg_replace( '/[^a-z0-9]/', '_', strtolower( $value ) ) );
        ?>
        <input type="radio" id="<?php echo $id; ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" class="ls-fb-radio">
        <label for="<?php echo $id; ?>" class="ls-fb-pill <?php echo esc_attr( $color_class ); ?>"><?php echo esc_html( $value ); ?></label>
        <?php
    endforeach;
}
endif;
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
            <div class="ls-fb-header-icon">&#128297;</div>
            <h2 class="ls-fb-header-title"><?php esc_html_e( 'Feedback Montage', 'loyal-system' ); ?></h2>
            <p class="ls-fb-header-sub">Merci d'avoir choisi nos services pour le montage de votre meuble.<br>Votre satisfaction est importante pour nous.<br>Nous aimerions connaître votre avis concernant notre intervention.</p>
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

                <?php
                $yesno = array(
                    'q_verified_before' => 'Le technicien a-t-il vérifié les meubles avant de commencer le montage ?',
                    'q_worked_fast'     => 'Durant le montage, le technicien a-t-il travaillé rapidement ?',
                    'q_assembled_well'  => 'Le meuble a-t-il été monté correctement et proprement ?',
                    'q_verified_after'  => 'Après le montage, avez-vous tout vérifié avec le technicien ?',
                    'q_collaborated'    => 'Le technicien a-t-il bien collaboré avec vous ?',
                    'q_on_time'         => "Le technicien est-il arrivé à l'heure prévue ?",
                );
                $i = 1;
                foreach ( $yesno as $key => $text ) : ?>
                <div class="ls-fb-question-card">
                    <div class="ls-fb-question-body">
                        <span class="ls-fb-qnum"><?php echo $i++; ?></span>
                        <p class="ls-fb-question-text"><?php echo esc_html( $text ); ?></p>
                    </div>
                    <div class="ls-fb-choices">
                        <input type="radio" id="<?php echo esc_attr( $key ); ?>_oui" name="<?php echo esc_attr( $key ); ?>" value="OUI" class="ls-fb-radio">
                        <label for="<?php echo esc_attr( $key ); ?>_oui" class="ls-fb-pill ls-fb-pill--oui">OUI</label>
                        <input type="radio" id="<?php echo esc_attr( $key ); ?>_non" name="<?php echo esc_attr( $key ); ?>" value="NON" class="ls-fb-radio">
                        <label for="<?php echo esc_attr( $key ); ?>_non" class="ls-fb-pill ls-fb-pill--non">NON</label>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Q7: Heure de fin -->
                <div class="ls-fb-question-card">
                    <div class="ls-fb-question-body">
                        <span class="ls-fb-qnum"><?php echo $i++; ?></span>
                        <p class="ls-fb-question-text"><?php esc_html_e( 'À quelle heure le technicien a-t-il terminé le montage ?', 'loyal-system' ); ?></p>
                    </div>
                    <div class="ls-fb-choices">
                        <input type="text" name="q_finish_time" class="ls-input ls-fb-text-input" placeholder="ex: 14h30">
                    </div>
                </div>

                <!-- Q8: Professionnalisme (colored multi-choice) -->
                <div class="ls-fb-question-card ls-fb-question-card--wrap">
                    <div class="ls-fb-question-body">
                        <span class="ls-fb-qnum"><?php echo $i++; ?></span>
                        <p class="ls-fb-question-text"><?php esc_html_e( 'Comment évaluez-vous le professionnalisme de notre équipe ?', 'loyal-system' ); ?></p>
                    </div>
                    <div class="ls-fb-choices ls-fb-choices--wrap">
                        <?php ls_fb_pill_choices( 'q_professionalism', array(
                            'Excellent' => 'ls-fb-pill--green',
                            'Bon'       => 'ls-fb-pill--lightgreen',
                            'Moyen'     => 'ls-fb-pill--orange',
                            'Mauvais'   => 'ls-fb-pill--red',
                        ) ); ?>
                    </div>
                </div>

                <!-- Q9: Satisfaction montage (colored multi-choice) -->
                <div class="ls-fb-question-card ls-fb-question-card--wrap">
                    <div class="ls-fb-question-body">
                        <span class="ls-fb-qnum"><?php echo $i++; ?></span>
                        <p class="ls-fb-question-text"><?php esc_html_e( 'Êtes-vous satisfait du montage effectué ?', 'loyal-system' ); ?></p>
                    </div>
                    <div class="ls-fb-choices ls-fb-choices--wrap">
                        <?php ls_fb_pill_choices( 'q_satisfied', array(
                            'Très satisfait' => 'ls-fb-pill--green',
                            'Satisfait'      => 'ls-fb-pill--lightgreen',
                            'Peu satisfait'  => 'ls-fb-pill--orange',
                            'Pas satisfait'  => 'ls-fb-pill--red',
                        ) ); ?>
                    </div>
                </div>

                <!-- Q10: Délai d'intervention -->
                <div class="ls-fb-question-card">
                    <div class="ls-fb-question-body">
                        <span class="ls-fb-qnum"><?php echo $i++; ?></span>
                        <p class="ls-fb-question-text"><?php esc_html_e( "Le délai d'intervention vous a-t-il convenu ?", 'loyal-system' ); ?></p>
                    </div>
                    <div class="ls-fb-choices">
                        <input type="radio" id="q_delay_ok_oui" name="q_delay_ok" value="OUI" class="ls-fb-radio">
                        <label for="q_delay_ok_oui" class="ls-fb-pill ls-fb-pill--oui">OUI</label>
                        <input type="radio" id="q_delay_ok_non" name="q_delay_ok" value="NON" class="ls-fb-radio">
                        <label for="q_delay_ok_non" class="ls-fb-pill ls-fb-pill--non">NON</label>
                    </div>
                </div>

                <!-- Q11: Recommandation (colored multi-choice) -->
                <div class="ls-fb-question-card ls-fb-question-card--wrap">
                    <div class="ls-fb-question-body">
                        <span class="ls-fb-qnum"><?php echo $i++; ?></span>
                        <p class="ls-fb-question-text"><?php esc_html_e( 'Recommanderez-vous nos services à votre entourage ?', 'loyal-system' ); ?></p>
                    </div>
                    <div class="ls-fb-choices ls-fb-choices--wrap">
                        <?php ls_fb_pill_choices( 'q_recommend', array(
                            'Oui'      => 'ls-fb-pill--green',
                            'Peut-être'=> 'ls-fb-pill--orange',
                            'Non'      => 'ls-fb-pill--red',
                        ) ); ?>
                    </div>
                </div>

            </div>

            <!-- Commentaires -->
            <div class="ls-card ls-fb-comment-card">
                <div class="ls-form-group" style="margin:0;">
                    <label class="ls-label"><?php esc_html_e( 'Commentaires ou suggestions (facultatif)', 'loyal-system' ); ?></label>
                    <textarea name="comment" class="ls-input" rows="3"
                        placeholder="<?php esc_attr_e( 'Partagez vos remarques ou suggestions...', 'loyal-system' ); ?>"></textarea>
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
