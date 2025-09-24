<?php

namespace AndiLeni\Statistics;

use rex;
use rex_addon;
use rex_list;
use rex_sql;
use rex_view;

class Country
{


    public function getChartData()
    {
        $sql = rex_sql::factory();
        $res = $sql->getArray('SELECT name, count FROM ' . rex::getTable('pagestats_data') . ' where type = "country" ORDER BY count DESC');

            $items = [];
            foreach ($res as $row) {
                $items[] = [
                    'name' => $row['name'],
                    'value' => (int) $row['count']
                ];
            }

            $top = 15;
            if (count($items) <= $top) return $items;

            $topItems = array_slice($items, 0, $top);
            $other = array_slice($items, $top);
            $otherSum = 0;
            foreach ($other as $o) $otherSum += $o['value'];
            $topItems[] = ['name' => 'Andere', 'value' => $otherSum];
            return $topItems;
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

    $list = rex_list::factory('SELECT name, count FROM ' . rex::getTable('pagestats_data') . ' where type = "country" ORDER BY count DESC LIMIT 40', 10000);

    $list->setColumnLabel('name', $addon->i18n('statistics_name'));
    $list->setColumnLabel('count', $addon->i18n('statistics_count'));

        $list->addTableAttribute('class', 'dt_order_second statistics_table');

        if ($list->getRows() == 0) {
            $table = rex_view::info($addon->i18n('statistics_no_data'));
        } else {
            $table = $list->get();
        }

        return $table;
    }
}
