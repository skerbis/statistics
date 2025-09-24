<?php

use AndiLeni\Statistics\DateFilter;
use AndiLeni\Statistics\Summary;

$addon = rex_addon::get('statistics');

// BASIC INITIALISATION

$current_backend_page = rex_get('page', 'string', '');
$request_date_start = htmlspecialchars_decode(rex_request('date_start', 'string', ''));
$request_date_end = htmlspecialchars_decode(rex_request('date_end', 'string', ''));

$filter_date_helper = new DateFilter($request_date_start, $request_date_end, 'pagestats_visits_per_day');

// overview of visits and visitors of today, total and filtered by date
$overview = new Summary($filter_date_helper);
$overview_data = $overview->getSummaryData();

// FRAGMENT FOR DATE FILTER
$filter_fragment = new rex_fragment();
$filter_fragment->setVar('current_backend_page', $current_backend_page);
$filter_fragment->setVar('date_start', $filter_date_helper->date_start);
$filter_fragment->setVar('date_end', $filter_date_helper->date_end);
$filter_fragment->setVar('wts', $filter_date_helper->whole_time_start->format("Y-m-d"));

echo $filter_fragment->parse('filter.php');

// Dashboard Layout with Bootstrap 3 Panels
echo '<div class="container-fluid">';
echo '<div class="row" style="margin-bottom: 20px;">';

// Key Metrics Panels with Icons
echo '<div class="col-lg-3 col-md-6" style="margin-bottom: 20px;">';
echo '<div class="panel panel-primary" style="border-left: 4px solid #337ab7; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
echo '<div class="panel-body">';
echo '<div class="row">';
echo '<div class="col-xs-8">';
echo '<div class="text-muted" style="font-size: 11px; text-transform: uppercase; font-weight: bold;">Besuche Gesamt</div>';
echo '<div class="h4" style="margin: 0; font-weight: bold;">' . number_format($overview_data['visits_total']) . '</div>';
echo '</div>';
echo '<div class="col-xs-4 text-right">';
echo '<i class="fa fa-eye fa-2x text-muted"></i>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="col-lg-3 col-md-6" style="margin-bottom: 20px;">';
echo '<div class="panel panel-success" style="border-left: 4px solid #5cb85c; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
echo '<div class="panel-body">';
echo '<div class="row">';
echo '<div class="col-xs-8">';
echo '<div class="text-muted" style="font-size: 11px; text-transform: uppercase; font-weight: bold;">Besucher Gesamt</div>';
echo '<div class="h4" style="margin: 0; font-weight: bold;">' . number_format($overview_data['visitors_total']) . '</div>';
echo '</div>';
echo '<div class="col-xs-4 text-right">';
echo '<i class="fa fa-users fa-2x text-muted"></i>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="col-lg-3 col-md-6" style="margin-bottom: 20px;">';
echo '<div class="panel panel-warning" style="border-left: 4px solid #f0ad4e; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
echo '<div class="panel-body">';
echo '<div class="row">';
echo '<div class="col-xs-8">';
echo '<div class="text-muted" style="font-size: 11px; text-transform: uppercase; font-weight: bold;">Besuche Heute</div>';
echo '<div class="h4" style="margin: 0; font-weight: bold;">' . number_format($overview_data['visits_today']) . '</div>';
echo '</div>';
echo '<div class="col-xs-4 text-right">';
echo '<i class="fa fa-calendar fa-2x text-muted"></i>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="col-lg-3 col-md-6" style="margin-bottom: 20px;">';
echo '<div class="panel panel-info" style="border-left: 4px solid #5bc0de; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
echo '<div class="panel-body">';
echo '<div class="row">';
echo '<div class="col-xs-8">';
echo '<div class="text-muted" style="font-size: 11px; text-transform: uppercase; font-weight: bold;">Besucher Heute</div>';
echo '<div class="h4" style="margin: 0; font-weight: bold;">' . number_format($overview_data['visitors_today']) . '</div>';
echo '</div>';
echo '<div class="col-xs-4 text-right">';
echo '<i class="fa fa-clock-o fa-2x text-muted"></i>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '</div>'; // end row
// Additional KPI row: this week metrics (last 7 days)
$week_start = (new DateTime())->modify('-7 day')->format('Y-m-d');
$week_end = (new DateTime())->format('Y-m-d');

