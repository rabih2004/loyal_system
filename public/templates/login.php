<?php

/**
 * Frontend template: Customer login / registration via OTP.
 *
 * @var bool        $is_logged_in
 * @var object|null $customer
 * @var string      $redirect_url
 * @package LoyalSystem
 */
if (! defined('ABSPATH')) {
    exit;
}

if ($is_logged_in && $customer) :
    $logout_url = wp_nonce_url(
        add_query_arg(array('ls_action' => 'logout', 'redirect_to' => esc_url(get_permalink()))),
        'ls_logout'
    );
?>
    <div class="ls-container ls-login-container">
        <div class="ls-card ls-text-center">
            <div class="ls-logged-in-notice">
                <div class="ls-check-icon" aria-hidden="true">&#10003;</div>
                <h2><?php esc_html_e('Vous êtes connecté', 'loyal-system'); ?></h2>
                <p class="ls-welcome-text">
                    <?php echo esc_html(sprintf(__('Bon retour, %s', 'loyal-system'), ! empty($customer->full_name) ? $customer->full_name : $customer->phone)); ?>
                </p>
                <a href="<?php echo esc_url($logout_url); ?>" class="ls-btn ls-btn-outline ls-btn-sm">
                    <?php esc_html_e('Se déconnecter', 'loyal-system'); ?>
                </a>
            </div>
        </div>
    </div>
<?php return;
endif;

$current_url = esc_url($redirect_url ?: get_permalink());
?>

