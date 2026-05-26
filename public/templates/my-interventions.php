<?php if ( ! defined( 'ABSPATH' ) ) exit;

/** @var string $dashboard_url */
/** @var array  $interventions */
$dashboard_url = $dashboard_url ?? '';
$interventions = $interventions ?? array();

$status_labels = array(
    'pending'     => 'En attente',
    'confirmed'   => 'Confirmé',
    'in_progress' => 'En cours',
    'done'        => 'Terminé',
    'cancelled'   => 'Annulé',
);

$type_labels = array(
    'livraison'   => 'Livraison',
    'montage'     => 'Montage',
    'maintenance' => 'Maintenance',
);

$type_icons = array(
    'livraison'   => '&#128666;',
    'montage'     => '&#128295;',
    'maintenance' => '&#128736;',
);
?>
<div class="ls-container ls-my-interventions-container">

    <?php if ( $dashboard_url ) : ?>
    <div class="ls-feedback-topbar" style="margin-bottom:20px;">
        <a href="<?php echo esc_url( $dashboard_url ); ?>" class="ls-btn ls-btn-outline ls-btn-sm">&larr; <?php esc_html_e( 'Retour au tableau de bord', 'loyal-system' ); ?></a>
    </div>
    <?php endif; ?>

    <h2 class="ls-page-title"><?php esc_html_e( 'Mes interventions', 'loyal-system' ); ?></h2>

    <?php if ( empty( $interventions ) ) : ?>
        <div class="ls-card ls-text-center">
            <p class="ls-text-muted" style="margin:0;"><?php esc_html_e( "Aucune intervention programmée pour l'instant.", 'loyal-system' ); ?></p>
        </div>
    <?php else : ?>
        <div class="ls-intervention-list">
            <?php foreach ( $interventions as $iv ) :
                $status_label = $status_labels[ $iv->status ] ?? ucfirst( $iv->status );
                $type_label   = $type_labels[ $iv->type ]     ?? ucfirst( $iv->type );
                $type_icon    = $type_icons[ $iv->type ]      ?? '&#128203;';
                $scheduled_dt = strtotime( $iv->scheduled_at );
            ?>
            <div class="ls-card ls-intervention-card ls-iv-status-<?php echo esc_attr( $iv->status ); ?>">

                <!-- Card header: type + status badge -->
                <div class="ls-iv-header">
                    <div class="ls-iv-type">
                        <span class="ls-iv-type-icon"><?php echo $type_icon; ?></span>
                        <span class="ls-iv-type-label"><?php echo esc_html( $type_label ); ?></span>
                    </div>
                    <span class="ls-badge ls-iv-badge ls-iv-status-<?php echo esc_attr( $iv->status ); ?>">
                        <?php echo esc_html( $status_label ); ?>
                    </span>
                </div>

                <!-- Scheduled date/time -->
                <div class="ls-iv-date-row">
                    <span class="ls-iv-date-icon">&#128197;</span>
                    <strong><?php echo esc_html( date_i18n( 'l j F Y à H:i', $scheduled_dt ) ); ?></strong>
                </div>

                <!-- Details grid -->
                <div class="ls-iv-details">
                    <?php if ( ! empty( $iv->branch_name ) ) : ?>
                    <div class="ls-iv-detail-item">
                        <span class="ls-iv-detail-label"><?php esc_html_e( 'Point de départ', 'loyal-system' ); ?></span>
                        <span class="ls-iv-detail-value"><?php echo esc_html( $iv->branch_name ); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $iv->pickup_name ) ) : ?>
                    <div class="ls-iv-detail-item">
                        <span class="ls-iv-detail-label"><?php echo esc_html( $iv->pickup_category ?: __( 'Responsable', 'loyal-system' ) ); ?></span>
                        <span class="ls-iv-detail-value">
                            <?php echo esc_html( $iv->pickup_name ); ?>
                            <?php if ( ! empty( $iv->pickup_plate ) ) : ?>
                                <span class="ls-iv-plate"><?php echo esc_html( $iv->pickup_plate ); ?></span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $iv->pickup_phone ) ) : ?>
                    <div class="ls-iv-detail-item">
                        <span class="ls-iv-detail-label"><?php echo esc_html( sprintf( __( 'Téléphone %s', 'loyal-system' ), $iv->pickup_category ?: __( 'responsable', 'loyal-system' ) ) ); ?></span>
                        <span class="ls-iv-detail-value">
                            <a href="tel:<?php echo esc_attr( $iv->pickup_phone ); ?>" class="ls-iv-phone-link">
                                <?php echo esc_html( $iv->pickup_phone ); ?>
                            </a>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ( ! empty( $iv->notes ) ) : ?>
                <div class="ls-iv-notes">
                    <span class="ls-iv-detail-label"><?php esc_html_e( 'Notes', 'loyal-system' ); ?></span>
                    <p class="ls-iv-notes-text"><?php echo nl2br( esc_html( $iv->notes ) ); ?></p>
                </div>
                <?php endif; ?>

                <?php if ( ! empty( $iv->attachment_path ) ) : ?>
                <div class="ls-iv-attachment">
                    <a href="<?php echo esc_url( wp_upload_dir()['baseurl'] . $iv->attachment_path ); ?>" target="_blank" rel="noopener" class="ls-btn ls-btn-sm ls-btn-outline">
                        &#128206; <?php esc_html_e( 'Voir la pièce jointe', 'loyal-system' ); ?>
                    </a>
                </div>
                <?php endif; ?>

            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.ls-intervention-list { display: flex; flex-direction: column; gap: 16px; }
