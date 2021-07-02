<?php

rex_sql_table::get(rex::getTable('pagestats_media'))
    ->ensureColumn(new rex_sql_column('url', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->ensureColumn(new rex_sql_column('date', 'date'))
    ->ensure();