$sql = rex_sql::factory();
$visits_this_week = $sql->getArray('SELECT IFNULL(SUM(count),0) as c FROM ' . rex::getTable('pagestats_visits_per_day') . ' WHERE date BETWEEN :start AND :end', ['start' => $week_start, 'end' => $week_end]);
$visits_this_week = $visits_this_week[0]['c'] ?? 0;

$visitors_this_week = $sql->getArray('SELECT IFNULL(SUM(count),0) as c FROM ' . rex::getTable('pagestats_visitors_per_day') . ' WHERE date BETWEEN :start AND :end', ['start' => $week_start, 'end' => $week_end]);
$visitors_this_week = $visitors_this_week[0]['c'] ?? 0;

// Most viewed article this week (exclude root '/'), match path part
$top_article = $sql->getArray('SELECT SUBSTRING(url, LOCATE("/", url)) as path, SUM(count) as c FROM ' . rex::getTable('pagestats_visits_per_url') . ' WHERE date BETWEEN :start AND :end AND SUBSTRING(url, LOCATE("/", url)) != "/" GROUP BY path ORDER BY c DESC LIMIT 1', ['start' => $week_start, 'end' => $week_end]);
$top_article_path = $top_article[0]['path'] ?? '-';
$top_article_hits = $top_article[0]['c'] ?? 0;

// pageviews per visitor this week (guard divide by zero)
$pv_per_visitor = $visitors_this_week > 0 ? round($visits_this_week / $visitors_this_week, 2) : 0;

echo '<div class="row" style="margin-bottom:20px;">';

// Visits this week
echo '<div class="col-lg-3 col-md-6" style="margin-bottom: 10px;">';
echo '<div class="panel panel-primary" style="border-left: 4px solid #2e6da4;">';
echo '<div class="panel-body text-center">';
echo '<div class="text-muted" style="font-size:11px; text-transform:uppercase; font-weight:bold;">Besuche diese Woche</div>';
echo '<div class="h4" style="margin:0; font-weight:bold;">' . number_format($visits_this_week) . '</div>';
echo '</div></div></div>';

// Visitors this week
echo '<div class="col-lg-3 col-md-6" style="margin-bottom: 10px;">';
echo '<div class="panel panel-success" style="border-left: 4px solid #3c763d;">';
echo '<div class="panel-body text-center">';
echo '<div class="text-muted" style="font-size:11px; text-transform:uppercase; font-weight:bold;">Besucher diese Woche</div>';
echo '<div class="h4" style="margin:0; font-weight:bold;">' . number_format($visitors_this_week) . '</div>';
echo '</div></div></div>';

// Top article this week
echo '<div class="col-lg-3 col-md-6" style="margin-bottom: 10px;">';
echo '<div class="panel panel-info" style="border-left: 4px solid #31708f;">';
echo '<div class="panel-body text-center">';
echo '<div class="text-muted" style="font-size:11px; text-transform:uppercase; font-weight:bold;">Meist aufgerufener Artikel (Woche)</div>';
echo '<div class="h5" style="margin:0; font-weight:bold;">' . htmlspecialchars($top_article_path) . '</div>';
echo '<div class="text-muted" style="font-size:12px;">' . number_format($top_article_hits) . ' Aufrufe</div>';
echo '</div></div></div>';

// Pageviews per visitor this week
echo '<div class="col-lg-3 col-md-6" style="margin-bottom: 10px;">';
echo '<div class="panel panel-warning" style="border-left: 4px solid #8a6d3b;">';
echo '<div class="panel-body text-center">';
echo '<div class="text-muted" style="font-size:11px; text-transform:uppercase; font-weight:bold;">Seiten/Sitzung (Woche)</div>';
echo '<div class="h4" style="margin:0; font-weight:bold;">' . $pv_per_visitor . '</div>';
echo '</div></div></div>';

echo '</div>'; // end week KPI row

