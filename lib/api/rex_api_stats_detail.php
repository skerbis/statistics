<?php

use AndiLeni\Statistics\chartData;
use AndiLeni\Statistics\DateFilter;
use AndiLeni\Statistics\Brand;
use AndiLeni\Statistics\Browser;
use AndiLeni\Statistics\Browsertype;
use AndiLeni\Statistics\Country;
use AndiLeni\Statistics\Hour;
use AndiLeni\Statistics\Lastpage;
use AndiLeni\Statistics\Model;
use AndiLeni\Statistics\OS;
use AndiLeni\Statistics\Pagecount;
use AndiLeni\Statistics\Weekday;
use AndiLeni\Statistics\VisitDuration;

/**
 * API class for loading specific detail views
 */
class rex_api_stats_detail extends rex_api_function
{
    protected $published = true;

    /**
     * Execute the API call
     */
    public function execute(): rex_api_result
    {
        $addon = rex_addon::get('statistics');

        $type = rex_request('type', 'string', '');
        $request_date_start = htmlspecialchars_decode(rex_request('date_start', 'string', ''));
        $request_date_end = htmlspecialchars_decode(rex_request('date_end', 'string', ''));

        $filter_date_helper = new DateFilter($request_date_start, $request_date_end, 'pagestats_visits_per_day');

        $html = '';

        switch ($type) {
            case 'browsertype':
                $browsertype = new Browsertype();
                $browsertype_data = $browsertype->getData();

                $fragment_browsertype = new rex_fragment();
                $fragment_browsertype->setVar('title', $addon->i18n('statistics_devicetype'));
                $fragment_browsertype->setVar('chart', '<div id="chart_browsertype_detail" style="width: 100%;height:500px"></div>', false);
                $fragment_browsertype->setVar('table', $browsertype->getList(), false);
                $html = $fragment_browsertype->parse('data_vertical.php');
                break;

            case 'browser':
                $browser = new Browser();
                $browser_data = $browser->getData();

                $fragment_browser = new rex_fragment();
                $fragment_browser->setVar('title', $addon->i18n('statistics_browser'));
                $fragment_browser->setVar('chart', '<div id="chart_browser_detail" style="width: 100%;height:500px"></div>', false);
                $fragment_browser->setVar('table', '<div id="browser_table_detail">Lade...</div>', false);
                $html = $fragment_browser->parse('data_vertical.php');
                break;

            case 'os':
                $os = new OS();
                $os_data = $os->getData();

                $fragment_os = new rex_fragment();
                $fragment_os->setVar('title', $addon->i18n('statistics_os'));
                $fragment_os->setVar('chart', '<div id="chart_os_detail" style="width: 100%;height:500px"></div>', false);
                $fragment_os->setVar('table', $os->getList(), false);
                $html = $fragment_os->parse('data_vertical.php');
                break;

            case 'brand':
                $brand = new Brand();
                $brand_data = $brand->getData();

                $fragment_brand = new rex_fragment();
                $fragment_brand->setVar('title', $addon->i18n('statistics_brand'));
                $fragment_brand->setVar('chart', '<div id="chart_brand_detail" style="width: 100%;height:500px"></div>', false);
                $fragment_brand->setVar('table', $brand->getList(), false);
                $html = $fragment_brand->parse('data_vertical.php');
                break;

            case 'weekday':
                $weekday = new Weekday();
                $weekday_data = $weekday->getData();

                $fragment_weekday = new rex_fragment();
                $fragment_weekday->setVar('title', $addon->i18n('statistics_days'));
                $fragment_weekday->setVar('chart', '<div id="chart_weekday_detail" style="width: 100%;height:500px"></div>', false);
                $fragment_weekday->setVar('table', $weekday->getList(), false);
                $html = $fragment_weekday->parse('data_vertical.php');
                break;

            case 'hour':
                $hour = new Hour();

                $fragment_hour = new rex_fragment();
                $fragment_hour->setVar('title', $addon->i18n('statistics_hours'));
                $fragment_hour->setVar('chart', '', false);
                $fragment_hour->setVar('table', $hour->getList(), false);
                $html = $fragment_hour->parse('data_vertical.php');
                break;

            case 'country':
                $country = new Country();

                $fragment_country = new rex_fragment();
                $fragment_country->setVar('title', $addon->i18n('statistics_country'));
                $fragment_country->setVar('chart', '', false);
                $fragment_country->setVar('table', $country->getList(), false);
                $html = $fragment_country->parse('data_vertical.php');
                break;

            case 'visitduration':
                $visitduration = new VisitDuration();
                $visitduration_data = $visitduration->getChartData();

                $fragment_visitduration = new rex_fragment();
                $fragment_visitduration->setVar('title', "Besuchsdauer");
                $fragment_visitduration->setVar('chart', '<div id="chart_visitduration_detail" style="width: 100%;height:500px"></div>', false);
                $fragment_visitduration->setVar('table', $visitduration->getList(), false);
                $fragment_visitduration->setVar('modalid', "bd_modal", false);
                $fragment_visitduration->setVar('note', "<p>Zeigt an, wie viel Zeit auf der Webseite verbracht wurde. Ein Wert von genau '0 Sekunden' sagt aus, dass der Besucher nur eine einzige Seite besucht hat.</p> Hinweis: <p>Die Besuchsdauer wird nur annähernd genau erfasst. D.h. konkret, die Besuchszeit der letzten vom Besucher aufgerufenen Seite kann nicht erfasst werden. Die Zeit berechnet sich somit aus der Dauer aller Aufrufe ausgenommen des letzten.</p>", false);
                $html = $fragment_visitduration->parse('data_vertical.php');
                break;

            default:
                $html = '<p>Unbekannter Detail-Typ.</p>';
        }

    // No client-side JS appended here — client will initialize charts after inserting the fragment.

        rex_response::cleanOutputBuffers();
        rex_response::setStatus(rex_response::HTTP_OK);
        rex_response::sendContent($html);
        exit;
    }
}