<div class="ls-container ls-login-container" id="ls-login-app">

    <div id="ls-auth-message" class="ls-message" role="alert" aria-live="polite" style="display:none;"></div>

    <!-- Étape 1 : Connexion par mot de passe (par défaut) -->
    <div id="ls-step-password-login" class="ls-step ls-card" style="display:block;">
        <div class="ls-card-header">
            <h2 class="ls-form-title"><?php esc_html_e('Se connecter', 'loyal-system'); ?></h2>
        </div>
        <form id="ls-password-login-form" novalidate>
            <div class="ls-form-group">
                <label for="ls-pw-login-phone" class="ls-label"><?php esc_html_e('Numéro de téléphone', 'loyal-system'); ?></label>
                <input type="tel" id="ls-pw-login-phone" name="phone" class="ls-input"
                    value="+224" placeholder="+224 XXXXXXXXX" autocomplete="tel" required>
            </div>
            <div class="ls-form-group">
                <label for="ls-pw-login-password" class="ls-label"><?php esc_html_e('Mot de passe', 'loyal-system'); ?></label>
                <div class="ls-input-wrap ls-password-wrap">
                    <input type="password" id="ls-pw-login-password" name="password" class="ls-input" autocomplete="current-password" required>
                    <button type="button" class="ls-pw-toggle" data-target="ls-pw-login-password" aria-label="<?php esc_attr_e('Afficher/masquer le mot de passe', 'loyal-system'); ?>">
                        <span class="ls-eye-icon" aria-hidden="true">&#128065;</span>
                    </button>
                </div>
            </div>
            <button type="submit" id="ls-pw-login-btn" class="ls-btn ls-btn-primary ls-btn-full">
                <span class="ls-btn-text"><?php esc_html_e('Se connecter', 'loyal-system'); ?></span>
                <span class="ls-btn-spinner" aria-hidden="true"></span>
            </button>
        </form>
        <div class="ls-text-center ls-mt-1">
            <button type="button" id="ls-switch-to-otp" class="ls-link-btn ls-small">
                <?php esc_html_e('Mot de passe oublié / Nouveau client ? Recevoir un code', 'loyal-system'); ?>
            </button>
        </div>
    </div><!-- #ls-step-password-login -->

    <!-- Étape 2a : Saisie du téléphone pour OTP -->
    <div id="ls-step-phone" class="ls-step ls-card" style="display:none;">
        <div class="ls-card-header">
            <h2 class="ls-form-title"><?php esc_html_e('Réinitialiser / Créer un compte', 'loyal-system'); ?></h2>
            <p class="ls-form-subtitle"><?php esc_html_e('Entrez votre numéro de téléphone pour recevoir un code de vérification', 'loyal-system'); ?></p>
        </div>
        <form id="ls-phone-form" novalidate>
            <div class="ls-form-group">
                <label for="ls-phone-input" class="ls-label"><?php esc_html_e('Numéro de téléphone', 'loyal-system'); ?> <span class="ls-required">*</span></label>
                <input type="tel" id="ls-phone-input" name="phone" class="ls-input ls-phone-input"
                    value="+224" placeholder="+224 XXXXXXXXX"
                    autocomplete="tel" inputmode="tel" required aria-required="true">
                <span class="ls-input-hint"><?php esc_html_e('Incluez votre indicatif pays, ex. +224 622 000 000', 'loyal-system'); ?></span>
            </div>
            <button type="submit" id="ls-send-otp-btn" class="ls-btn ls-btn-primary ls-btn-full">
                <span class="ls-btn-text"><?php esc_html_e('Envoyer le code de vérification', 'loyal-system'); ?></span>
                <span class="ls-btn-spinner" aria-hidden="true"></span>
            </button>
        </form>
        <div class="ls-text-center ls-mt-1">
            <button type="button" id="ls-switch-to-password" class="ls-link-btn ls-small">
                &larr; <?php esc_html_e('Retour à la connexion', 'loyal-system'); ?>
            </button>
        </div>
    </div><!-- #ls-step-phone -->

    <!-- Étape 2 : OTP -->
    <div id="ls-step-otp" class="ls-step ls-card" style="display:none;">
        <div class="ls-card-header">
            <h2 class="ls-form-title"><?php esc_html_e('Vérifiez votre téléphone', 'loyal-system'); ?></h2>
            <p class="ls-form-subtitle">
                <?php esc_html_e('Entrez le code à 6 chiffres envoyé au', 'loyal-system'); ?>
                <strong id="ls-otp-phone-display"></strong>
            </p>
        </div>
        <form id="ls-otp-form" novalidate>
            <div class="ls-form-group">
                <div class="ls-otp-inputs" role="group">
                    <?php for ($i = 1; $i <= 6; $i++) : ?>
                        <input type="text" class="ls-otp-digit" inputmode="numeric" pattern="[0-9]"
                            maxlength="1" autocomplete="<?php echo 1 === $i ? 'one-time-code' : 'off'; ?>"
                            data-index="<?php echo $i - 1; ?>"
                            aria-label="<?php echo esc_attr(sprintf(__('Chiffre %d', 'loyal-system'), $i)); ?>">
                    <?php endfor; ?>
                </div>
            </div>
            <button type="submit" id="ls-verify-otp-btn" class="ls-btn ls-btn-primary ls-btn-full">
                <span class="ls-btn-text"><?php esc_html_e('Vérifier le code', 'loyal-system'); ?></span>
                <span class="ls-btn-spinner" aria-hidden="true"></span>
            </button>
        </form>
        <div class="ls-otp-resend ls-text-center ls-mt-1">
            <span id="ls-resend-countdown" class="ls-countdown" aria-live="polite"></span>
            <button type="button" id="ls-resend-otp-btn" class="ls-link-btn" style="display:none;">
                <?php esc_html_e('Renvoyer le code', 'loyal-system'); ?>
            </button>
        </div>
        <div class="ls-text-center ls-mt-1">
            <button type="button" id="ls-otp-back" class="ls-link-btn">&larr; <?php esc_html_e('Retour', 'loyal-system'); ?></button>
        </div>
    </div><!-- #ls-step-otp -->

    <!-- Étape 4 : Définir / Réinitialiser le mot de passe -->
    <div id="ls-step-set-password" class="ls-step ls-card" style="display:none;">
        <div class="ls-card-header">
            <h2 class="ls-form-title"><?php esc_html_e('Définir votre mot de passe', 'loyal-system'); ?></h2>
            <p class="ls-form-subtitle"><?php esc_html_e('Choisissez un mot de passe pour sécuriser votre compte.', 'loyal-system'); ?></p>
        </div>
        <form id="ls-set-password-form" novalidate>
            <div class="ls-form-group">
                <label for="ls-new-password" class="ls-label"><?php esc_html_e('Nouveau mot de passe', 'loyal-system'); ?> <span class="ls-required">*</span></label>
                <div class="ls-input-wrap ls-password-wrap">
                    <input type="password" id="ls-new-password" name="password" class="ls-input"
                        autocomplete="new-password" minlength="6" required>
                    <button type="button" class="ls-pw-toggle" data-target="ls-new-password">
                        <span class="ls-eye-icon" aria-hidden="true">&#128065;</span>
                    </button>
                </div>
                <span class="ls-input-hint"><?php esc_html_e('Minimum 6 caractères', 'loyal-system'); ?></span>
            </div>
            <div class="ls-form-group">
                <label for="ls-confirm-password" class="ls-label"><?php esc_html_e('Confirmer le mot de passe', 'loyal-system'); ?> <span class="ls-required">*</span></label>
                <div class="ls-input-wrap ls-password-wrap">
                    <input type="password" id="ls-confirm-password" name="password_confirm" class="ls-input"
                        autocomplete="new-password" minlength="6" required>
                    <button type="button" class="ls-pw-toggle" data-target="ls-confirm-password">
                        <span class="ls-eye-icon" aria-hidden="true">&#128065;</span>
                    </button>
                </div>
            </div>
            <button type="submit" id="ls-set-password-btn" class="ls-btn ls-btn-primary ls-btn-full">
                <span class="ls-btn-text"><?php esc_html_e('Enregistrer et continuer', 'loyal-system'); ?></span>
                <span class="ls-btn-spinner" aria-hidden="true"></span>
            </button>
        </form>
    </div><!-- #ls-step-set-password -->

</div><!-- #ls-login-app -->

<script type="text/javascript">
    window.lsLoginData = {
        currentUrl: <?php echo wp_json_encode($current_url); ?>,
        redirectUrl: <?php
                        $redirect = esc_url_raw(wp_unslash($_GET['redirect_to'] ?? ''));
                        if (! $redirect) {
                            $dashboard_id = LS_Settings::dashboard_page_id();
                            $redirect = $dashboard_id ? get_permalink($dashboard_id) : '';
                        }
                        echo wp_json_encode($redirect);
                        ?>,
        ajaxUrl: <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,
        nonce: <?php echo wp_json_encode(wp_create_nonce('ls_public_nonce')); ?>
    };
</script>