// Filtered Data
echo '<div class="row" style="margin-bottom: 20px;">';
echo '<div class="col-lg-6" style="margin-bottom: 20px;">';
echo '<div class="panel panel-default" style="box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
echo '<div class="panel-heading">';
echo '<h3 class="panel-title">Besuche im Zeitraum</h3>';
echo '</div>';
echo '<div class="panel-body text-center">';
echo '<span class="h2" style="margin: 0; font-weight: bold;">' . number_format($overview_data['visits_datefilter']) . '</span>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="col-lg-6" style="margin-bottom: 20px;">';
echo '<div class="panel panel-default" style="box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
echo '<div class="panel-heading">';
echo '<h3 class="panel-title">Besucher im Zeitraum</h3>';
echo '</div>';
echo '<div class="panel-body text-center">';
echo '<span class="h2" style="margin: 0; font-weight: bold;">' . number_format($overview_data['visitors_datefilter']) . '</span>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

// Detail Sections with Clickable Panels
echo '<div class="row">';
echo '<div class="col-xs-12">';
echo '<h4 style="margin-bottom: 20px;">Detaillierte Analysen</h4>';
echo '</div>';
echo '</div>';

echo '<div class="row">';

// Device Types
echo '<div class="col-lg-3 col-md-6" style="margin-bottom: 20px;">';
echo '<div class="panel panel-default" style="cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onclick="loadDetail(\'browsertype\')">';
echo '<div class="panel-body text-center">';
echo '<i class="fa fa-mobile fa-3x text-primary" style="margin-bottom: 15px;"></i>';
echo '<h5>Gerätetypen</h5>';
echo '<p class="text-muted">Smartphones, Tablets, Desktop</p>';
echo '</div>';
echo '</div>';
echo '</div>';

// Browsers
echo '<div class="col-lg-3 col-md-6" style="margin-bottom: 20px;">';
echo '<div class="panel panel-default" style="cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onclick="loadDetail(\'browser\')">';
echo '<div class="panel-body text-center">';
echo '<i class="fa fa-globe fa-3x text-success" style="margin-bottom: 15px;"></i>';
echo '<h5>Browser</h5>';
echo '<p class="text-muted">Chrome, Firefox, Safari, etc.</p>';
echo '</div>';
echo '</div>';
echo '</div>';

// Operating Systems
echo '<div class="col-lg-3 col-md-6" style="margin-bottom: 20px;">';
echo '<div class="panel panel-default" style="cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onclick="loadDetail(\'os\')">';
echo '<div class="panel-body text-center">';
echo '<i class="fa fa-cogs fa-3x text-warning" style="margin-bottom: 15px;"></i>';
echo '<h5>Betriebssysteme</h5>';
echo '<p class="text-muted">Windows, macOS, Linux, etc.</p>';
echo '</div>';
echo '</div>';
echo '</div>';

// Brands
echo '<div class="col-lg-3 col-md-6" style="margin-bottom: 20px;">';
echo '<div class="panel panel-default" style="cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onclick="loadDetail(\'brand\')">';
echo '<div class="panel-body text-center">';
echo '<i class="fa fa-building fa-3x text-info" style="margin-bottom: 15px;"></i>';
echo '<h5>Marken</h5>';
echo '<p class="text-muted">Apple, Samsung, Google, etc.</p>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '</div>'; // end row

echo '<div class="row">';

// Time Analysis
echo '<div class="col-lg-3 col-md-6" style="margin-bottom: 20px;">';
echo '<div class="panel panel-default" style="cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onclick="loadDetail(\'weekday\')">';
echo '<div class="panel-body text-center">';
echo '<i class="fa fa-calendar fa-3x text-muted" style="margin-bottom: 15px;"></i>';
echo '<h5>Wochentage</h5>';
echo '<p class="text-muted">Besuche nach Wochentagen</p>';
echo '</div>';
echo '</div>';
echo '</div>';

