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

// Dashboard Layout with Bootstrap Cards
echo '<div class="container-fluid">';
echo '<div class="row mb-4">';

// Key Metrics Cards with Icons
echo '<div class="col-lg-3 col-md-6 mb-4">';
echo '<div class="card border-left-primary shadow h-100 py-2">';
echo '<div class="card-body">';
echo '<div class="row no-gutters align-items-center">';
echo '<div class="col mr-2">';
echo '<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Besuche Gesamt</div>';
echo '<div class="h5 mb-0 font-weight-bold text-gray-800">' . number_format($overview_data['visits_total']) . '</div>';
echo '</div>';
echo '<div class="col-auto">';
echo '<i class="fas fa-eye fa-2x text-gray-300"></i>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="col-lg-3 col-md-6 mb-4">';
echo '<div class="card border-left-success shadow h-100 py-2">';
echo '<div class="card-body">';
echo '<div class="row no-gutters align-items-center">';
echo '<div class="col mr-2">';
echo '<div class="text-xs font-weight-bold text-success text-uppercase mb-1">Besucher Gesamt</div>';
echo '<div class="h5 mb-0 font-weight-bold text-gray-800">' . number_format($overview_data['visitors_total']) . '</div>';
echo '</div>';
echo '<div class="col-auto">';
echo '<i class="fas fa-users fa-2x text-gray-300"></i>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="col-lg-3 col-md-6 mb-4">';
echo '<div class="card border-left-warning shadow h-100 py-2">';
echo '<div class="card-body">';
echo '<div class="row no-gutters align-items-center">';
echo '<div class="col mr-2">';
echo '<div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Besuche Heute</div>';
echo '<div class="h5 mb-0 font-weight-bold text-gray-800">' . number_format($overview_data['visits_today']) . '</div>';
echo '</div>';
echo '<div class="col-auto">';
echo '<i class="fas fa-calendar-day fa-2x text-gray-300"></i>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="col-lg-3 col-md-6 mb-4">';
echo '<div class="card border-left-info shadow h-100 py-2">';
echo '<div class="card-body">';
echo '<div class="row no-gutters align-items-center">';
echo '<div class="col mr-2">';
echo '<div class="text-xs font-weight-bold text-info text-uppercase mb-1">Besucher Heute</div>';
echo '<div class="h5 mb-0 font-weight-bold text-gray-800">' . number_format($overview_data['visitors_today']) . '</div>';
echo '</div>';
echo '<div class="col-auto">';
echo '<i class="fas fa-user-clock fa-2x text-gray-300"></i>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '</div>'; // end row

// Filtered Data
echo '<div class="row mb-4">';
echo '<div class="col-lg-6 mb-4">';
echo '<div class="card shadow h-100">';
echo '<div class="card-header py-3">';
echo '<h6 class="m-0 font-weight-bold text-primary">Besuche im Zeitraum</h6>';
echo '</div>';
echo '<div class="card-body">';
echo '<div class="text-center">';
echo '<span class="h2 mb-0 font-weight-bold text-gray-800">' . number_format($overview_data['visits_datefilter']) . '</span>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="col-lg-6 mb-4">';
echo '<div class="card shadow h-100">';
echo '<div class="card-header py-3">';
echo '<h6 class="m-0 font-weight-bold text-success">Besucher im Zeitraum</h6>';
echo '</div>';
echo '<div class="card-body">';
echo '<div class="text-center">';
echo '<span class="h2 mb-0 font-weight-bold text-gray-800">' . number_format($overview_data['visitors_datefilter']) . '</span>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

// Detail Sections with Clickable Cards
echo '<div class="row">';
echo '<div class="col-12">';
echo '<h4 class="mb-4">Detaillierte Analysen</h4>';
echo '</div>';
echo '</div>';

echo '<div class="row">';

// Device Types
echo '<div class="col-lg-3 col-md-6 mb-4">';
echo '<div class="card shadow h-100" onclick="loadDetail(\'browsertype\')" style="cursor: pointer;">';
echo '<div class="card-body text-center">';
echo '<i class="fas fa-mobile-alt fa-3x text-primary mb-3"></i>';
echo '<h5 class="card-title">Gerätetypen</h5>';
echo '<p class="card-text text-muted">Smartphones, Tablets, Desktop</p>';
echo '</div>';
echo '</div>';
echo '</div>';

// Browsers
echo '<div class="col-lg-3 col-md-6 mb-4">';
echo '<div class="card shadow h-100" onclick="loadDetail(\'browser\')" style="cursor: pointer;">';
echo '<div class="card-body text-center">';
echo '<i class="fas fa-globe fa-3x text-success mb-3"></i>';
echo '<h5 class="card-title">Browser</h5>';
echo '<p class="card-text text-muted">Chrome, Firefox, Safari, etc.</p>';
echo '</div>';
echo '</div>';
echo '</div>';

// Operating Systems
echo '<div class="col-lg-3 col-md-6 mb-4">';
echo '<div class="card shadow h-100" onclick="loadDetail(\'os\')" style="cursor: pointer;">';
echo '<div class="card-body text-center">';
echo '<i class="fas fa-cogs fa-3x text-warning mb-3"></i>';
echo '<h5 class="card-title">Betriebssysteme</h5>';
echo '<p class="card-text text-muted">Windows, macOS, Linux, etc.</p>';
echo '</div>';
echo '</div>';
echo '</div>';

