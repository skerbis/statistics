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
 * Handles the "brand" data for statistics
 *
 */
class Brand
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

        $result = $sql->setQuery('SELECT name, count FROM ' . rex::getTable('pagestats_data') . ' WHERE type = "brand" ORDER BY count DESC');

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

        $items = [];
        foreach ($sql as $row) {
            $items[] = [
                'name' => $row->getValue('name'),
                'value' => (int) $row->getValue('count')
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

        $list = rex_list::factory('SELECT name, count FROM ' . rex::getTable('pagestats_data') . ' where type = "brand" ORDER BY count DESC', 10000);

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
