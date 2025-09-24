<?php

use AndiLeni\Statistics\DateFilter;

$addon = rex_addon::get('statistics');

$current_backend_page = rex_get('page', 'string', '');
$request_date_start = htmlspecialchars_decode(rex_request('date_start', 'string', ''));
$request_date_end = htmlspecialchars_decode(rex_request('date_end', 'string', ''));

$filter_date_helper = new DateFilter($request_date_start, $request_date_end, 'pagestats_visits_per_url');

// FRAGMENT FOR DATE FILTER
$filter_fragment = new rex_fragment();
$filter_fragment->setVar('current_backend_page', $current_backend_page);
$filter_fragment->setVar('date_start', $filter_date_helper->date_start);
$filter_fragment->setVar('date_end', $filter_date_helper->date_end);
$filter_fragment->setVar('wts', $filter_date_helper->whole_time_start->format("Y-m-d"));

echo $filter_fragment->parse('filter.php');

// Get all articles
$sql = rex_sql::factory();
$articles = $sql->getArray("SELECT id, name, clang_id FROM rex_article WHERE status = 1 ORDER BY name");

// Collect all paths for batch query
$paths = [];
$article_map = [];
foreach ($articles as $article) {
    $url = rex_getUrl($article['id'], $article['clang_id']);
    // Remove protocol and domain to match stored URLs
    $parsed = parse_url($url);
    $path = isset($parsed['path']) ? $parsed['path'] : '/';
    if (isset($parsed['query']) && $parsed['query'] != '') {
        $path .= '?' . $parsed['query'];
    }

    $paths[] = $path;
    $article_map[$path] = $article;
}

// Batch query to get stats for all paths at once
$path_placeholders = str_repeat('?,', count($paths) - 1) . '?';
$query = "SELECT SUBSTRING(url, LOCATE('/', url)) AS path_part, SUM(count) AS total 
          FROM " . rex::getTable('pagestats_visits_per_url') . " 
          WHERE SUBSTRING(url, LOCATE('/', url)) IN ($path_placeholders) 
          AND date BETWEEN ? AND ? 
          GROUP BY path_part";
$params = array_merge($paths, [$filter_date_helper->date_start->format('Y-m-d'), $filter_date_helper->date_end->format('Y-m-d')]);
$stats = $sql->getArray($query, $params);

// Map stats back to articles
$article_stats = [];
foreach ($stats as $stat) {
    $path = $stat['path_part'];
    if (isset($article_map[$path])) {
        $article = $article_map[$path];
        $total = $stat['total'];
        if ($total > 0) {
            $article_stats[] = [
                'id' => $article['id'],
                'name' => $article['name'],
                'clang_id' => $article['clang_id'],
                'url' => rex_getUrl($article['id'], $article['clang_id']),
                'path' => $path,
                'total' => $total
            ];
        }
    }
}

// Sort by total desc
usort($article_stats, function($a, $b) {
    return $b['total'] <=> $a['total'];
});

// Display table
$table = '<table class="table table-striped table-hover dt_order_second">';
$table .= '<thead><tr><th>Artikel</th><th>Sprache</th><th>Besuche</th><th>Link</th></tr></thead><tbody>';
foreach ($article_stats as $stat) {
    $table .= '<tr>';
    $table .= '<td>' . htmlspecialchars($stat['name']) . ' (ID: ' . $stat['id'] . ')</td>';
    $table .= '<td>' . $stat['clang_id'] . '</td>';
    $table .= '<td>' . $stat['total'] . '</td>';
    $table .= '<td><a href="' . $stat['url'] . '" target="_blank">Ansehen</a> | <a href="index.php?page=statistics/pages&url=' . urlencode($stat['path']) . '&date_start=' . $filter_date_helper->date_start->format('Y-m-d') . '&date_end=' . $filter_date_helper->date_end->format('Y-m-d') . '">Details</a></td>';
    $table .= '</tr>';
}
$table .= '</tbody></table>';

$fragment = new rex_fragment();
$fragment->setVar('title', 'Artikel-Statistiken');
$fragment->setVar('body', $table, false);
echo $fragment->parse('core/page/section.php');

?>