<?php
// Safe re-aggregation script for pagestats_visits_per_url
// Place this file in the addon tools folder and open it from the backend (requires backend session).
// It performs a dry-run first and only executes destructive steps after explicit confirmation.

use rex;
use rex_sql;

if (!defined('REX')) {
    echo "This script must be executed inside REDAXO backend.";
    exit;
}

if (!rex::isBackend()) {
    echo "Only available in backend.";
    exit;
}

// require a user with settings permission
if (!rex::getUser() || !rex::getUser()->hasPerm('statistics[settings]')) {
    echo rex_view::error('Permission denied. Require statistics[settings] permission.');
    exit;
}

echo '<div class="rex-page">';
echo '<h2>Re-aggregate pagestats_visits_per_url (safe mode)</h2>';

$sql = rex_sql::factory();

// basic info
$totalRows = $sql->getArray('SELECT COUNT(*) as c FROM ' . rex::getTable('pagestats_visits_per_url'))[0]['c'] ?? 0;
$distinctUrls = $sql->getArray('SELECT COUNT(DISTINCT url) as c FROM ' . rex::getTable('pagestats_visits_per_url'))[0]['c'] ?? 0;
$distinctDates = $sql->getArray('SELECT COUNT(DISTINCT date) as c FROM ' . rex::getTable('pagestats_visits_per_url'))[0]['c'] ?? 0;

echo '<p>Total rows: <b>' . number_format($totalRows) . '</b><br />Distinct URLs: <b>' . number_format($distinctUrls) . '</b><br />Distinct Dates: <b>' . number_format($distinctDates) . '</b></p>';

// show top 10 paths (path = substring starting at first slash)
$top = $sql->getArray('SELECT SUBSTRING(url, LOCATE("/", url)) as path, SUM(count) as c FROM ' . rex::getTable('pagestats_visits_per_url') . ' GROUP BY path ORDER BY c DESC LIMIT 10');

echo '<h3>Top 10 paths (preview)</h3>';
echo '<table class="table table-striped"><thead><tr><th>Path</th><th>Hits</th></tr></thead><tbody>';
foreach ($top as $row) {
    echo '<tr><td>' . htmlspecialchars($row['path']) . '</td><td>' . number_format($row['c']) . '</td></tr>';
}
echo '</tbody></table>';

// Dry-run summary for aggregation by (url,date)
$aggPreview = $sql->getArray('SELECT COUNT(*) as rows_after FROM (SELECT url, date, SUM(count) as s FROM ' . rex::getTable('pagestats_visits_per_url') . ' GROUP BY url, date) t');
$rowsAfter = $aggPreview[0]['rows_after'] ?? 0;

echo '<p>Rows after aggregation (group by url,date): <b>' . number_format($rowsAfter) . '</b></p>';

// If confirmed, proceed
$confirm = rex_request('confirm', 'int', 0);

if ($confirm !== 1) {
    // show confirmation form
    $csrf = rex_csrf_token::factory('statistics_reaggregate');
    echo '<form method="post">';
    echo $csrf->getHiddenField();
    echo '<p><strong>Dry-run complete.</strong> If you want to proceed with the re-aggregation the script will:</p>';
    echo '<ul><li>Create a new aggregated table <code>' . rex::getTable('pagestats_visits_per_url_new') . '</code></li>';
    echo '<li>Rename the current table to <code>' . rex::getTable('pagestats_visits_per_url_backup_'.date('YmdHis')) . '</code></li>';
    echo '<li>Rename the new table to the original name (atomic rename)</li></ul>';
    echo '<p><strong>Make sure you have a DB backup before proceeding.</strong></p>';
    echo '<p><input type="hidden" name="confirm" value="1" /></p>';
    echo '<p><button class="btn btn-danger" type="submit">Proceed with re-aggregation</button> <a class="btn btn-default" href="' . rex_url::backendPage('statistics/migrate') . '">Cancel</a></p>';
    echo '</form>';
    echo '</div>';
    exit;
}

// CSRF check
if (!rex_csrf_token::factory('statistics_reaggregate')->isValid()) {
    echo rex_view::error('Invalid CSRF token');
    exit;
}

// perform aggregation
try {
    // 1) create aggregated table
    $newTable = rex::getTable('pagestats_visits_per_url_new');
    $origTable = rex::getTable('pagestats_visits_per_url');
    $backupTable = rex::getTable('pagestats_visits_per_url_backup_' . date('YmdHis'));

    // Drop newTable if exists (clean up)
    $sql->setQuery('DROP TABLE IF EXISTS ' . $newTable);

    // Create new aggregated table (url, date, count)
    $createSql = 'CREATE TABLE ' . $newTable . ' AS SELECT url, date, SUM(count) as count FROM ' . $origTable . ' GROUP BY url, date';
    $sql->setQuery($createSql);

    // Rename tables atomically: orig -> backup, new -> orig
    $renameSql = 'RENAME TABLE ' . $origTable . ' TO ' . $backupTable . ', ' . $newTable . ' TO ' . $origTable;
    $sql->setQuery($renameSql);

    echo rex_view::success('Re-aggregation completed. Original table renamed to ' . htmlspecialchars($backupTable));
    echo '<p>Note: indexes and constraints on the new table may be missing. Review and re-create indexes if necessary.</p>';

} catch (Exception $e) {
    echo rex_view::error('Error during re-aggregation: ' . $e->getMessage());
}

echo '</div>';

?>
