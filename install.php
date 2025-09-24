<?php


// Ensure pagestats_data columns exist first
rex_sql_table::get(rex::getTable('pagestats_data'))
    ->ensureColumn(new rex_sql_column('type', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->ensure();

// Before we set the PRIMARY KEY on (type,name) make sure there are no duplicate
// (type,name) combinations. If duplicates exist, aggregate them into a
// temporary table and swap atomically. This avoids ALTER TABLE failing on install
// when the restored backup contains duplicate rows.
try {
    $sql = rex_sql::factory();
    $fullTable = rex::getTable('pagestats_data');
    $prefix = rex::getTablePrefix();
    $shortTable = substr($fullTable, strlen($prefix));

    // Check for at least one duplicate (type,name)
    $hasDup = $sql->getArray('SELECT `type`,`name`,COUNT(*) AS cnt FROM `' . $fullTable . '` GROUP BY `type`,`name` HAVING cnt>1 LIMIT 1');
    if (!empty($hasDup)) {
        // Fetch column list to preserve non-aggregated columns using ANY_VALUE()
        $colsRes = $sql->getArray("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . $shortTable . "' ORDER BY ORDINAL_POSITION");
        $cols = array_map(function ($r) { return $r['COLUMN_NAME']; }, $colsRes);

        $selectParts = [];
        foreach ($cols as $col) {
            if ($col === 'type' || $col === 'name') {
                continue;
            }
            if ($col === 'count') {
                $selectParts[] = 'SUM(`count`) AS `count`';
                continue;
            }
            // Preserve other columns with ANY_VALUE to keep table shape
            $selectParts[] = 'ANY_VALUE(`' . $col . '`) AS `' . $col . '`';
        }

        $tmpTable = $fullTable . '_tmp';

        // Create aggregated temporary table
        $sql->setQuery('DROP TABLE IF EXISTS `' . $tmpTable . '`');
        $createSql = 'CREATE TABLE `' . $tmpTable . '` AS SELECT `type`, `name`, ' . implode(', ', $selectParts) . ' FROM `' . $fullTable . '` GROUP BY `type`, `name`';
        $sql->setQuery($createSql);

        // Sanity check: compare totals
        $old = $sql->getArray('SELECT COUNT(*) AS rcount, SUM(`count`) AS tot FROM `' . $fullTable . '`');
        $new = $sql->getArray('SELECT COUNT(*) AS rcount, SUM(`count`) AS tot FROM `' . $tmpTable . '`');

        if (!empty($old) && !empty($new)) {
            // If counts match, swap tables atomically
            if ($old[0]['tot'] == $new[0]['tot']) {
                $bak = $fullTable . '_bak_' . time();
                $sql->setQuery('RENAME TABLE `' . $fullTable . '` TO `' . $bak . '`, `' . $tmpTable . '` TO `' . $fullTable . '`');
            }
            // If totals don't match we still keep tmp table for manual inspection
        }
    }
} catch (rex_sql_exception $e) {
    // If anything fails here, ignore and continue; install will later try to set PK and may fail,
    // but we avoid breaking install process with fatal errors from this helper.
    rex_logger::logException($e);
}

// Now ensure indexes and primary key
rex_sql_table::get(rex::getTable('pagestats_data'))
    ->setPrimaryKey(['type', 'name'])
    ->ensureIndex(new rex_sql_index('type_count', ['type', 'count']))
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_visits_per_day'))
    ->ensureColumn(new rex_sql_column('date', 'date'))
    ->ensureColumn(new rex_sql_column('domain', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['date', 'domain'])
    ->ensureIndex(new rex_sql_index('date', ['date']))
    ->ensureIndex(new rex_sql_index('date_domain', ['date', 'domain']))
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_visitors_per_day'))
    ->ensureColumn(new rex_sql_column('date', 'date'))
    ->ensureColumn(new rex_sql_column('domain', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['date', 'domain'])
    ->ensureIndex(new rex_sql_index('date', ['date']))
    ->ensureIndex(new rex_sql_index('date_domain', ['date', 'domain']))
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_visits_per_url'))
    ->ensureColumn(new rex_sql_column('hash', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('date', 'date'))
    ->ensureColumn(new rex_sql_column('url', 'varchar(2048)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['hash'])
    ->ensureIndex(new rex_sql_index('date', ['date']))
    ->ensure();

// Add index on url prefix manually, since rex_sql_index doesn't support prefixes
$sql = rex_sql::factory();
try {
    $sql->setQuery("ALTER TABLE " . rex::getTable('pagestats_visits_per_url') . " ADD INDEX idx_url (url(255))");
} catch (rex_sql_exception $e) {
    // Index might already exist, ignore
}

rex_sql_table::get(rex::getTable('pagestats_urlstatus'))
    ->ensureColumn(new rex_sql_column('hash', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('url', 'varchar(2048)'))
    ->ensureColumn(new rex_sql_column('status', 'varchar(255)'))
    ->setPrimaryKey(['hash'])
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_bot'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('category', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('producer', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['name', 'category', 'producer'])
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_hash'))
    ->ensureColumn(new rex_sql_column('hash', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('datetime', 'datetime'))
    ->setPrimaryKey(['hash'])
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_referer'))
    ->removeColumn('id')
    ->ensureColumn(new rex_sql_column('hash', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('referer', 'varchar(2048)'))
    ->ensureColumn(new rex_sql_column('date', 'date'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['hash'])
    ->ensureIndex(new rex_sql_index('date', ['date']))
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_sessionstats'))
    ->ensureColumn(new rex_sql_column('token', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('lastpage', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('lastvisit', 'datetime'))
    ->ensureColumn(new rex_sql_column('visitduration', 'int'))
    ->ensureColumn(new rex_sql_column('pagecount', 'int'))
    ->setPrimaryKey(['token'])
    ->ensureIndex(new rex_sql_index('lastvisit', ['lastvisit']))
    ->ensure();

// media
rex_sql_table::get(rex::getTable('pagestats_media'))
    ->ensureColumn(new rex_sql_column('url', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('date', 'date'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['url', 'date'])
    ->ensureIndex(new rex_sql_index('date', ['date']))
    ->ensure();


// api
rex_sql_table::get(rex::getTable('pagestats_api'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('date', 'date'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['name', 'date'])
    ->ensureIndex(new rex_sql_index('date', ['date']))
    ->ensure();

// Precomputed monthly aggregates
rex_sql_table::get(rex::getTable('pagestats_monthly_visits'))
    ->ensureColumn(new rex_sql_column('year', 'int'))
    ->ensureColumn(new rex_sql_column('month', 'int'))
    ->ensureColumn(new rex_sql_column('domain', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['year', 'month', 'domain'])
    ->ensureIndex(new rex_sql_index('year_month', ['year', 'month']))
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_monthly_visitors'))
    ->ensureColumn(new rex_sql_column('year', 'int'))
    ->ensureColumn(new rex_sql_column('month', 'int'))
    ->ensureColumn(new rex_sql_column('domain', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['year', 'month', 'domain'])
    ->ensureIndex(new rex_sql_index('year_month', ['year', 'month']))
    ->ensure();

// Precomputed yearly aggregates
rex_sql_table::get(rex::getTable('pagestats_yearly_visits'))
    ->ensureColumn(new rex_sql_column('year', 'int'))
    ->ensureColumn(new rex_sql_column('domain', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['year', 'domain'])
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_yearly_visitors'))
    ->ensureColumn(new rex_sql_column('year', 'int'))
    ->ensureColumn(new rex_sql_column('domain', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['year', 'domain'])
    ->ensure();

// Precomputed page totals
rex_sql_table::get(rex::getTable('pagestats_pages_total'))
    ->ensureColumn(new rex_sql_column('id', 'int', true, null))
    ->ensureColumn(new rex_sql_column('url', 'varchar(2048)'))
    ->ensureColumn(new rex_sql_column('total_count', 'int'))
    ->ensureColumn(new rex_sql_column('last_updated', 'datetime'))
    ->setPrimaryKey(['id'])
    ->ensureIndex(new rex_sql_index('total_count', ['total_count']))
    ->ensure();

// Add unique index on url prefix manually
$sql = rex_sql::factory();
try {
    $sql->setQuery("ALTER TABLE " . rex::getTable('pagestats_pages_total') . " ADD UNIQUE INDEX idx_url_unique (url(255))");
} catch (rex_sql_exception $e) {
    // Index might already exist, ignore
}

// ip 2 geo database installation
$today = new DateTimeImmutable();
$dbUrl = "https://download.db-ip.com/free/dbip-country-lite-{$today->format('Y-m')}.mmdb.gz";

try {
    $socket = rex_socket::factoryUrl($dbUrl);

    $response = $socket->doGet();
    if ($response->isOk()) {
        $body = $response->getBody();
        $body = gzdecode($body);
        rex_file::put(rex_path::addonData("statistics", "ip2geo.mmdb"), $body);
        return true;
    }

    return false;
} catch (rex_socket_exception $e) {
    rex_logger::logException($e);
    return false;
}
