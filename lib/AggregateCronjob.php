<?php

class rex_statistics_aggregate_cronjob extends rex_cronjob
{

    public function execute()
    {
        $sql = rex_sql::factory();

        try {
            // Clear monthly visits
            $sql->setQuery("TRUNCATE TABLE " . rex::getTable("pagestats_monthly_visits"));
            // Insert aggregated monthly visits
            $sql->setQuery("INSERT INTO " . rex::getTable("pagestats_monthly_visits") . " (year, month, domain, count)
                SELECT YEAR(date), MONTH(date), domain, SUM(count)
                FROM " . rex::getTable("pagestats_visits_per_day") . "
                GROUP BY YEAR(date), MONTH(date), domain");

            // Clear monthly visitors
            $sql->setQuery("TRUNCATE TABLE " . rex::getTable("pagestats_monthly_visitors"));
            // Insert aggregated monthly visitors
            $sql->setQuery("INSERT INTO " . rex::getTable("pagestats_monthly_visitors") . " (year, month, domain, count)
                SELECT YEAR(date), MONTH(date), domain, SUM(count)
                FROM " . rex::getTable("pagestats_visitors_per_day") . "
                GROUP BY YEAR(date), MONTH(date), domain");

            // Clear yearly visits
            $sql->setQuery("TRUNCATE TABLE " . rex::getTable("pagestats_yearly_visits"));
            // Insert aggregated yearly visits
            $sql->setQuery("INSERT INTO " . rex::getTable("pagestats_yearly_visits") . " (year, domain, count)
                SELECT YEAR(date), domain, SUM(count)
                FROM " . rex::getTable("pagestats_visits_per_day") . "
                GROUP BY YEAR(date), domain");

            // Clear pages total
            $sql->setQuery("TRUNCATE TABLE " . rex::getTable("pagestats_pages_total"));
            $sql->setQuery("ALTER TABLE " . rex::getTable("pagestats_pages_total") . " AUTO_INCREMENT = 1");
            // Insert aggregated pages total
            $sql->setQuery("INSERT IGNORE INTO " . rex::getTable("pagestats_pages_total") . " (url, total_count, last_updated)
                SELECT url, SUM(count), NOW()
                FROM " . rex::getTable("pagestats_visits_per_url") . "
                GROUP BY url
                ORDER BY SUM(count) DESC");

        } catch (rex_sql_exception $e) {
            rex_logger::logException($e);
            return false;
        }

        return true;
    }


    public function getTypeName()
    {
        return "Aggregiere Statistiken für bessere Performance";
    }
}