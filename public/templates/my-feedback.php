<?php if ( ! defined( 'ABSPATH' ) ) exit;
/** @var string $dashboard_url */
/** @var string $maintenance_url */
/** @var string $delivery_url */
/** @var array  $feedback */
$dashboard_url   = $dashboard_url   ?? '';
$maintenance_url = $maintenance_url ?? '#';
$delivery_url    = $delivery_url    ?? '#';
$feedback        = $feedback        ?? array();
?>
<div class="ls-container ls-my-feedback-container">

    <!-- Top bar -->
    <div class="ls-myfb-topbar">
        <?php if ( $dashboard_url ) : ?>
        <a href="<?php echo esc_url( $dashboard_url ); ?>" class="ls-btn ls-btn-outline ls-btn-sm">
            &larr; <?php esc_html_e( 'Retour au tableau de bord', 'loyal-system' ); ?>
        </a>
        <?php endif; ?>
        <div class="ls-myfb-topbar-actions">
            <?php if ( $maintenance_url && $maintenance_url !== '#' ) : ?>
            <a href="<?php echo esc_url( $maintenance_url ); ?>" class="ls-btn ls-btn-outline ls-btn-sm">
                &#128295; <?php esc_html_e( 'Avis maintenance', 'loyal-system' ); ?>
            </a>
            <?php endif; ?>
            <?php if ( $delivery_url && $delivery_url !== '#' ) : ?>
            <a href="<?php echo esc_url( $delivery_url ); ?>" class="ls-btn ls-btn-outline ls-btn-sm">
                &#128666; <?php esc_html_e( 'Avis livraison', 'loyal-system' ); ?>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <h2 class="ls-page-title"><?php esc_html_e( 'Mes avis', 'loyal-system' ); ?></h2>

    <?php if ( empty( $feedback ) ) : ?>

        <div class="ls-card ls-myfb-empty">
            <div class="ls-myfb-empty-icon">&#128203;</div>
            <p class="ls-myfb-empty-text"><?php esc_html_e( 'Vous n\'avez pas encore soumis d\'avis.', 'loyal-system' ); ?></p>
            <div class="ls-myfb-empty-actions">
                <?php if ( $maintenance_url && $maintenance_url !== '#' ) : ?>
                <a href="<?php echo esc_url( $maintenance_url ); ?>" class="ls-btn ls-btn-primary">
                    &#128295; <?php esc_html_e( 'Soumettre un avis maintenance', 'loyal-system' ); ?>
                </a>
                <?php endif; ?>
                <?php if ( $delivery_url && $delivery_url !== '#' ) : ?>
                <a href="<?php echo esc_url( $delivery_url ); ?>" class="ls-btn ls-btn-outline">
                    &#128666; <?php esc_html_e( 'Soumettre un avis livraison', 'loyal-system' ); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>

    <?php else : ?>

        <div class="ls-myfb-list">
        <?php foreach ( $feedback as $fb ) :
            $answers      = json_decode( $fb->answers, true ) ?: array();
            $type_map = array(
                'maintenance' => array( 'label' => __( 'Maintenance',      'loyal-system' ), 'class' => 'ls-myfb-type--maintenance', 'icon' => '&#128295;' ),
                'delivery'    => array( 'label' => __( 'Livraison',         'loyal-system' ), 'class' => 'ls-myfb-type--delivery',    'icon' => '&#128666;' ),
                'montage'     => array( 'label' => __( 'Montage',           'loyal-system' ), 'class' => 'ls-myfb-type--montage',     'icon' => '&#128297;' ),
            );
            $type_info  = $type_map[ $fb->type ] ?? array( 'label' => ucfirst( $fb->type ), 'class' => 'ls-myfb-type--other', 'icon' => '&#128203;' );
            $type_label = $type_info['label'];
            $type_class = $type_info['class'];
            $type_icon  = $type_info['icon'];

            // Score: count OUI answers
            $total  = count( $answers );
            $yes    = 0;
            foreach ( $answers as $val ) {
                if ( strtoupper( trim( $val ) ) === 'OUI' ) { $yes++; }
            }
            $score_pct = $total > 0 ? round( $yes / $total * 100 ) : null;
        ?>
        <div class="ls-myfb-item ls-card">

            <!-- Header row -->
            <div class="ls-myfb-item-header">
                <div class="ls-myfb-item-left">
                    <span class="ls-myfb-type-badge <?php echo esc_attr( $type_class ); ?>">
                        <?php echo $type_icon; // phpcs:ignore ?>
                        <?php echo esc_html( $type_label ); ?>
                    </span>
                    <span class="ls-myfb-date ls-text-muted">
                        <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $fb->submitted_at ) ) ); ?>
                    </span>
                </div>
                <?php if ( $score_pct !== null ) : ?>
                <div class="ls-myfb-score <?php echo $score_pct >= 70 ? 'ls-myfb-score--good' : ( $score_pct >= 40 ? 'ls-myfb-score--mid' : 'ls-myfb-score--low' ); ?>">
                    <?php echo esc_html( $score_pct ); ?>%
                </div>
                <?php endif; ?>
                <!-- Toggle button -->
                <button type="button" class="ls-myfb-toggle ls-link-btn" aria-expanded="false">
                    <?php esc_html_e( 'Voir les réponses', 'loyal-system' ); ?> <span class="ls-myfb-chevron">&#8964;</span>
                </button>
            </div>

            <!-- Answers panel (collapsed by default) -->
            <div class="ls-myfb-answers" style="display:none;">
                <?php if ( ! empty( $answers ) ) : ?>
                <ul class="ls-myfb-answer-list">
                    <?php
                    $q_index = 1;
                    foreach ( $answers as $key => $val ) :
                        $q_label = str_replace( 'q_', '', $key );
                        $q_label = ucfirst( str_replace( '_', ' ', $q_label ) );
                        $val_up  = strtoupper( trim( $val ) );
                        $pill_cls = '';
                        if ( $val_up === 'OUI' )      { $pill_cls = 'ls-myfb-ans--oui'; }
                        elseif ( $val_up === 'NON' )  { $pill_cls = 'ls-myfb-ans--non'; }
                    ?>
                    <li class="ls-myfb-answer-row">
                        <span class="ls-myfb-ans-num"><?php echo $q_index++; ?></span>
                        <span class="ls-myfb-ans-label"><?php echo esc_html( $q_label ); ?></span>
                        <span class="ls-myfb-ans-val <?php echo esc_attr( $pill_cls ); ?>"><?php echo esc_html( $val ); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else : ?>
                    <p class="ls-text-muted ls-small"><?php esc_html_e( 'Aucune réponse enregistrée.', 'loyal-system' ); ?></p>
                <?php endif; ?>
            </div>

        </div>
        <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<script>
(function($){
    $(document).on('click', '.ls-myfb-toggle', function(){
        var $btn     = $(this);
        var $answers = $btn.closest('.ls-myfb-item').find('.ls-myfb-answers');
        var open     = $answers.is(':visible');
        $answers.slideToggle(200);
        $btn.attr('aria-expanded', !open);
        $btn.find('.ls-myfb-chevron').css('transform', open ? '' : 'rotate(180deg)');
        $btn.find('.ls-myfb-toggle-text').text( open ? '<?php esc_html_e( 'Voir les réponses', 'loyal-system' ); ?>' : '<?php esc_html_e( 'Masquer les réponses', 'loyal-system' ); ?>' );
    });
})(jQuery);
</script>
