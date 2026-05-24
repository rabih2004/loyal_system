<?php if ( ! defined( 'ABSPATH' ) ) exit;

$cur = LS_Settings::default_invoice_currency();

function ls_fmt( $n ) { return number_format( (float) $n, 0, '.', ' ' ); }
function ls_trend( $current, $previous ) {
    if ( ! $previous ) return '';
    $pct   = round( ( $current - $previous ) / $previous * 100 );
    $cls   = $pct >= 0 ? 'ls-trend-up' : 'ls-trend-down';
    $arrow = $pct >= 0 ? '&#9650;' : '&#9660;';
    return '<span class="' . $cls . '">' . $arrow . ' ' . abs( $pct ) . '% vs last month</span>';
}

$ticket_labels = array(
    'open'        => array( 'label' => __( 'Open', 'loyal-system' ),        'pill' => 'ls-pill-open' ),
    'in_progress' => array( 'label' => __( 'In Progress', 'loyal-system' ), 'pill' => 'ls-pill-in_progress' ),
    'resolved'    => array( 'label' => __( 'Resolved', 'loyal-system' ),    'pill' => 'ls-pill-resolved' ),
    'closed'      => array( 'label' => __( 'Closed', 'loyal-system' ),      'pill' => 'ls-pill-closed' ),
);
?>
<div class="wrap ls-admin-wrap ls-dashboard-wrap">

    <h1 style="display:flex;align-items:center;gap:10px;">
        <span class="dashicons dashicons-chart-bar" style="font-size:26px;color:#2271b1;"></span>
        <?php esc_html_e( 'Dashboard', 'loyal-system' ); ?>
        <span style="font-size:13px;font-weight:400;color:#9ea3a8;margin-left:4px;"><?php echo esc_html( date_i18n( get_option( 'date_format' ) ) ); ?></span>
    </h1>
    <hr class="wp-header-end">

    <!-- ── KPI Cards ──────────────────────────────────────────────────────── -->
    <p class="ls-dash-section-title"><?php esc_html_e( 'Overview', 'loyal-system' ); ?></p>

    <div class="ls-kpi-grid">

        <!-- Today Revenue -->
        <div class="ls-kpi-card ls-kpi-blue">
            <div class="ls-kpi-card-top">
                <div class="ls-kpi-body">
                    <div class="ls-kpi-label"><?php esc_html_e( "Today's Revenue", 'loyal-system' ); ?></div>
                    <div class="ls-kpi-value"><?php echo esc_html( ls_fmt( $stats['today_revenue'] ) ); ?></div>
                </div>
                <div class="ls-kpi-icon-wrap"><span class="dashicons dashicons-chart-line"></span></div>
            </div>
            <div class="ls-kpi-card-footer">
                <?php echo esc_html( $stats['today_invoices'] ); ?> <?php esc_html_e( 'invoice(s) today', 'loyal-system' ); ?>
            </div>
        </div>

        <!-- This Month Revenue -->
        <div class="ls-kpi-card ls-kpi-teal">
            <div class="ls-kpi-card-top">
                <div class="ls-kpi-body">
                    <div class="ls-kpi-label"><?php esc_html_e( 'Month Revenue', 'loyal-system' ); ?></div>
                    <div class="ls-kpi-value"><?php echo esc_html( ls_fmt( $stats['month_revenue'] ) ); ?></div>
                </div>
                <div class="ls-kpi-icon-wrap"><span class="dashicons dashicons-calendar-alt"></span></div>
            </div>
            <div class="ls-kpi-card-footer">
                <?php echo esc_html( $stats['month_invoices'] ); ?> <?php esc_html_e( 'invoices', 'loyal-system' ); ?>
                &nbsp;<?php echo wp_kses( ls_trend( $stats['month_revenue'], $stats['prev_month_revenue'] ), array( 'span' => array( 'class' => array() ) ) ); ?>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="ls-kpi-card ls-kpi-green">
            <div class="ls-kpi-card-top">
                <div class="ls-kpi-body">
                    <div class="ls-kpi-label"><?php esc_html_e( 'Total Revenue', 'loyal-system' ); ?></div>
                    <div class="ls-kpi-value"><?php echo esc_html( ls_fmt( $stats['total_revenue'] ) ); ?></div>
                </div>
                <div class="ls-kpi-icon-wrap"><span class="dashicons dashicons-money-alt"></span></div>
            </div>
            <div class="ls-kpi-card-footer">
                <?php echo esc_html( ls_fmt( $stats['total_invoices'] ) ); ?> <?php esc_html_e( 'total invoices', 'loyal-system' ); ?>
            </div>
        </div>

        <!-- Customers -->
        <div class="ls-kpi-card ls-kpi-indigo">
            <div class="ls-kpi-card-top">
                <div class="ls-kpi-body">
                    <div class="ls-kpi-label"><?php esc_html_e( 'Customers', 'loyal-system' ); ?></div>
                    <div class="ls-kpi-value"><?php echo esc_html( ls_fmt( $stats['total_customers'] ) ); ?></div>
                </div>
                <div class="ls-kpi-icon-wrap"><span class="dashicons dashicons-groups"></span></div>
            </div>
            <div class="ls-kpi-card-footer">
                +<?php echo esc_html( $stats['month_customers'] ); ?> <?php esc_html_e( 'this month', 'loyal-system' ); ?>
            </div>
        </div>

        <!-- Credits -->
        <div class="ls-kpi-card ls-kpi-amber">
            <div class="ls-kpi-card-top">
                <div class="ls-kpi-body">
                    <div class="ls-kpi-label"><?php esc_html_e( 'Credits Issued', 'loyal-system' ); ?></div>
                    <div class="ls-kpi-value"><?php echo esc_html( ls_fmt( $stats['total_credits_issued'] ) ); ?></div>
                </div>
                <div class="ls-kpi-icon-wrap"><span class="dashicons dashicons-star-filled"></span></div>
            </div>
            <div class="ls-kpi-card-footer">
                <?php echo esc_html( ls_fmt( $stats['total_credits_redeemed'] ) ); ?> <?php esc_html_e( 'redeemed', 'loyal-system' ); ?>
            </div>
        </div>

        <!-- Discounts -->
        <div class="ls-kpi-card ls-kpi-purple">
            <div class="ls-kpi-card-top">
                <div class="ls-kpi-body">
                    <div class="ls-kpi-label"><?php esc_html_e( 'Total Discounts', 'loyal-system' ); ?></div>
                    <div class="ls-kpi-value"><?php echo esc_html( ls_fmt( $stats['total_discounts'] ) ); ?></div>
                </div>
                <div class="ls-kpi-icon-wrap"><span class="dashicons dashicons-tag"></span></div>
            </div>
            <div class="ls-kpi-card-footer">
                <?php esc_html_e( 'Credits redeemed on invoices', 'loyal-system' ); ?>
            </div>
        </div>

        <!-- Tickets -->
        <div class="ls-kpi-card ls-kpi-red">
            <div class="ls-kpi-card-top">
                <div class="ls-kpi-body">
                    <div class="ls-kpi-label"><?php esc_html_e( 'Open Tickets', 'loyal-system' ); ?></div>
                    <div class="ls-kpi-value"><?php echo esc_html( $stats['open_tickets'] ); ?></div>
                </div>
                <div class="ls-kpi-icon-wrap"><span class="dashicons dashicons-tickets-alt"></span></div>
            </div>
            <div class="ls-kpi-card-footer">
                <?php echo esc_html( $stats['total_tickets'] ); ?> <?php esc_html_e( 'total tickets', 'loyal-system' ); ?>
            </div>
        </div>

    </div>

    <!-- ── Charts Row ──────────────────────────────────────────────────────── -->
    <p class="ls-dash-section-title"><?php esc_html_e( 'Analytics', 'loyal-system' ); ?></p>

    <div class="ls-dash-row">

        <!-- Revenue line chart -->
        <div class="ls-dash-col ls-dash-col-wide ls-dash-panel">
            <div class="ls-dash-panel-header">
                <span class="dashicons dashicons-chart-area"></span>
                <h3><?php esc_html_e( 'Revenue — Last 30 Days', 'loyal-system' ); ?></h3>
            </div>
            <div class="ls-dash-panel-body">
                <canvas id="ls-revenue-chart" height="90"></canvas>
            </div>
        </div>

        <!-- Ticket donut -->
        <div class="ls-dash-col ls-dash-col-narrow ls-dash-panel">
            <div class="ls-dash-panel-header">
                <span class="dashicons dashicons-tickets-alt"></span>
                <h3><?php esc_html_e( 'Tickets by Status', 'loyal-system' ); ?></h3>
            </div>
            <div class="ls-dash-panel-body" style="display:flex;flex-direction:column;align-items:center;gap:16px;">
                <?php if ( empty( $stats['tickets_by_status'] ) ) : ?>
                    <p style="color:#9ea3a8;font-size:13px;text-align:center;padding:20px 0;"><?php esc_html_e( 'No tickets yet.', 'loyal-system' ); ?></p>
                <?php else : ?>
                    <canvas id="ls-ticket-chart" style="max-width:180px;max-height:180px;"></canvas>
                    <div style="width:100%;">
                        <?php foreach ( $stats['tickets_by_status'] as $ts ) :
                            $info = $ticket_labels[ $ts->status ] ?? array( 'label' => ucfirst( $ts->status ), 'pill' => '' );
                        ?>
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:5px 0;border-bottom:1px solid #f1f5f9;">
                            <span class="ls-status-pill <?php echo esc_attr( $info['pill'] ); ?>"><?php echo esc_html( $info['label'] ); ?></span>
                            <strong style="font-size:13px;"><?php echo (int) $ts->count; ?></strong>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- ── Bottom Row ──────────────────────────────────────────────────────── -->
    <p class="ls-dash-section-title"><?php esc_html_e( 'Top Performers', 'loyal-system' ); ?></p>

    <div class="ls-dash-panel">
        <div class="ls-dash-panel-header">
            <span class="dashicons dashicons-awards"></span>
            <h3><?php esc_html_e( 'Top 5 Customers by Revenue', 'loyal-system' ); ?></h3>
        </div>
        <div class="ls-dash-panel-body" style="padding:0;">
            <?php if ( empty( $stats['top_customers'] ) ) : ?>
                <p style="padding:20px;color:#9ea3a8;font-size:13px;"><?php esc_html_e( 'No data yet.', 'loyal-system' ); ?></p>
            <?php else : ?>
            <table class="ls-dash-table">
                <thead>
                    <tr>
                        <th style="width:36px;">#</th>
                        <th><?php esc_html_e( 'Customer', 'loyal-system' ); ?></th>
                        <th><?php esc_html_e( 'Phone', 'loyal-system' ); ?></th>
                        <th style="text-align:center;"><?php esc_html_e( 'Invoices', 'loyal-system' ); ?></th>
                        <th style="text-align:right;"><?php esc_html_e( 'Revenue', 'loyal-system' ); ?> (<?php echo esc_html( $cur ); ?>)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rank = 1; foreach ( $stats['top_customers'] as $c ) : ?>
                    <tr>
                        <td><span class="ls-rank"><?php echo $rank++; ?></span></td>
                        <td><strong><?php echo esc_html( $c->full_name ?: '—' ); ?></strong></td>
                        <td style="color:#6b7280;"><?php echo esc_html( $c->phone ); ?></td>
                        <td style="text-align:center;"><?php echo (int) $c->invoices; ?></td>
                        <td style="text-align:right;" class="ls-revenue-badge"><?php echo esc_html( ls_fmt( $c->total ) ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
(function(){
    var revenueData = <?php echo wp_json_encode( $stats['revenue_by_day'] ); ?>;
    var labels = [], values = [];
    var today = new Date();
    for ( var i = 29; i >= 0; i-- ) {
        var d = new Date( today );
        d.setDate( d.getDate() - i );
        var key = d.toISOString().split('T')[0];
        labels.push( key.slice(5) );
        var found = revenueData.find( function(r){ return r.day === key; } );
        values.push( found ? parseFloat( found.total ) : 0 );
    }

    if ( document.getElementById('ls-revenue-chart') ) {
        new Chart( document.getElementById('ls-revenue-chart').getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '<?php echo esc_js( $cur ); ?>',
                    data: values,
                    borderColor: '#2271b1',
                    backgroundColor: 'rgba(34,113,177,0.07)',
                    borderWidth: 2.5,
                    pointRadius: 3,
                    pointBackgroundColor: '#2271b1',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                plugins: { legend: { display: false }, tooltip: {
                    callbacks: { label: function(ctx){ return ' ' + ctx.parsed.y.toLocaleString() + ' <?php echo esc_js( $cur ); ?>'; } }
                }},
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' },
                         ticks: { font: { size: 11 }, callback: function(v){ return v >= 1000000 ? (v/1000000).toFixed(1)+'M' : v >= 1000 ? (v/1000).toFixed(0)+'k' : v; } } }
                }
            }
        });
    }

    var ticketData = <?php echo wp_json_encode( $stats['tickets_by_status'] ); ?>;
    var statusColors = { open:'#ef4444', in_progress:'#f59e0b', resolved:'#22c55e', closed:'#94a3b8' };

    if ( document.getElementById('ls-ticket-chart') && ticketData.length ) {
        new Chart( document.getElementById('ls-ticket-chart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ticketData.map( function(t){ return t.status; } ),
                datasets: [{
                    data: ticketData.map( function(t){ return parseInt(t.count); } ),
                    backgroundColor: ticketData.map( function(t){ return statusColors[t.status] || '#cbd5e1'; } ),
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                cutout: '70%'
            }
        });
    }
})();
</script>