.ls-intervention-card { padding: 18px 20px; border-left: 4px solid #e2e8f0; }
.ls-iv-status-pending    { border-left-color: #f59e0b; }
.ls-iv-status-confirmed  { border-left-color: #3b82f6; }
.ls-iv-status-in_progress{ border-left-color: #8b5cf6; }
.ls-iv-status-done       { border-left-color: #10b981; }
.ls-iv-status-cancelled  { border-left-color: #ef4444; }

.ls-iv-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.ls-iv-type   { display: flex; align-items: center; gap: 8px; }
.ls-iv-type-icon  { font-size: 1.3rem; }
.ls-iv-type-label { font-weight: 700; font-size: 1rem; color: #1e293b; }

.ls-iv-badge { font-size: 0.75rem; padding: 3px 10px; border-radius: 99px; font-weight: 600; }
.ls-iv-status-pending     .ls-iv-badge,
.ls-badge.ls-iv-status-pending    { background: #fef3c7; color: #92400e; }
.ls-badge.ls-iv-status-confirmed  { background: #dbeafe; color: #1e40af; }
.ls-badge.ls-iv-status-in_progress{ background: #ede9fe; color: #5b21b6; }
.ls-badge.ls-iv-status-done       { background: #d1fae5; color: #065f46; }
.ls-badge.ls-iv-status-cancelled  { background: #fee2e2; color: #991b1b; }

.ls-iv-date-row { display: flex; align-items: center; gap: 8px; margin-bottom: 14px; font-size: 0.95rem; color: #374151; }
.ls-iv-date-icon { font-size: 1rem; }

.ls-iv-details { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
@media (max-width: 480px) { .ls-iv-details { grid-template-columns: 1fr; } }

.ls-iv-detail-item  { display: flex; flex-direction: column; gap: 2px; }
.ls-iv-detail-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; font-weight: 600; }
.ls-iv-detail-value { font-size: 0.9rem; color: #1e293b; display: flex; align-items: center; gap: 6px; }
.ls-iv-plate        { background: #f1f5f9; border-radius: 4px; padding: 1px 7px; font-size: 0.78rem; font-family: monospace; color: #475569; }
.ls-iv-phone-link   { color: #2563eb; text-decoration: none; }
.ls-iv-phone-link:hover { text-decoration: underline; }

.ls-iv-notes        { margin-top: 10px; padding-top: 10px; border-top: 1px solid #f1f5f9; }
.ls-iv-notes-text   { margin: 4px 0 0; font-size: 0.88rem; color: #475569; line-height: 1.5; }

.ls-iv-attachment   { margin-top: 12px; }
</style>
