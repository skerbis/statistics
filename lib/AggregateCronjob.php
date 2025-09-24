<?php

class rex_statistics_aggregate_cronjob extends rex_cronjob
{

    public function execute()
    {
        $sql = rex_sql::factory();

        try {
            // Clear and insert monthly visits (only if source table exists)
            $src = rex::getTable('pagestats_visits_per_day');
            $dst = rex::getTable('pagestats_monthly_visits');
            if ($this->tableExists($src) && $this->tableExists($dst)) {
                $sql->setQuery("TRUNCATE TABLE " . $dst);
                $sql->setQuery("INSERT INTO " . $dst . " (year, month, domain, count)
                    SELECT YEAR(`date`), MONTH(`date`), domain, SUM(`count`)
                    FROM " . $src . "
                    GROUP BY YEAR(`date`), MONTH(`date`), domain");
            }

            // Clear and insert monthly visitors (only if source table exists)
            $src = rex::getTable('pagestats_visitors_per_day');
            $dst = rex::getTable('pagestats_monthly_visitors');
            if ($this->tableExists($src) && $this->tableExists($dst)) {
                $sql->setQuery("TRUNCATE TABLE " . $dst);
                $sql->setQuery("INSERT INTO " . $dst . " (year, month, domain, count)
                    SELECT YEAR(`date`), MONTH(`date`), domain, SUM(`count`)
                    FROM " . $src . "
                    GROUP BY YEAR(`date`), MONTH(`date`), domain");
            }

            // Clear and insert yearly visits (only if source table exists)
            $src = rex::getTable('pagestats_visits_per_day');
            $dst = rex::getTable('pagestats_yearly_visits');
            if ($this->tableExists($src) && $this->tableExists($dst)) {
                $sql->setQuery("TRUNCATE TABLE " . $dst);
                $sql->setQuery("INSERT INTO " . $dst . " (year, domain, count)
                    SELECT YEAR(`date`), domain, SUM(`count`)
                    FROM " . $src . "
                    GROUP BY YEAR(`date`), domain");
            }

            // Clear and insert pages total (only if source and destination exist) - now in chunks to avoid timeouts
            $src = rex::getTable('pagestats_visits_per_url');
            $dst = rex::getTable('pagestats_pages_total');
            if ($this->tableExists($src) && $this->tableExists($dst)) {
                $sql->setQuery("TRUNCATE TABLE " . $dst);
                // reset auto increment if column exists
                $sql->setQuery("ALTER TABLE " . $dst . " AUTO_INCREMENT = 1");

                // Get total distinct URLs
                $totalUrls = $sql->getArray('SELECT COUNT(DISTINCT url) AS c FROM ' . $src)[0]['c'] ?? 0;
                $chunkSize = 1000; // Process in chunks of 1000 URLs
                $processed = 0;

                while ($processed < $totalUrls) {
                    // Get next chunk of URLs
                    $urls = $sql->getArray('SELECT DISTINCT url FROM ' . $src . ' ORDER BY url LIMIT ' . $chunkSize . ' OFFSET ' . $processed);
                    if (empty($urls)) break;

                    // Aggregate for this chunk
                    $urlList = array_map(function ($r) use ($sql) { return $sql->escape($r['url']); }, $urls);
                    $inClause = "'" . implode("','", $urlList) . "'";

                    $sql->setQuery("INSERT INTO " . $dst . " (url, total_count, last_updated)
                        SELECT url, SUM(`count`), NOW()
                        FROM " . $src . "
                        WHERE url IN (" . $inClause . ")
                        GROUP BY url");

                    $processed += count($urls);
                }
            }

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

    /**
     * Check if a table exists in current database
     *
     * @param string $table full table name (with prefix)
     * @return bool
     */
    private function tableExists(string $table): bool
    {
        try {
            $sql = rex_sql::factory();
            $res = $sql->getArray("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . $table . "' LIMIT 1");
            return !empty($res);
        } catch (rex_sql_exception $e) {
            rex_logger::logException($e);
            return false;
        }
    }
}