<?php

namespace AndiLeni\Statistics;

use rex;
use rex_addon;
use rex_list;
use rex_sql;
use rex_view;
use InvalidArgumentException;
use rex_sql_exception;
use rex_exception;

/**
 * Handles the "hour" data for statistics
 *
 */
class Hour
{

    /**
     * 
     * 
     * @return rex_sql 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    private function getSql(): rex_sql
    {
        $sql = rex_sql::factory();

        $result = $sql->setQuery('SELECT name, count FROM ' . rex::getTable('pagestats_data') . ' WHERE type = "hour" ORDER BY count DESC');

        return $result;
    }


    /**
     * 
     * 
     * @return array 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    public function getData(): array
    {
        $sql = $this->getSql();

        $counts = array_fill(0, 24, 0);
        foreach ($sql as $row) {
            $h = intval($row->getValue('name'));
            if ($h >=0 && $h <= 23) $counts[$h] = (int)$row->getValue('count');
        }

        $labels = [];
        for ($i = 0; $i < 24; $i++) {
            $labels[] = sprintf('%02d:00', $i);
        }

        return [
            'labels' => $labels,
            'values' => $counts,
        ];
    }


    /**
     * 
     * 
     * @return string 
     * @throws InvalidArgumentException 
     * @throws rex_exception 
     */
    public function getList(): string
    {
        $addon = rex_addon::get('statistics');

        $list = rex_list::factory('SELECT name, count FROM ' . rex::getTable('pagestats_data') . ' where type = "hour" ORDER BY count DESC', 10000);

        $list->setColumnLabel('name', $addon->i18n('statistics_name'));
        $list->setColumnLabel('count', $addon->i18n('statistics_count'));
        $list->setColumnFormat('hour', 'custom',  function ($params) {

            $hour = $params['value'];
            if (strlen($hour) == 1) {
                return '0' . $hour . ' Uhr';
            } else {
                return $hour . ' Uhr';
            }
        });
        $list->addTableAttribute('class', 'dt_order_second statistics_table');

        if ($list->getRows() == 0) {
            $table = rex_view::info($addon->i18n('statistics_no_data'));
        } else {
            $table = $list->get();
        }

        return $table;
    }
}
