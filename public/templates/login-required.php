<?php if ( ! defined( 'ABSPATH' ) ) exit;
$login_url = add_query_arg( 'redirect_to', rawurlencode( home_url( add_query_arg( array() ) ) ), LS_Settings::login_url() );
?>
<div class="ls-container ls-login-required-container">
    <div class="ls-card ls-text-center">
        <div class="ls-lock-icon" aria-hidden="true">&#128274;</div>
        <h2><?php esc_html_e( 'Sign in required', 'loyal-system' ); ?></h2>
        <p><?php esc_html_e( 'You need to be signed in to view this page.', 'loyal-system' ); ?></p>
        <a href="<?php echo esc_url( $login_url ); ?>" class="ls-btn ls-btn-primary">
            <?php esc_html_e( 'Sign In', 'loyal-system' ); ?>
        </a>
    </div>
</div>
