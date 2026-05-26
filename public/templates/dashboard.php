<?php

/**
 * Customer dashboard template.
 *
 * @var bool        $is_logged_in
 * @var object|null $customer
 * @package LoyalSystem
 */
if (! defined('ABSPATH')) {
    exit;
}

$balance                  = LS_Database::get_balance($customer->id);
$my_tickets_url           = LS_Settings::my_tickets_page_id()           ? get_permalink(LS_Settings::my_tickets_page_id())           : '#';
$submit_url               = LS_Settings::submit_ticket_page_id()        ? get_permalink(LS_Settings::submit_ticket_page_id())        : '#';
$my_feedback_url          = LS_Settings::my_feedback_page_id()          ? get_permalink(LS_Settings::my_feedback_page_id())          : '';
$feedback_maintenance_url = LS_Settings::feedback_maintenance_page_id() ? get_permalink(LS_Settings::feedback_maintenance_page_id()) : '';
$feedback_delivery_url    = LS_Settings::feedback_delivery_page_id()    ? get_permalink(LS_Settings::feedback_delivery_page_id())    : '';
$feedback_merchant_url    = LS_Settings::feedback_merchant_page_id()    ? get_permalink(LS_Settings::feedback_merchant_page_id())    : '';
$form_montage_url         = LS_Settings::form_montage_page_id()         ? get_permalink(LS_Settings::form_montage_page_id())         : '';
$my_interventions_url     = LS_Settings::my_interventions_page_id()     ? get_permalink(LS_Settings::my_interventions_page_id())     : '';
$logout_url = wp_nonce_url(
    add_query_arg(array('ls_action' => 'logout', 'redirect_to' => esc_url(get_permalink()))),
    'ls_logout'
);
?>
<div class="ls-container ls-dashboard-container">

    <!-- Header row: greeting + sign-out -->
    <div class="ls-dashboard-header">
        <div class="ls-dash-greeting-wrap">
            <span class="ls-dash-greeting-hi"><?php esc_html_e('Bienvenue,', 'loyal-system'); ?></span>
            <span class="ls-dash-greeting-name"><?php echo esc_html($customer->full_name ?: $customer->phone); ?></span>
        </div>
        <a href="<?php echo esc_url($logout_url); ?>" class="ls-btn ls-btn-outline ls-btn-sm"><?php esc_html_e('Se déconnecter', 'loyal-system'); ?></a>
    </div>

    <!-- Compact balance + quick-action buttons on same row -->
    <div class="ls-dash-top-row">
        <div class="ls-dash-balance-compact">
            <span class="ls-dash-balance-label"><?php esc_html_e('Solde', 'loyal-system'); ?></span>
            <span class="ls-dash-balance-amount"><?php echo esc_html(number_format($balance, 0, '.', ' ') . ' ' . LS_Invoice::get_default_currency()); ?></span>
        </div>
    </div>

    <!-- Quick-access menu -->
    <div class="ls-card ls-dash-menu-card">
        <h3 class="ls-section-title"><?php esc_html_e('Menu', 'loyal-system'); ?></h3>

        <!-- Group 1: personal -->
        <p class="ls-dash-menu-group-label"><?php esc_html_e('Mes activités', 'loyal-system'); ?></p>
        <nav class="ls-dash-btn-row" aria-label="<?php esc_attr_e('Mes activités', 'loyal-system'); ?>">
            <a href="<?php echo esc_url($my_tickets_url); ?>" class="ls-dash-btn">
                <span class="ls-dash-btn-icon">&#127916;</span>
                <span class="ls-dash-btn-label"><?php esc_html_e('Mes tickets', 'loyal-system'); ?><br>(Maintenance)</span>
            </a>
            <?php if ($my_feedback_url) : ?>
                <a href="<?php echo esc_url($my_feedback_url); ?>" class="ls-dash-btn">
                    <span class="ls-dash-btn-icon">&#11088;</span>
                    <span class="ls-dash-btn-label"><?php esc_html_e('Mes avis', 'loyal-system'); ?></span>
                </a>
            <?php endif; ?>
            <?php if ($my_interventions_url) : ?>
                <a href="<?php echo esc_url($my_interventions_url); ?>" class="ls-dash-btn">
                    <span class="ls-dash-btn-icon">&#128666;</span>
                    <span class="ls-dash-btn-label"><?php esc_html_e('Mes interventions', 'loyal-system'); ?></span>
                </a>
            <?php endif; ?>
        </nav>

        <!-- Group 2: actions -->
        <p class="ls-dash-menu-group-label ls-dash-menu-group-label--mt"><?php esc_html_e('Déposer une demande', 'loyal-system'); ?></p>
        <nav class="ls-dash-btn-row" aria-label="<?php esc_attr_e('Déposer une demande', 'loyal-system'); ?>">
            <a href="<?php echo esc_url($submit_url); ?>" class="ls-dash-btn">
                <span class="ls-dash-btn-icon">&#128196;</span>
                <span class="ls-dash-btn-label"><?php esc_html_e('Nouveau ticket', 'loyal-system'); ?> <br>(Maintenance)</span>
            </a>
            <?php if ($feedback_maintenance_url) : ?>
                <a href="<?php echo esc_url($feedback_maintenance_url); ?>" class="ls-dash-btn">
                    <span class="ls-dash-btn-icon">&#128295;</span>
                    <span class="ls-dash-btn-label"><?php esc_html_e('Feedback Maintenance', 'loyal-system'); ?></span>
                </a>
            <?php endif; ?>
            <?php if ($form_montage_url) : ?>
                <a href="<?php echo esc_url($form_montage_url); ?>" class="ls-dash-btn">
                    <span class="ls-dash-btn-icon">&#128297;</span>
                    <span class="ls-dash-btn-label"><?php esc_html_e('Feedback Montage', 'loyal-system'); ?></span>
                </a>
            <?php endif; ?>
            <?php if ($feedback_delivery_url) : ?>
                <a href="<?php echo esc_url($feedback_delivery_url); ?>" class="ls-dash-btn">
                    <span class="ls-dash-btn-icon">&#128666;</span>
                    <span class="ls-dash-btn-label"><?php esc_html_e('Feedback Livraison', 'loyal-system'); ?></span>
                </a>
            <?php endif; ?>
            <?php if ($feedback_merchant_url) : ?>
                <a href="<?php echo esc_url($feedback_merchant_url); ?>" class="ls-dash-btn">
                    <span class="ls-dash-btn-icon">&#127978;</span>
                    <span class="ls-dash-btn-label"><?php esc_html_e('Feedback Magasin', 'loyal-system'); ?></span>
                </a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Profile -->
    <div class="ls-card ls-profile-card">
        <h3 class="ls-section-title"><?php esc_html_e('Mon profil', 'loyal-system'); ?></h3>
        <div id="ls-profile-msg" class="ls-message" style="display:none;" role="alert"></div>
        <form id="ls-profile-form">
            <div class="ls-profile-grid">
                <div class="ls-form-group">
                    <label class="ls-label"><?php esc_html_e('Nom complet', 'loyal-system'); ?></label>
                    <input type="text" name="full_name" class="ls-input"
                        value="<?php echo esc_attr($customer->full_name ?? ''); ?>"
                        placeholder="<?php esc_attr_e('Votre nom', 'loyal-system'); ?>">
                </div>
                <div class="ls-form-group">
                    <label class="ls-label"><?php esc_html_e('Téléphone', 'loyal-system'); ?></label>
                    <input type="tel" class="ls-input" value="<?php echo esc_attr($customer->phone ?? ''); ?>" disabled>
                </div>
                <div class="ls-form-group ls-form-group-full">
                    <label class="ls-label"><?php esc_html_e('Adresse', 'loyal-system'); ?></label>
                    <textarea name="address" class="ls-input" rows="2"
                        placeholder="<?php esc_attr_e('Votre adresse de livraison', 'loyal-system'); ?>"><?php echo esc_textarea($customer->address ?? ''); ?></textarea>
                </div>
            </div>
            <button type="submit" id="ls-profile-btn" class="ls-btn ls-btn-primary">
                <span class="ls-btn-text"><?php esc_html_e('Enregistrer le profil', 'loyal-system'); ?></span>
                <span class="ls-btn-spinner" aria-hidden="true"></span>
            </button>
        </form>
    </div>

    <!-- Recent ledger -->
    <div class="ls-card ls-ledger-card">
        <h3 class="ls-section-title"><?php esc_html_e('Transactions récentes', 'loyal-system'); ?></h3>
        <?php
        $ledger = LS_Database::get_ledger($customer->id, 5);
        if (empty($ledger)) :
        ?>
            <p class="ls-text-muted"><?php esc_html_e('Aucune transaction pour le moment.', 'loyal-system'); ?></p>
        <?php else : ?>
            <table class="ls-table ls-ledger-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Date',    'loyal-system'); ?></th>
                        <th><?php esc_html_e('Type',    'loyal-system'); ?></th>
                        <th><?php esc_html_e('Montant', 'loyal-system'); ?></th>
                        <th><?php esc_html_e('Solde',   'loyal-system'); ?></th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ledger as $entry) : ?>
                        <tr>
                            <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($entry->created_at))); ?></td>
                            <td><span class="ls-badge ls-type-<?php echo esc_attr($entry->type); ?>"><?php echo esc_html(ucfirst($entry->type)); ?></span></td>
                            <td><?php echo esc_html(number_format($entry->amount, 0, '.', ' ')); ?></td>
                            <td><?php echo esc_html(number_format($entry->balance_after, 0, '.', ' ')); ?></td>
                            <td>
                                <?php if (! empty($entry->invoice_file_path)) : ?>
                                    <a href="<?php echo esc_url(wp_upload_dir()['baseurl'] . $entry->invoice_file_path); ?>"
                                        target="_blank" class="ls-ledger-file-btn" title="<?php esc_attr_e('Voir la facture', 'loyal-system'); ?>">
                                        &#128196;
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>