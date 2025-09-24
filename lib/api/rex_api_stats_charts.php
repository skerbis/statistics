<?php

use AndiLeni\Statistics\chartData;
use AndiLeni\Statistics\DateFilter;

/**
 * API class for lazy loading chart data
 *
 */
class rex_api_stats_charts extends rex_api_function
{

    protected $published = true;

    /**
     * Execute the API call
     *
     * @return rex_api_result
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     */
    public function execute(): rex_api_result
    {
        $addon = rex_addon::get('statistics');

        $request_date_start = htmlspecialchars_decode(rex_request('date_start', 'string', ''));
        $request_date_end = htmlspecialchars_decode(rex_request('date_end', 'string', ''));

        $sql = rex_sql::factory();
        $filter_date_helper = new DateFilter($request_date_start, $request_date_end, 'pagestats_visits_per_day');

        $chart_data = new chartData($filter_date_helper);

        $type = rex_request('type', 'string', 'main');

        $data = [];
        switch ($type) {
            case 'main':
                $data = $chart_data->getMainChartData();
                break;
            case 'monthly':
                $data = $chart_data->getChartDataMonthly();
                break;
            case 'yearly':
                $data = $chart_data->getChartDataYearly();
                break;
            case 'heatmap':
                $data = $chart_data->getHeatmapVisits();
                break;
            case 'browser':
                $browser = new \AndiLeni\Statistics\Browser();
                $data = $browser->getData();
                break;
            case 'browsertype':
                $browsertype = new \AndiLeni\Statistics\Browsertype();
                $data = $browsertype->getData();
                break;
            case 'os':
                $os = new \AndiLeni\Statistics\OS();
                $data = $os->getData();
                break;
            case 'brand':
                $brand = new \AndiLeni\Statistics\Brand();
                $data = $brand->getData();
                break;
            case 'model':
                $model = new \AndiLeni\Statistics\Model();
                $data = $model->getData();
                break;
            case 'weekday':
                $weekday = new \AndiLeni\Statistics\Weekday();
                $data = $weekday->getData();
                break;
            case 'hour':
                $hour = new \AndiLeni\Statistics\Hour();
                $data = $hour->getData();
                break;
            case 'pagecount':
                $pagecount = new \AndiLeni\Statistics\Pagecount();
                $data = $pagecount->getChartData();
                break;
            case 'visitduration':
                $visitduration = new \AndiLeni\Statistics\VisitDuration();
                $data = $visitduration->getChartData();
                break;
            case 'lastpage':
                $lastpage = new \AndiLeni\Statistics\Lastpage();
                $data = $lastpage->getChartData();
                break;
            case 'country':
                $country = new \AndiLeni\Statistics\Country();
                $data = $country->getChartData();
                break;
        }

        return new rex_api_result(true, $data);
    }
}