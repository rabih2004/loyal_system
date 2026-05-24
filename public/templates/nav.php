<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<nav class="ls-nav" role="navigation" aria-label="<?php esc_attr_e( 'Espace client', 'loyal-system' ); ?>">
    <div class="ls-nav-inner">

        <?php if ( $is_logged_in && $customer ) : ?>
            <span class="ls-nav-greeting">
                <?php echo esc_html( sprintf( __( 'Bonjour, %s', 'loyal-system' ), $customer->full_name ?: $customer->phone ) ); ?>
            </span>
        <?php endif; ?>

        <ul class="ls-nav-links">
            <?php foreach ( $nav_items as $item ) :
                if ( ! $item['page_id'] ) { continue; }
                $dest       = get_permalink( $item['page_id'] );
                $url        = $nav_url( $item['page_id'] );
                $is_current = ( $item['page_id'] === $current_id );
            ?>
            <li class="ls-nav-item<?php echo $is_current ? ' ls-nav-current' : ''; ?>">
                <a href="<?php echo esc_url( $url ); ?>" class="ls-nav-link<?php echo $is_current ? ' ls-nav-link--active' : ''; ?>">
                    <?php if ( ! empty( $item['icon'] ) ) : ?>
                        <span class="ls-nav-icon" aria-hidden="true"><?php echo $item['icon']; // phpcs:ignore ?></span>
                    <?php endif; ?>
                    <?php echo esc_html( $item['label'] ); ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>

        <div class="ls-nav-auth">
            <?php if ( $is_logged_in ) :
                $logout_url = wp_nonce_url(
                    add_query_arg( array( 'ls_action' => 'logout', 'redirect_to' => esc_url( home_url( '/' ) ) ) ),
                    'ls_logout'
                );
            ?>
                <a href="<?php echo esc_url( $logout_url ); ?>" class="ls-btn ls-btn-outline ls-btn-sm">
                    <?php esc_html_e( 'Se déconnecter', 'loyal-system' ); ?>
                </a>
            <?php else : ?>
                <a href="<?php echo esc_url( $login_url ); ?>" class="ls-btn ls-btn-primary ls-btn-sm">
                    <?php esc_html_e( 'Se connecter', 'loyal-system' ); ?>
                </a>
            <?php endif; ?>
        </div>

    </div>
</nav>

<style>
.ls-nav { background:#fff; border-bottom:1px solid #e5e7eb; padding:10px 0; }
.ls-nav-inner { display:flex; align-items:center; gap:16px; flex-wrap:wrap; padding:0 16px; }
.ls-nav-greeting { font-weight:600; color:#374151; white-space:nowrap; font-size:0.9em; }
.ls-nav-links { list-style:none; margin:0; padding:0; display:flex; gap:4px; flex-wrap:wrap; flex:1; }
.ls-nav-link { display:inline-flex; align-items:center; gap:5px; padding:6px 12px; border-radius:6px; color:#374151; text-decoration:none; font-size:0.875em; transition:background .15s,color .15s; }
.ls-nav-link:hover { background:#f3f4f6; color:#111827; text-decoration:none; }
.ls-nav-link--active { background:#eff6ff; color:#2563eb; font-weight:600; }
.ls-nav-icon { font-size:1em; line-height:1; }
.ls-nav-auth { margin-left:auto; }
@media(max-width:600px){
    .ls-nav-inner { flex-direction:column; align-items:stretch; gap:10px; }
    .ls-nav-links { gap:2px; }
    .ls-nav-link { padding:8px 10px; }
    .ls-nav-auth { margin-left:0; }
}
</style>
