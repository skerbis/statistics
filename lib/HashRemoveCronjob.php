<?php

class rex_statistics_hashremove_cronjob extends rex_cronjob
{

    public function execute()
    {
        $sql = rex_sql::factory();

        try {
            $tbl = rex::getTable('pagestats_hash');
            // only execute if table exists
            $exists = $sql->getArray("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . $tbl . "' LIMIT 1");
            if (!empty($exists)) {
                $sql->setQuery("DELETE FROM " . $tbl . " WHERE datetime < CURDATE();");
            }
        } catch (rex_sql_exception $e) {
            rex_logger::logException($e);
            return false;
        }

        return true;
    }


    public function getTypeName()
    {
        return "Entferne Client-Hashes des Statistics Addons die älter sind als einen Tag";
    }
}
