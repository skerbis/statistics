<?php


rex_sql_table::get(rex::getTable('pagestats_data'))
    ->ensureColumn(new rex_sql_column('type', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
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
    ->ensureColumn(new rex_sql_column('id', 'int', false, true))
    ->ensureColumn(new rex_sql_column('url', 'varchar(2048)'))
    ->ensureColumn(new rex_sql_column('total_count', 'int'))
    ->ensureColumn(new rex_sql_column('last_updated', 'datetime'))
    ->setPrimaryKey(['id'])
    ->ensureIndex(new rex_sql_index('url_unique', ['url(255)'], rex_sql_index::UNIQUE))
    ->ensureIndex(new rex_sql_index('total_count', ['total_count']))
    ->ensure();

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