// Brands
echo '<div class="col-lg-3 col-md-6 mb-4">';
echo '<div class="card shadow h-100" onclick="loadDetail(\'brand\')" style="cursor: pointer;">';
echo '<div class="card-body text-center">';
echo '<i class="fas fa-building fa-3x text-info mb-3"></i>';
echo '<h5 class="card-title">Marken</h5>';
echo '<p class="card-text text-muted">Apple, Samsung, Google, etc.</p>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '</div>'; // end row

echo '<div class="row">';

// Time Analysis
echo '<div class="col-lg-3 col-md-6 mb-4">';
echo '<div class="card shadow h-100" onclick="loadDetail(\'weekday\')" style="cursor: pointer;">';
echo '<div class="card-body text-center">';
echo '<i class="fas fa-calendar-week fa-3x text-secondary mb-3"></i>';
echo '<h5 class="card-title">Wochentage</h5>';
echo '<p class="card-text text-muted">Besuche nach Wochentagen</p>';
echo '</div>';
echo '</div>';
echo '</div>';

// Hours
echo '<div class="col-lg-3 col-md-6 mb-4">';
echo '<div class="card shadow h-100" onclick="loadDetail(\'hour\')" style="cursor: pointer;">';
echo '<div class="card-body text-center">';
echo '<i class="fas fa-clock fa-3x text-dark mb-3"></i>';
echo '<h5 class="card-title">Stunden</h5>';
echo '<p class="card-text text-muted">Besuche nach Uhrzeit</p>';
echo '</div>';
echo '</div>';
echo '</div>';

// Countries
echo '<div class="col-lg-3 col-md-6 mb-4">';
echo '<div class="card shadow h-100" onclick="loadDetail(\'country\')" style="cursor: pointer;">';
echo '<div class="card-body text-center">';
echo '<i class="fas fa-map-marker-alt fa-3x text-danger mb-3"></i>';
echo '<h5 class="card-title">Länder</h5>';
echo '<p class="card-text text-muted">Geografische Verteilung</p>';
echo '</div>';
echo '</div>';
echo '</div>';

// Visit Duration
echo '<div class="col-lg-3 col-md-6 mb-4">';
echo '<div class="card shadow h-100" onclick="loadDetail(\'visitduration\')" style="cursor: pointer;">';
echo '<div class="card-body text-center">';
echo '<i class="fas fa-stopwatch fa-3x text-primary mb-3"></i>';
echo '<h5 class="card-title">Besuchsdauer</h5>';
echo '<p class="card-text text-muted">Verweildauer auf der Seite</p>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '</div>'; // end row

// Full Charts Button (Legacy)
echo '<div class="row mb-4">';
echo '<div class="col-12 text-center">';
echo '<button class="btn btn-outline-primary btn-lg" onclick="loadCharts()">Alle detaillierten Charts laden</button>';
echo '<div id="charts-container" style="display:none; margin-top: 20px;"></div>';
echo '</div>';
echo '</div>';

// Detail Modal
echo '<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">';
echo '<div class="modal-dialog modal-xl" role="document">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<h5 class="modal-title" id="detailModalLabel">Detailansicht</h5>';
echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
echo '<span aria-hidden="true">&times;</span>';
echo '</button>';
echo '</div>';
echo '<div class="modal-body" id="detailModalBody">';
echo '<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Lade Details...</p></div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '</div>'; // end container

?>

<script>
function loadCharts() {
    const container = document.getElementById('charts-container');
    container.style.display = 'block';
    container.innerHTML = '<p>Charts werden geladen...</p>';

    // Load the full stats page content via AJAX
    fetch('index.php?page=statistics/api&api=stats_load_full&date_start=<?= urlencode($filter_date_helper->date_start->format('Y-m-d')) ?>&date_end=<?= urlencode($filter_date_helper->date_end->format('Y-m-d')) ?>')
        .then(response => response.json())
        .then(result => {
            if (result.ok) {
                container.innerHTML = result.data;
            } else {
                container.innerHTML = '<p>Fehler beim Laden der Charts.</p>';
            }
        })
        .catch(error => {
            container.innerHTML = '<p>Fehler beim Laden der Charts.</p>';
        });
}

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
    modalBody.html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Lade Details...</p></div>');
    modal.modal('show');

    // Load specific detail via AJAX
    fetch('index.php?page=statistics/api&api=stats_detail&type=' + type + '&date_start=<?= urlencode($filter_date_helper->date_start->format('Y-m-d')) ?>&date_end=<?= urlencode($filter_date_helper->date_end->format('Y-m-d')) ?>')
        .then(response => response.json())
        .then(result => {
            if (result.ok) {
                modalBody.html(result.data);
            } else {
                modalBody.html('<p>Fehler beim Laden der Details.</p>');
            }
        })
        .catch(error => {
            modalBody.html('<p>Fehler beim Laden der Details.</p>');
        });
}
</script>