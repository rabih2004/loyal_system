<?php if (! defined('ABSPATH')) exit;

$dashboard_url = $dashboard_url ?? '';
$is_logged_in  = $is_logged_in  ?? false;
$customer      = $customer      ?? null;

// Helper: render a group of colored pill radio buttons
if ( ! function_exists( 'ls_fb_pill_choices' ) ) :
function ls_fb_pill_choices( string $name, array $options ) {
    foreach ( $options as $value => $color_class ) :
        $id = esc_attr( $name . '_' . preg_replace('/[^a-z0-9]/', '_', strtolower($value)) );
        ?>
        <input type="radio" id="<?php echo $id; ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" class="ls-fb-radio">
        <label for="<?php echo $id; ?>" class="ls-fb-pill <?php echo esc_attr($color_class); ?>"><?php echo esc_html($value); ?></label>
        <?php
    endforeach;
}
endif;
?>
<div class="ls-container ls-feedback-container">

    <?php if ($dashboard_url) : ?>
        <div class="ls-feedback-topbar">
            <a href="<?php echo esc_url($dashboard_url); ?>" class="ls-btn ls-btn-outline ls-btn-sm">&larr; <?php esc_html_e('Retour au tableau de bord', 'loyal-system'); ?></a>
        </div>
    <?php endif; ?>

    <!-- Success state -->
    <div id="ls-feedback-success" style="display:none;" class="ls-ticket-success-wrap">
        <div class="ls-ticket-success-icon">&#10003;</div>
        <h2 class="ls-ticket-success-title"><?php esc_html_e('Merci pour votre avis !', 'loyal-system'); ?></h2>
        <p class="ls-ticket-success-msg"><?php esc_html_e('Votre évaluation a bien été enregistrée.', 'loyal-system'); ?></p>
        <?php if ($dashboard_url) : ?>
            <a href="<?php echo esc_url($dashboard_url); ?>" class="ls-btn ls-btn-outline"><?php esc_html_e('Retour au tableau de bord', 'loyal-system'); ?></a>
        <?php endif; ?>
    </div>

    <div id="ls-feedback-form-wrap">
        <!-- Header -->
        <div class="ls-fb-header">
            <div class="ls-fb-header-icon">&#128295;</div>
            <h2 class="ls-fb-header-title"><?php esc_html_e('Feedback Maintenance', 'loyal-system'); ?></h2>
            <p class="ls-fb-header-sub">Nous vous remercions d'avoir fait appel à nos services de dépannage.<br>Afin d'améliorer la qualité de nos prestations, nous souhaiterions recueillir votre avis.</p>
        </div>

        <div id="ls-feedback-msg" class="ls-message" role="alert" style="display:none;"></div>

        <form id="ls-feedback-form" novalidate>
            <input type="hidden" name="feedback_type" value="maintenance">

            <!-- Contact info -->
            <div class="ls-card ls-fb-contact-card">
                <h3 class="ls-fb-section-label"><?php esc_html_e('Vos informations', 'loyal-system'); ?></h3>
                <div class="ls-fb-contact-row">
                    <div class="ls-form-group">
                        <label class="ls-label"><?php esc_html_e('Numéro de téléphone', 'loyal-system'); ?> <span class="ls-required">*</span></label>
                        <input type="tel" name="phone" class="ls-input"
                            value="<?php echo esc_attr($is_logged_in && $customer ? $customer->phone : '+224'); ?>"
                            placeholder="+224 XXXXXXXXX" required>
                    </div>
                    <div class="ls-form-group">
                        <label class="ls-label"><?php esc_html_e('Nom complet', 'loyal-system'); ?></label>
                        <input type="text" name="full_name" class="ls-input"
                            value="<?php echo esc_attr($is_logged_in && $customer ? $customer->full_name : ''); ?>"
                            placeholder="<?php esc_attr_e('Votre nom', 'loyal-system'); ?>">
                    </div>
                </div>
            </div>

            <!-- Section 1 : Satisfaction Générale -->
            <div class="ls-card ls-fb-questions-card">
                <h3 class="ls-fb-section-label">1. <?php esc_html_e('Satisfaction Générale', 'loyal-system'); ?></h3>
                <div class="ls-fb-question-card ls-fb-question-card--wrap">
                    <div class="ls-fb-question-body">
                        <p class="ls-fb-question-text"><?php esc_html_e('Comment évaluez-vous notre service ?', 'loyal-system'); ?></p>
                    </div>
                    <div class="ls-fb-choices ls-fb-choices--wrap">
                        <?php ls_fb_pill_choices('q_general', [
                            'Excellent' => 'ls-fb-pill--green',
                            'Très bon'  => 'ls-fb-pill--lightgreen',
                            'Bon'       => 'ls-fb-pill--blue',
                            'Moyen'     => 'ls-fb-pill--orange',
                            'Mauvais'   => 'ls-fb-pill--red',
                        ]); ?>
                    </div>
                </div>
            </div>

            <!-- Section 2 : Qualité du Dépannage -->
            <div class="ls-card ls-fb-questions-card">
                <h3 class="ls-fb-section-label">2. <?php esc_html_e('Qualité du Dépannage', 'loyal-system'); ?></h3>

                <div class="ls-fb-question-card ls-fb-question-card--wrap">
                    <div class="ls-fb-question-body">
                        <p class="ls-fb-question-text"><?php esc_html_e('Le problème a-t-il été résolu ?', 'loyal-system'); ?></p>
                    </div>
                    <div class="ls-fb-choices ls-fb-choices--wrap">
                        <?php ls_fb_pill_choices('q_problem_resolved', [
                            'Oui complètement' => 'ls-fb-pill--green',
                            'Partiellement'    => 'ls-fb-pill--orange',
                            'Non'              => 'ls-fb-pill--red',
                        ]); ?>
                    </div>
                </div>

                <div class="ls-fb-question-card ls-fb-question-card--wrap">
                    <div class="ls-fb-question-body">
                        <p class="ls-fb-question-text"><?php esc_html_e('Êtes-vous satisfait de la qualité du dépannage ?', 'loyal-system'); ?></p>
                    </div>
                    <div class="ls-fb-choices ls-fb-choices--wrap">
                        <?php ls_fb_pill_choices('q_quality_satisfied', [
                            'Très satisfait' => 'ls-fb-pill--green',
                            'Satisfait'      => 'ls-fb-pill--lightgreen',
                            'Peu satisfait'  => 'ls-fb-pill--orange',
                            'Pas satisfait'  => 'ls-fb-pill--red',
                        ]); ?>
                    </div>
                </div>
            </div>

            <!-- Section 3 : Intervention du réparateur -->
            <div class="ls-card ls-fb-questions-card">
                <h3 class="ls-fb-section-label">3. <?php esc_html_e('Intervention du réparateur', 'loyal-system'); ?></h3>
                <p class="ls-fb-section-sub"><?php esc_html_e('Le réparateur était-il :', 'loyal-system'); ?></p>

                <?php foreach ([
                    'q_polite'            => 'Poli et respectueux',
                    'q_professional'      => 'Professionnel',
                    'q_punctual'          => 'Ponctuel',
                    'q_clear_explanation' => 'Clair dans ses explications',
                ] as $key => $label) : ?>
                    <div class="ls-fb-question-card">
                        <div class="ls-fb-question-body">
                            <p class="ls-fb-question-text"><?php echo esc_html($label); ?></p>
                        </div>
                        <div class="ls-fb-choices">
                            <input type="radio" id="<?php echo esc_attr($key); ?>_oui" name="<?php echo esc_attr($key); ?>" value="OUI" class="ls-fb-radio">
                            <label for="<?php echo esc_attr($key); ?>_oui" class="ls-fb-pill ls-fb-pill--oui">OUI</label>
                            <input type="radio" id="<?php echo esc_attr($key); ?>_non" name="<?php echo esc_attr($key); ?>" value="NON" class="ls-fb-radio">
                            <label for="<?php echo esc_attr($key); ?>_non" class="ls-fb-pill ls-fb-pill--non">NON</label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Section 4 : Délais et Communication -->
            <div class="ls-card ls-fb-questions-card">
                <h3 class="ls-fb-section-label">4. <?php esc_html_e('Délais et Communication', 'loyal-system'); ?></h3>

                <?php foreach ([
                    'q_on_time'        => 'Le réparateur est-il arrivé à l\'heure prévue ?',
                    'q_delay_ok'       => 'Le délai d\'intervention vous a-t-il convenu ?',
                    'q_verified_after' => 'Après le dépannage, avez-vous tout vérifié avec le réparateur ?',
                    'q_communication'  => 'La communication avec notre équipe était-elle satisfaisante ?',
                ] as $key => $text) : ?>
                    <div class="ls-fb-question-card">
                        <div class="ls-fb-question-body">
                            <p class="ls-fb-question-text"><?php echo esc_html($text); ?></p>
                        </div>
                        <div class="ls-fb-choices">
                            <input type="radio" id="<?php echo esc_attr($key); ?>_oui" name="<?php echo esc_attr($key); ?>" value="OUI" class="ls-fb-radio">
                            <label for="<?php echo esc_attr($key); ?>_oui" class="ls-fb-pill ls-fb-pill--oui">OUI</label>
                            <input type="radio" id="<?php echo esc_attr($key); ?>_non" name="<?php echo esc_attr($key); ?>" value="NON" class="ls-fb-radio">
                            <label for="<?php echo esc_attr($key); ?>_non" class="ls-fb-pill ls-fb-pill--non">NON</label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Section 5 : Recommandation -->
            <div class="ls-card ls-fb-questions-card">
                <h3 class="ls-fb-section-label">5. <?php esc_html_e('Recommandation', 'loyal-system'); ?></h3>
                <div class="ls-fb-question-card ls-fb-question-card--wrap">
                    <div class="ls-fb-question-body">
                        <p class="ls-fb-question-text"><?php esc_html_e('Recommanderez-vous notre service ?', 'loyal-system'); ?></p>
                    </div>
                    <div class="ls-fb-choices ls-fb-choices--wrap">
                        <?php ls_fb_pill_choices('q_recommend', [
                            'Oui certainement' => 'ls-fb-pill--green',
                            'Peut-être'        => 'ls-fb-pill--orange',
                            'Non'              => 'ls-fb-pill--red',
                        ]); ?>
                    </div>
                </div>
            </div>

            <!-- Section 6 : Commentaires et Suggestions -->
            <div class="ls-card ls-fb-comment-card">
                <h3 class="ls-fb-section-label">6. <?php esc_html_e('Commentaires et Suggestions', 'loyal-system'); ?></h3>
                <div class="ls-form-group" style="margin:0;">
                    <textarea name="comment" class="ls-input" rows="4"
                        placeholder="<?php esc_attr_e('Partagez vos remarques ou suggestions...', 'loyal-system'); ?>"></textarea>
                </div>
            </div>

            <button type="submit" id="ls-feedback-btn" class="ls-btn ls-btn-primary ls-btn-full">
                <span class="ls-btn-text"><?php esc_html_e('Envoyer l\'évaluation', 'loyal-system'); ?></span>
                <span class="ls-btn-spinner" aria-hidden="true"></span>
            </button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/feedback-js.php'; ?>