// Hours
echo '<div class="col-lg-3 col-md-6" style="margin-bottom: 20px;">';
echo '<div class="panel panel-default" style="cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onclick="loadDetail(\'hour\')">';
echo '<div class="panel-body text-center">';
echo '<i class="fa fa-clock-o fa-3x text-muted" style="margin-bottom: 15px;"></i>';
echo '<h5>Stunden</h5>';
echo '<p class="text-muted">Besuche nach Uhrzeit</p>';
echo '</div>';
echo '</div>';
echo '</div>';

// Countries
echo '<div class="col-lg-3 col-md-6" style="margin-bottom: 20px;">';
echo '<div class="panel panel-default" style="cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onclick="loadDetail(\'country\')">';
echo '<div class="panel-body text-center">';
echo '<i class="fa fa-map-marker fa-3x text-danger" style="margin-bottom: 15px;"></i>';
echo '<h5>Länder</h5>';
echo '<p class="text-muted">Geografische Verteilung</p>';
echo '</div>';
echo '</div>';
echo '</div>';

// Visit Duration
echo '<div class="col-lg-3 col-md-6" style="margin-bottom: 20px;">';
echo '<div class="panel panel-default" style="cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onclick="loadDetail(\'visitduration\')">';
echo '<div class="panel-body text-center">';
echo '<i class="fa fa-clock-o fa-3x text-primary" style="margin-bottom: 15px;"></i>';
echo '<h5>Besuchsdauer</h5>';
echo '<p class="text-muted">Verweildauer auf der Seite</p>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '</div>'; // end row

// Full charts have been disabled — only detail modals are provided now.

// Detail Modal
echo '<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">';
echo '<div class="modal-dialog modal-lg" role="document">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
echo '<span aria-hidden="true">&times;</span>';
echo '</button>';
echo '<h4 class="modal-title" id="detailModalLabel">Detailansicht</h4>';
echo '</div>';
echo '<div class="modal-body" id="detailModalBody">';
echo '<div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i><p>Lade Details...</p></div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '</div>'; // end container

?>

<script>
// Full charts disabled: loadCharts() removed to prevent fetching the full dashboard.

function loadDetail(type) {
    const modal = $('#detailModal');
    const modalBody = $('#detailModalBody');
    const titles = {
        'browsertype': 'Gerätetypen',
        'browser': 'Browser',
        'os': 'Betriebssysteme',
        'brand': 'Marken',
        'weekday': 'Wochentage',
        'hour': 'Stunden',
        'country': 'Länder',
        'visitduration': 'Besuchsdauer'
    };

    $('#detailModalLabel').text(titles[type] || 'Detailansicht');
    modalBody.html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i><p>Lade Details...</p></div>');
    modal.modal('show');

    // Read date range from the visible inputs so the modal respects user selection
    var startInput = document.getElementById('statistics_datefilter_start');
    var endInput = document.getElementById('statistics_datefilter_end');
    var startVal = (startInput && startInput.value) ? startInput.value : '<?= $filter_date_helper->date_start->format('Y-m-d') ?>';
    var endVal = (endInput && endInput.value) ? endInput.value : '<?= $filter_date_helper->date_end->format('Y-m-d') ?>';
    const dateQs = '&date_start=' + encodeURIComponent(startVal) + '&date_end=' + encodeURIComponent(endVal);

    loadFetch('/redaxo/index.php?rex-api-call=stats_detail&type=' + type + dateQs,
              '/index.php?rex-api-call=stats_detail&type=' + type + dateQs)
        .then(async response => {
            const ct = (response.headers.get('content-type') || '').toLowerCase();
            const text = await response.text();

            if (ct.indexOf('application/json') !== -1) {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    return { ok: false, debugText: text };
                }
            }

            // If the response is HTML, treat it as a successful fragment
            if (ct.indexOf('text/html') !== -1) {
                return { ok: true, html: text };
            }

            // Fallback: treat everything else as debug text
            return { ok: false, debugText: text };
        })
        .then(result => {
            if (result && result.ok) {
                if (result.html) {
                    modalBody.html(result.html);
                    // initialize charts inside the loaded fragment
                    initDetailCharts(type, dateQs);

                    // Execute any inline or external scripts from the loaded fragment
                    modalBody.find('script').each(function() {
                        var s = document.createElement('script');
                        if (this.src) {
                            s.src = this.src;
                            s.async = false;
                            document.head.appendChild(s);
                        } else {
                            try {
                                s.text = this.innerHTML;
                                document.head.appendChild(s);
                                document.head.removeChild(s);
                            } catch (e) {
                                // fallback: evaluate
                                try { window.eval(this.innerHTML); } catch (ee) { /* ignore */ }
                            }
                        }
                    });
                } else {
                    modalBody.html(result.data || result.msg || '');
                }
            } else if (result && result.debugText) {
                modalBody.html('<pre style="white-space:pre-wrap;">' + escapeHtml(result.debugText) + '</pre>');
            } else {
                modalBody.html('<p>Fehler beim Laden der Details.</p>');
            }
        })
        .catch(error => {
            modalBody.html('<p>Fehler beim Laden der Details.</p>');
        });

    // small helper to escape HTML for debug output
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/\"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
}

