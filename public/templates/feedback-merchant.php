<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
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
            <div class="ls-fb-header-icon">&#127978;</div>
            <h2 class="ls-fb-header-title"><?php esc_html_e( 'Votre Avis', 'loyal-system' ); ?></h2>
            <p class="ls-fb-header-sub"><?php esc_html_e( 'Évaluez votre expérience en magasin.', 'loyal-system' ); ?></p>
        </div>

        <!-- Stars display (decorative) -->
        <div class="ls-merchant-stars" aria-hidden="true">
            <span>&#11088;</span><span>&#11088;</span><span>&#11088;</span><span>&#11088;</span><span>&#11088;</span>
        </div>

        <div id="ls-feedback-msg" class="ls-message" role="alert" style="display:none;"></div>

        <form id="ls-merchant-feedback-form" novalidate>
            <input type="hidden" name="action" value="ls_submit_merchant_feedback">
            <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'ls_public_nonce' ) ); ?>">

            <!-- Contact info -->
            <div class="ls-card ls-fb-contact-card">
                <h3 class="ls-fb-section-label"><?php esc_html_e( 'Vos Coordonnées', 'loyal-system' ); ?></h3>
                <div class="ls-fb-contact-row">
                    <div class="ls-form-group">
                        <label class="ls-label"><?php esc_html_e( 'Téléphone', 'loyal-system' ); ?> <span class="ls-required">*</span></label>
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

                <!-- Branch / merchant selector -->
                <?php if ( ! empty( $branches ) ) : ?>
                <div class="ls-form-group">
                    <label class="ls-label"><?php esc_html_e( 'Magasin / Enseigne', 'loyal-system' ); ?> <span class="ls-required">*</span></label>
                    <select name="branch_id" class="ls-input" required>
                        <option value=""><?php esc_html_e( '— Choisir un magasin —', 'loyal-system' ); ?></option>
                        <?php foreach ( $branches as $b ) : ?>
                        <option value="<?php echo esc_attr( $b->id ); ?>"><?php echo esc_html( $b->name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <!-- Questions -->
            <div class="ls-card ls-fb-questions-card">
                <h3 class="ls-fb-section-label"><?php esc_html_e( 'Évaluation', 'loyal-system' ); ?></h3>

                <!-- Q1: Personnel accueillant -->
                <div class="ls-fb-question-card">
                    <div class="ls-fb-question-body">
                        <span class="ls-fb-qnum">1</span>
                        <p class="ls-fb-question-text">Personnel accueillant</p>
                    </div>
                    <div class="ls-fb-choices">
                        <input type="radio" id="q_welcoming_oui" name="q_welcoming" value="OUI" class="ls-fb-radio">
                        <label for="q_welcoming_oui" class="ls-fb-pill ls-fb-pill--oui">Oui</label>
                        <input type="radio" id="q_welcoming_non" name="q_welcoming" value="NON" class="ls-fb-radio">
                        <label for="q_welcoming_non" class="ls-fb-pill ls-fb-pill--non">Non</label>
                    </div>
                </div>

                <!-- Q2: Service rapide -->
                <div class="ls-fb-question-card">
                    <div class="ls-fb-question-body">
                        <span class="ls-fb-qnum">2</span>
                        <p class="ls-fb-question-text">Service rapide</p>
                    </div>
                    <div class="ls-fb-choices">
                        <input type="radio" id="q_fast_oui" name="q_fast" value="OUI" class="ls-fb-radio">
                        <label for="q_fast_oui" class="ls-fb-pill ls-fb-pill--oui">Oui</label>
                        <input type="radio" id="q_fast_non" name="q_fast" value="NON" class="ls-fb-radio">
                        <label for="q_fast_non" class="ls-fb-pill ls-fb-pill--non">Non</label>
                    </div>
                </div>

                <!-- Q3: Qualité produit (slider) -->
                <div class="ls-fb-question-card ls-fb-question-card--slider">
                    <div class="ls-fb-question-body">
                        <span class="ls-fb-qnum">3</span>
                        <p class="ls-fb-question-text">Qualité produit</p>
                    </div>
                    <div class="ls-fb-slider-wrap">
                        <div class="ls-fb-slider-labels">
                            <span>Faible</span><span>Excellent</span>
                        </div>
                        <input type="range" name="q_quality" min="1" max="10" value="5" class="ls-fb-slider" id="q_quality">
                        <div class="ls-fb-slider-val" id="q_quality_val">5 / 10</div>
                    </div>
                </div>

                <!-- Q4: Rapport qualité / prix (slider) -->
                <div class="ls-fb-question-card ls-fb-question-card--slider">
                    <div class="ls-fb-question-body">
                        <span class="ls-fb-qnum">4</span>
                        <p class="ls-fb-question-text">Rapport qualité / prix</p>
                    </div>
                    <div class="ls-fb-slider-wrap">
                        <div class="ls-fb-slider-labels">
                            <span>Bas</span><span>Élevé</span>
                        </div>
                        <input type="range" name="q_value" min="1" max="10" value="5" class="ls-fb-slider" id="q_value">
                        <div class="ls-fb-slider-val" id="q_value_val">5 / 10</div>
                    </div>
                </div>

                <!-- Q5: Recommanderiez-vous ? -->
                <div class="ls-fb-question-card">
                    <div class="ls-fb-question-body">
                        <span class="ls-fb-qnum">5</span>
                        <p class="ls-fb-question-text">Recommanderiez-vous notre enseigne ?</p>
                    </div>
                    <div class="ls-fb-choices">
                        <input type="radio" id="q_recommend_oui" name="q_recommend" value="OUI" class="ls-fb-radio">
                        <label for="q_recommend_oui" class="ls-fb-pill ls-fb-pill--oui">Oui</label>
                        <input type="radio" id="q_recommend_non" name="q_recommend" value="NON" class="ls-fb-radio">
                        <label for="q_recommend_non" class="ls-fb-pill ls-fb-pill--non">Non</label>
                    </div>
                </div>

                <!-- Comment -->
                <div class="ls-form-group" style="margin-top:16px;">
                    <textarea name="comment" class="ls-input" rows="3"
                        placeholder="Commentaire (optionnel)"></textarea>
                </div>
            </div>

            <button type="submit" id="ls-merchant-feedback-btn" class="ls-btn ls-btn-primary ls-btn-full">
                <span class="ls-btn-text"><?php esc_html_e( 'Envoyer mon avis', 'loyal-system' ); ?></span>
                <span class="ls-btn-spinner" aria-hidden="true"></span>
            </button>
        </form>
    </div>
</div>

<script>
(function($){
    // Live slider value display
    $('#q_quality, #q_value').on('input', function(){
        $('#' + this.id + '_val').text(this.value + ' / 10');
    });

    $('#ls-merchant-feedback-form').on('submit', function(e){
        e.preventDefault();
        var $btn = $('#ls-merchant-feedback-btn');
        var $msg = $('#ls-feedback-msg');
        $msg.hide();

        var phone = $(this).find('[name="phone"]').val().trim();
        if ( ! phone || phone === '+224' ) {
            $msg.attr('class','ls-message ls-message--error').text('<?php esc_html_e( 'Please enter your phone number.', 'loyal-system' ); ?>').show();
            return;
        }

        $btn.prop('disabled', true).find('.ls-btn-spinner').show();

        $.post('<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>', $(this).serialize())
        .done(function(resp){
            if ( resp.success ) {
                $('#ls-feedback-form-wrap, .ls-feedback-topbar').hide();
                $('#ls-feedback-success').show();
                $('html,body').animate({ scrollTop: $('#ls-feedback-success').offset().top - 60 }, 300);
            } else {
                var msg = resp.data && resp.data.message ? resp.data.message : '<?php esc_html_e( 'An error occurred.', 'loyal-system' ); ?>';
                $msg.attr('class','ls-message ls-message--error').text(msg).show();
            }
        })
        .fail(function(){
            $msg.attr('class','ls-message ls-message--error').text('<?php esc_html_e( 'An error occurred.', 'loyal-system' ); ?>').show();
        })
        .always(function(){
            $btn.prop('disabled', false).find('.ls-btn-spinner').hide();
        });
    });
})(jQuery);
</script>
