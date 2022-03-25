<?php

class StatsOverview
{

    private $filter_date_helper;

    public function __construct($filter_date_helper)
    {
        $this->filter_date_helper = clone $filter_date_helper;
    }

    public function get_overview_data()
    {

        $sql = rex_sql::factory();

        $visits_total = $sql->setQuery('SELECT ifnull(sum(count),0) as "count" from ' . rex::getTable('pagestats_visits_per_day'));
        $visits_total = $visits_total->getValue('count');

        $visits_today = $sql->setQuery('SELECT ifnull(sum(count),0) as "count" from ' . rex::getTable('pagestats_visits_per_day') . ' where date = :date', ['date' => date('Y-m-d')]);
        $visits_today = $visits_today->getValue('count');

        $visitors_total = $sql->setQuery('SELECT ifnull(sum(count),0) as "count" from ' . rex::getTable('pagestats_visitors_per_day'));
        $visitors_total = $visitors_total->getValue('count');

        $visitors_today = $sql->setQuery('SELECT ifnull(sum(count),0) as "count" from ' . rex::getTable('pagestats_visitors_per_day') . ' where date = :date', ['date' => date('Y-m-d')]);
        $visitors_today = $visitors_today->getValue('count');


        $visits_datefilter = $sql->setQuery('SELECT ifnull(sum(count),0) as "count" from ' . rex::getTable('pagestats_visits_per_day') . ' where date between :start and :end', ['start' => $this->filter_date_helper->date_start->format('Y-m-d'), ':end' => $this->filter_date_helper->date_end->format('Y-m-d')]);
        $visits_datefilter = $visits_datefilter->getValue('count');

        $visitors_datefilter = $sql->setQuery('SELECT ifnull(sum(count),0) as "count" from ' . rex::getTable('pagestats_visitors_per_day') . ' where date between :start and :end', ['start' => $this->filter_date_helper->date_start->format('Y-m-d'), ':end' => $this->filter_date_helper->date_end->format('Y-m-d')]);
        $visitors_datefilter = $visitors_datefilter->getValue('count');

        return [
            'visits_datefilter' => $visits_datefilter,
            'visitors_datefilter' => $visitors_datefilter,
            'visits_today' => $visits_today,
            'visitors_today' => $visitors_today,
            'visits_total' => $visits_total,
            'visitors_total' => $visitors_total,
        ];
    }
}
