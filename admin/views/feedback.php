<?php if (! defined('ABSPATH')) exit;

$type_filter = sanitize_key($_GET['feedback_type'] ?? '');
$paged       = max(1, (int) ($_GET['paged'] ?? 1));
$per_page    = 30;
$is_merchant = ($type_filter === 'merchant');

// Types stored in wp_ls_feedback (not merchant)
$feedback_types = array('maintenance', 'delivery', 'montage');

// Question labels for all feedback types
$question_labels = array(
    // maintenance / montage
    'q_presentable'     => 'Présentable et en uniforme',
    'q_verified_before' => 'Vérifié meubles avant montage',
    'q_worked_fast'     => 'Travaillé rapidement',
    'q_tightened'       => 'Bien serré les meubles',
    'q_verified_after'  => 'Tout vérifié après montage',
    'q_collaborated'    => 'Bien collaboré',
    'q_on_time'         => 'Arrivé à l\'heure',
    'q_finish_time'     => 'Heure de fin',
    'q_arrival_time'    => 'Heure d\'arrivée',
    'q_rating'          => 'Note / 10',
    'q_satisfied'       => 'Satisfait(e)',
    // delivery / management
    'q_fast_unload'     => 'Débarquement rapide',
    'q_placed'          => 'Meubles déposés à leur place',
    'q_driver_present'  => 'Chauffeur présent',
    'q_cooperated'      => 'Coopération lors débarquement',
    'q_verified'        => 'Vérifié meubles avec employés',
    'q_match_order'     => 'Correspond à la commande',
    'q_contacted'       => 'Contacté avant livraison',
    'q_protected'       => 'Articles bien protégés',
    'q_no_damage'       => 'Aucun dommage au domicile',
    'q_goods_intact'    => 'Marchandise en bon état',
    'q_handled_well'    => 'Manipulé avec soin',
    'q_complete_order'  => 'Commande complète',
);

$is_all = ($type_filter === '');

if ($is_merchant) {
    $rows          = LS_Database::get_merchant_feedback(array('limit' => $per_page, 'offset' => ($paged - 1) * $per_page));
    $total         = LS_Database::count_merchant_feedback();
    $rows_feedback = array();
    $rows_merchant = $rows;
} elseif ($is_all) {
    $rows_feedback = LS_Database::get_feedback(array('type' => '', 'limit' => $per_page, 'offset' => ($paged - 1) * $per_page));
    $rows_merchant = LS_Database::get_merchant_feedback(array('limit' => $per_page, 'offset' => ($paged - 1) * $per_page));
    $total         = LS_Database::count_feedback() + LS_Database::count_merchant_feedback();
    $rows          = $rows_feedback;
} else {
    $rows          = LS_Database::get_feedback(array('type' => $type_filter, 'limit' => $per_page, 'offset' => ($paged - 1) * $per_page));
    $total         = LS_Database::count_feedback($type_filter);
    $rows_feedback = $rows;
    $rows_merchant = array();
}