// client-side helper to fetch chart data for details
async function loadDetailChartData(type, dateQs) {
    const response = await fetch('/redaxo/index.php?rex-api-call=stats_charts&type=' + encodeURIComponent(type) + (dateQs || ''), { credentials: 'same-origin' });
    if (!response.ok) throw new Error('Chart data fetch failed');
    return response.json();
}

// initialize charts for the given detail type
function initDetailCharts(type, dateQs) {
    try {
        switch(type) {
            case 'browser':
            case 'browsertype':
            case 'os':
            case 'brand':
                loadDetailChartData(type, dateQs).then(data => {
                    const el = document.getElementById('chart_' + type + '_detail');
                    if (!el) return;
                    const theme = (typeof rex !== 'undefined' && (rex.theme == 'dark' || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches && rex.theme == 'auto'))) ? 'dark' : 'shine';
                    const chart = echarts.init(el, theme);
                    chart.setOption({
                        tooltip: { trigger: 'item', formatter: '{b}: <b>{c}</b> ({d}%)' },
                        series: [{ type: 'pie', radius: '85%', data: data }]
                    });
                }).catch(()=>{});
                break;
            case 'weekday':
                // weekday remains a bar chart (labels/values)
                loadDetailChartData(type, dateQs).then(data => {
                    const el = document.getElementById('chart_' + type + '_detail');
                    if (!el) return;
                    const theme = (typeof rex !== 'undefined' && (rex.theme == 'dark' || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches && rex.theme == 'auto'))) ? 'dark' : 'shine';
                    const chart = echarts.init(el, theme);
                    chart.setOption({
                        tooltip: { trigger: 'axis' },
                        xAxis: { type: 'category', data: data.labels || [] },
                        yAxis: { type: 'value' },
                        series: [{ type: 'bar', data: data.values || data }]
                    });
                }).catch(()=>{});
                break;

            case 'hour':
                // hours as bar chart with 24 labels
                loadDetailChartData(type, dateQs).then(data => {
                    const el = document.getElementById('chart_hour_detail');
                    if (!el) return;
                    const theme = (typeof rex !== 'undefined' && (rex.theme == 'dark' || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches && rex.theme == 'auto'))) ? 'dark' : 'shine';
                    const chart = echarts.init(el, theme);
                    chart.setOption({
                        tooltip: { trigger: 'axis' },
                        xAxis: { type: 'category', data: data.labels || [] },
                        yAxis: { type: 'value' },
                        series: [{ type: 'bar', data: data.values || data }]
                    });
                }).catch(()=>{});
                break;
            // hour, country, visitduration intentionally show only a table — no charts requested
        }
    } catch (e) {
        /* ignore chart init errors */
    }
}
</script>

<script>
// Helper: try primaryURL, if 404 then try fallbackURL
function loadFetch(primaryURL, fallbackURL) {
    return fetch(primaryURL, { credentials: 'same-origin' }).then(resp => {
        if (resp.status === 404 && fallbackURL) {
            return fetch(fallbackURL, { credentials: 'same-origin' });
        }
        return resp;
    }).catch(err => {
        if (fallbackURL) return fetch(fallbackURL, { credentials: 'same-origin' });
        throw err;
    });
}
</script>