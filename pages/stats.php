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
echo '<div class="row">';

// Key Metrics Cards
echo '<div class="col-md-3 mb-3">';
echo '<div class="card text-white bg-primary">';
echo '<div class="card-header">Besuche Gesamt</div>';
echo '<div class="card-body">';
echo '<h5 class="card-title">' . number_format($overview_data['visits_total']) . '</h5>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="col-md-3 mb-3">';
echo '<div class="card text-white bg-success">';
echo '<div class="card-header">Besucher Gesamt</div>';
echo '<div class="card-body">';
echo '<h5 class="card-title">' . number_format($overview_data['visitors_total']) . '</h5>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="col-md-3 mb-3">';
echo '<div class="card text-white bg-warning">';
echo '<div class="card-header">Besuche Heute</div>';
echo '<div class="card-body">';
echo '<h5 class="card-title">' . number_format($overview_data['visits_today']) . '</h5>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="col-md-3 mb-3">';
echo '<div class="card text-white bg-info">';
echo '<div class="card-header">Besucher Heute</div>';
echo '<div class="card-body">';
echo '<h5 class="card-title">' . number_format($overview_data['visitors_today']) . '</h5>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '</div>'; // end row

// Filtered Data
echo '<div class="row">';
echo '<div class="col-md-6 mb-3">';
echo '<div class="card">';
echo '<div class="card-header">Besuche im Zeitraum</div>';
echo '<div class="card-body">';
echo '<h5 class="card-title">' . number_format($overview_data['visits_datefilter']) . '</h5>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="col-md-6 mb-3">';
echo '<div class="card">';
echo '<div class="card-header">Besucher im Zeitraum</div>';
echo '<div class="card-body">';
echo '<h5 class="card-title">' . number_format($overview_data['visitors_datefilter']) . '</h5>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

// Lazy Load Sections for Charts
echo '<div class="row">';
echo '<div class="col-md-12">';
echo '<button class="btn btn-primary" onclick="loadCharts()">Detaillierte Charts laden</button>';
echo '<div id="charts-container" style="display:none; margin-top: 20px;">';
echo '<p>Charts werden geladen...</p>';
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
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(error => {
            container.innerHTML = '<p>Fehler beim Laden der Charts.</p>';
        });
}
</script>