?>
<div class="wrap ls-admin-wrap">
    <h1><?php esc_html_e('Customer Feedback', 'loyal-system'); ?></h1>
    <hr class="wp-header-end">
    <div>
        <!-- Filter tabs -->
        <ul class="subsubsub" style="margin-bottom:12px;">
            <li><a href="<?php echo esc_url(admin_url('admin.php?page=ls-feedback')); ?>"
                    <?php if (! $type_filter) echo 'class="current"'; ?>>
                    <?php esc_html_e('Tous', 'loyal-system'); ?>
                    <span class="count">(<?php echo LS_Database::count_feedback() + LS_Database::count_merchant_feedback(); ?>)</span>
                </a> |</li>
            <li><a href="<?php echo esc_url(admin_url('admin.php?page=ls-feedback&feedback_type=maintenance')); ?>"
                    <?php if ($type_filter === 'maintenance') echo 'class="current"'; ?>>
                    &#128295; <?php esc_html_e('Maintenance', 'loyal-system'); ?>
                    <span class="count">(<?php echo LS_Database::count_feedback('maintenance'); ?>)</span>
                </a> |</li>
            <li><a href="<?php echo esc_url(admin_url('admin.php?page=ls-feedback&feedback_type=delivery')); ?>"
                    <?php if ($type_filter === 'delivery') echo 'class="current"'; ?>>
                    &#128666; <?php esc_html_e('Livraison', 'loyal-system'); ?>
                    <span class="count">(<?php echo LS_Database::count_feedback('delivery'); ?>)</span>
                </a> |</li>
            <li><a href="<?php echo esc_url(admin_url('admin.php?page=ls-feedback&feedback_type=montage')); ?>"
                    <?php if ($type_filter === 'montage') echo 'class="current"'; ?>>
                    &#128297; <?php esc_html_e('Montage', 'loyal-system'); ?>
                    <span class="count">(<?php echo LS_Database::count_feedback('montage'); ?>)</span>
                </a> |</li>
            <li><a href="<?php echo esc_url(admin_url('admin.php?page=ls-feedback&feedback_type=merchant')); ?>"
                    <?php if ($type_filter === 'merchant') echo 'class="current"'; ?>>
                    &#127978; <?php esc_html_e('Magasin', 'loyal-system'); ?>
                    <span class="count">(<?php echo LS_Database::count_merchant_feedback(); ?>)</span>
                </a></li>
        </ul>
    </div>
    <div style="clear:both;">
        <?php if (! $is_merchant) : ?>
            <!-- ── Maintenance / Delivery feedback table ───────────────────────── -->
            <?php if ($is_all) : ?>
                <h2 style="margin-top:20px;font-size:14px;font-weight:700;">&#128295;&#128666;&#128297; <?php esc_html_e('Maintenance, Livraison &amp; Montage', 'loyal-system'); ?></h2><?php endif; ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th style="width:100px;"><?php esc_html_e('Type', 'loyal-system'); ?></th>
                        <th><?php esc_html_e('Customer', 'loyal-system'); ?></th>
                        <th style="width:140px;"><?php esc_html_e('Date', 'loyal-system'); ?></th>
                        <th style="width:90px;"><?php esc_html_e('Score', 'loyal-system'); ?></th>
                        <th style="width:120px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows_feedback)) : ?>
                        <tr>
                            <td colspan="6"><?php esc_html_e('No feedback yet.', 'loyal-system'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($rows_feedback as $row) :
                            $answers     = json_decode($row->answers, true) ?: array();
                            $yes_count   = count(array_filter($answers, fn($v) => $v === 'OUI'));
                            $yesno_total = count(array_filter($answers, fn($v) => in_array($v, array('OUI', 'NON'), true)));
                            $score_pct   = $yesno_total > 0 ? round($yes_count / $yesno_total * 100) : null;
                            $rating      = $answers['q_rating'] ?? null;
                            $type_styles = array(
                                'maintenance' => 'background:#fef3c7;color:#92400e',
                                'delivery'    => 'background:#dbeafe;color:#1e40af',
                                'montage'     => 'background:#dcfce7;color:#166534',
                            );
                            $type_style = $type_styles[$row->type] ?? 'background:#f3f4f6;color:#374151';
                            $type_labels_fb = array(
                                'maintenance' => 'Maintenance',
                                'delivery'    => 'Livraison',
                                'montage'     => 'Montage',
                            );
                            $type_display = $type_labels_fb[$row->type] ?? ucfirst($row->type);
                        ?>
                            <tr>
                                <td><?php echo (int) $row->id; ?></td>
                                <td>
                                    <span style="<?php echo esc_attr($type_style); ?>;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;">
                                        <?php echo esc_html($type_display); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo esc_html($row->full_name ?: '—'); ?></strong><br>
                                    <small style="color:#666;"><?php echo esc_html($row->phone); ?></small>
                                </td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' H:i', strtotime($row->submitted_at))); ?></td>
                                <td>
                                    <?php if ($rating !== null) : ?>
                                        <strong style="font-size:1.1em;"><?php echo esc_html($rating); ?>/10</strong>
                                    <?php elseif ($score_pct !== null) : ?>
                                        <strong style="color:<?php echo $score_pct >= 70 ? '#16a34a' : ($score_pct >= 40 ? '#d97706' : '#dc2626'); ?>">
                                            <?php echo esc_html($score_pct); ?>%
                                        </strong>
                                        <?php else : ?>—<?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="button button-small ls-fb-toggle" data-id="<?php echo (int) $row->id; ?>">
                                        <?php esc_html_e('View', 'loyal-system'); ?>
                                    </button>
                                </td>
                            </tr>
                            <tr class="ls-fb-detail-row" id="ls-fb-detail-<?php echo (int) $row->id; ?>" style="display:none;">
                                <td colspan="6" style="background:#f8fafc;padding:16px 20px;">
                                    <table style="border-collapse:collapse;width:100%;max-width:680px;font-size:13px;">
                                        <?php foreach ($answers as $key => $val) :
                                            $label = $question_labels[$key] ?? ucfirst(str_replace('q_', '', str_replace('_', ' ', $key)));
                                        ?>
                                            <tr style="border-bottom:1px solid #e2e8f0;">
                                                <td style="padding:7px 12px 7px 0;color:#374151;font-weight:600;width:60%;"><?php echo esc_html($label); ?></td>
                                                <td style="padding:7px 0;">
                                                    <?php if ($val === 'OUI') : ?>
                                                        <span style="background:#dcfce7;color:#15803d;padding:2px 10px;border-radius:20px;font-weight:700;font-size:12px;">OUI</span>
                                                    <?php elseif ($val === 'NON') : ?>
                                                        <span style="background:#fee2e2;color:#b91c1c;padding:2px 10px;border-radius:20px;font-weight:700;font-size:12px;">NON</span>
                                                    <?php else : ?>
                                                        <span><?php echo esc_html($val); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                    <?php if (! empty($row->comment)) : ?>
                                        <div style="margin-top:12px;padding:10px 14px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;">
                                            <strong style="display:block;margin-bottom:4px;color:#374151;"><?php esc_html_e('Comment', 'loyal-system'); ?></strong>
                                            <span style="color:#4b5563;"><?php echo esc_html($row->comment); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if ($is_merchant || $is_all) : ?>
            <!-- ── Merchant feedback table ─────────────────────────────────────── -->
            <?php if ($is_all) : ?><h2 style="margin-top:24px;font-size:14px;font-weight:700;">&#127978; <?php esc_html_e('Magasin', 'loyal-system'); ?></h2><?php endif; ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th><?php esc_html_e('Branch / Store', 'loyal-system'); ?></th>
                        <th><?php esc_html_e('Customer', 'loyal-system'); ?></th>
                        <th style="width:130px;"><?php esc_html_e('Date', 'loyal-system'); ?></th>
                        <th style="width:90px;"><?php esc_html_e('Accueil', 'loyal-system'); ?></th>
                        <th style="width:90px;"><?php esc_html_e('Service', 'loyal-system'); ?></th>
                        <th style="width:80px;"><?php esc_html_e('Qualité', 'loyal-system'); ?></th>
                        <th style="width:80px;"><?php esc_html_e('Prix', 'loyal-system'); ?></th>
                        <th style="width:90px;"><?php esc_html_e('Recommande', 'loyal-system'); ?></th>
                        <th><?php esc_html_e('Comment', 'loyal-system'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows_merchant)) : ?>
                        <tr>
                            <td colspan="10"><?php esc_html_e('No merchant feedback yet.', 'loyal-system'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($rows_merchant as $row) : ?>
                            <tr>
                                <td><?php echo (int) $row->id; ?></td>
                                <td><strong><?php echo esc_html($row->branch_name ?: '—'); ?></strong></td>
                                <td>
                                    <?php echo esc_html($row->full_name ?: '—'); ?><br>
                                    <small style="color:#666;"><?php echo esc_html($row->phone); ?></small>
                                </td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' H:i', strtotime($row->submitted_at))); ?></td>
                                <td><?php echo $row->q_welcoming
                                        ? '<span style="background:' . ($row->q_welcoming === 'OUI' ? '#dcfce7;color:#15803d' : '#fee2e2;color:#b91c1c') . ';padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;">' . esc_html($row->q_welcoming) . '</span>'
                                        : '<span style="color:#999;">—</span>'; ?></td>
                                <td><?php echo $row->q_fast
                                        ? '<span style="background:' . ($row->q_fast === 'OUI' ? '#dcfce7;color:#15803d' : '#fee2e2;color:#b91c1c') . ';padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;">' . esc_html($row->q_fast) . '</span>'
                                        : '<span style="color:#999;">—</span>'; ?></td>
                                <td>
                                    <?php if ($row->q_quality) :
                                        $q = (int) $row->q_quality;
                                        $col = $q >= 7 ? '#16a34a' : ($q >= 4 ? '#d97706' : '#dc2626');
                                    ?>
                                        <strong style="color:<?php echo $col; ?>"><?php echo $q; ?>/10</strong>
                                        <?php else : ?>—<?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row->q_value) :
                                        $v = (int) $row->q_value;
                                        $col = $v >= 7 ? '#16a34a' : ($v >= 4 ? '#d97706' : '#dc2626');
                                    ?>
                                        <strong style="color:<?php echo $col; ?>"><?php echo $v; ?>/10</strong>
                                        <?php else : ?>—<?php endif; ?>
                                </td>
                                <td><?php echo $row->q_recommend
                                        ? '<span style="background:' . ($row->q_recommend === 'OUI' ? '#dcfce7;color:#15803d' : '#fee2e2;color:#b91c1c') . ';padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;">' . esc_html($row->q_recommend) . '</span>'
                                        : '<span style="color:#999;">—</span>'; ?></td>
                                <td style="max-width:200px;"><small><?php echo esc_html($row->comment ?: '—'); ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php
        $num_pages = ceil($total / $per_page);
        if ($num_pages > 1) {
            echo '<div class="tablenav bottom"><div class="tablenav-pages">';
            echo paginate_links(array(
                'base'    => add_query_arg('paged', '%#%'),
                'format'  => '',
                'current' => $paged,
                'total'   => $num_pages,
            ));
            echo '</div></div>';
        }
        ?>
    </div>
</div>

<script>
    (function($) {
        $(document).on('click', '.ls-fb-toggle', function() {
            var id = $(this).data('id');
            var $row = $('#ls-fb-detail-' + id);
            $('.ls-fb-detail-row').not($row).hide();
            $row.toggle();
        });
    })(jQuery);
</script>