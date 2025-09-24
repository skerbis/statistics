<?php

use AndiLeni\Statistics\chartData;
use AndiLeni\Statistics\DateFilter;
use AndiLeni\Statistics\ListData;
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
 * API class for loading full stats HTML
 *
 */
class rex_api_stats_load_full extends rex_api_function
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

        // data for charts
        $chart_data = new chartData($filter_date_helper);

        // main chart data for visits and visitors
        $main_chart_data = $chart_data->getMainChartData();

        // heatmap data for visits per day in this year
        $data_heatmap = $chart_data->getHeatmapVisits();

        // chart data monthly
        $chart_data_monthly = $chart_data->getChartDataMonthly();

        // chart data yearly
        $chart_data_yearly = $chart_data->getChartDataYearly();

        // device specific data - lazy loaded
        $browser = new Browser();
        $browser_data = $browser->getData();

        $browsertype = new Browsertype();
        $browsertype_data = $browsertype->getData();

        $os = new OS();
        $os_data = $os->getData();

        $brand = new Brand();
        $brand_data = $brand->getData();

        $model = new Model();
        $model_data = $model->getData();

        $weekday = new Weekday();
        $weekday_data = $weekday->getData();

        $hour = new Hour();
        $hour_data = $hour->getData();

        $pagecount = new Pagecount();
        $pagecount_data = $pagecount->getChartData();

        $visitduration = new VisitDuration();
        $visitduration_data = $visitduration->getChartData();

        $lastpage = new Lastpage();
        $lastpage_data = $lastpage->getChartData();

        $country = new Country();
        $country_data = $country->getChartData();

        // FRAGMENTS FOR
        // - PANEL WITH CHART "VIEWS TOTAL"
        // - TABLE WITH DATA FOR "VIEWS TOTAL"

        $lists_data = new ListData($filter_date_helper);

        $lists_daily = $lists_data->getListsDaily();
        $lists_monthly = $lists_data->getListsMonthly();
        $lists_yearly = $lists_data->getListsYearly();

        $fragment_main_chart = new rex_fragment();
        $fragment_main_chart->setVar('daily', '<div id="chart_visits_daily" style="width: 100%;height:500px;"></div><hr><div id="chart_visits_heatmap" style="width: 100%;height:200px;"></div>' . $lists_daily->parse('collapse.php'), false);
        $fragment_main_chart->setVar('monthly', '<div id="chart_visits_monthly" style="width: 100%;height:500px;"></div>' . $lists_monthly->parse('collapse.php'), false);
        $fragment_main_chart->setVar('yearly', '<div id="chart_visits_yearly" style="width: 100%;height:500px;"></div>' . $lists_yearly->parse('collapse.php'), false);
        $main_chart_html = $fragment_main_chart->parse('main_chart.php');

        $fragment_browser = new rex_fragment();
        $fragment_browser->setVar('title', $addon->i18n('statistics_browser'));
        $fragment_browser->setVar('chart', '<div id="chart_browser" style="width: 100%;height:500px"></div>', false);
        $fragment_browser->setVar('table', '<div id="browser_table">Lade...</div>', false);
        $browser_html = $fragment_browser->parse('data_vertical.php');

        $fragment_browsertype = new rex_fragment();
        $fragment_browsertype->setVar('title', $addon->i18n('statistics_devicetype'));
        $fragment_browsertype->setVar('chart', '<div id="chart_browsertype" style="width: 100%;height:500px"></div>', false);
        $fragment_browsertype->setVar('table', $browsertype->getList(), false);
        $browsertype_html = $fragment_browsertype->parse('data_vertical.php');

        $fragment_os = new rex_fragment();
        $fragment_os->setVar('title', $addon->i18n('statistics_os'));
        $fragment_os->setVar('chart', '<div id="chart_os" style="width: 100%;height:500px"></div>', false);
        $fragment_os->setVar('table', $os->getList(), false);
        $os_html = $fragment_os->parse('data_vertical.php');

        $fragment_brand = new rex_fragment();
        $fragment_brand->setVar('title', $addon->i18n('statistics_brand'));
        $fragment_brand->setVar('chart', '<div id="chart_brand" style="width: 100%;height:500px"></div>', false);
        $fragment_brand->setVar('table', $brand->getList(), false);
        $brand_html = $fragment_brand->parse('data_vertical.php');

        $fragment_model = new rex_fragment();
        $fragment_model->setVar('title', $addon->i18n('statistics_model'));
        $fragment_model->setVar('chart', '<div id="chart_model" style="width: 100%;height:500px"></div>', false);
        $fragment_model->setVar('table', $model->getList(), false);
        $model_html = $fragment_model->parse('data_vertical.php');

        $fragment_weekday = new rex_fragment();
        $fragment_weekday->setVar('title', $addon->i18n('statistics_days'));
        $fragment_weekday->setVar('chart', '<div id="chart_weekday" style="width: 100%;height:500px"></div>', false);
        $fragment_weekday->setVar('table', $weekday->getList(), false);
        $weekday_html = $fragment_weekday->parse('data_vertical.php');

        $fragment_hour = new rex_fragment();
        $fragment_hour->setVar('title', $addon->i18n('statistics_hours'));
        $fragment_hour->setVar('chart', '<div id="chart_hour" style="width: 100%;height:500px"></div>', false);
        $fragment_hour->setVar('table', $hour->getList(), false);
        $hour_html = $fragment_hour->parse('data_vertical.php');

        $fragment_pagecount = new rex_fragment();
        $fragment_pagecount->setVar('title', "Anzahl besuchter Seiten in einer Sitzung");
        $fragment_pagecount->setVar('chart', '<div id="chart_pagecount" style="width: 100%;height:500px"></div>', false);
        $fragment_pagecount->setVar('table', $pagecount->getList(), false);
        $fragment_pagecount->setVar('modalid', "pc_modal", false);
        $fragment_pagecount->setVar('note', "<p>Zeigt an, wie viele Seiten in einer Sitzung besucht wurden.</p>", false);
        $pagecount_html = $fragment_pagecount->parse('data_vertical.php');

        $fragment_visitduration = new rex_fragment();
        $fragment_visitduration->setVar('title', "Besuchsdauer");
        $fragment_visitduration->setVar('chart', '<div id="chart_visitduration" style="width: 100%;height:500px"></div>', false);
        $fragment_visitduration->setVar('table', $visitduration->getList(), false);
        $fragment_visitduration->setVar('modalid', "bd_modal", false);
        $fragment_visitduration->setVar('note', "<p>Zeigt an, wie viel Zeit auf der Webseite verbracht wurde. Ein Wert von genau '0 Sekunden' sagt aus, dass der Besucher nur eine einzige Seite besucht hat.</p> Hinweis: <p>Die Besuchsdauer wird nur annähernd genau erfasst. D.h. konkret, die Besuchszeit der letzten vom Besucher aufgerufenen Seite kann nicht erfasst werden. Die Zeit berechnet sich somit aus der Dauer aller Aufrufe ausgenommen des letzten.</p>", false);
        $visitduration_html = $fragment_visitduration->parse('data_vertical.php');

        $fragment_lastpage = new rex_fragment();
        $fragment_lastpage->setVar('title', "Ausstiegsseiten");
        $fragment_lastpage->setVar('chart', '<div id="chart_lastpage" style="width: 100%;height:500px"></div>', false);
        $fragment_lastpage->setVar('table', $lastpage->getList(), false);
        $fragment_lastpage->setVar('modalid', "lp_modal", false);
        $fragment_lastpage->setVar('note', "<p>Zeigt an, welche URLs als letztes aufgerufen worden sind bevor die Webseite verlassen wurde.</p>", false);
        $lastpage_html = $fragment_lastpage->parse('data_vertical.php');

        $fragment_country = new rex_fragment();
        $fragment_country->setVar('title', "Länder");
        $fragment_country->setVar('chart', '<div id="chart_country" style="width: 100%;height:500px"></div>', false);
        $fragment_country->setVar('table', $country->getList(), false);
        $country_html = $fragment_country->parse('data_vertical.php');

        $list = rex_list::factory('SELECT * FROM ' . rex::getTable('pagestats_bot') . ' ORDER BY count DESC', 1000);
        $list->setColumnLabel('name', $addon->i18n('statistics_name'));
        $list->setColumnLabel('count', $addon->i18n('statistics_count'));
        $list->setColumnLabel('category', $addon->i18n('statistics_category'));
        $list->setColumnLabel('producer', $addon->i18n('statistics_producer'));
        $list->addTableAttribute('class', 'dt_bots statistics_table table-striped table-hover');

        if ($list->getRows() == 0) {
            $table = rex_view::info($addon->i18n('statistics_no_data'));
        } else {
            $table = $list->get();
        }

        $fragment_bots = new rex_fragment();
        $fragment_bots->setVar('title', 'Bots:');
        $fragment_bots->setVar('body', $table, false);
        $bots_html = $fragment_bots->parse('core/page/section.php');

        $html = $main_chart_html . $browser_html . $browsertype_html . $os_html . $brand_html . $model_html . $weekday_html . $hour_html . $pagecount_html . $visitduration_html . $lastpage_html . $country_html . $bots_html;

    $show_toolbox = rex_config::get('statistics', 'statistics_show_chart_toolbox') ? 'true' : 'false';

    $date_qs = '&date_start=' . urlencode($request_date_start) . '&date_end=' . urlencode($request_date_end);

    // Add JavaScript for charts
    $html .= '<script>
    if (rex.theme == "dark" || window.matchMedia(\'(prefers-color-scheme: dark)\').matches && rex.theme == "auto") {
        var theme = "dark";
    } else {
        var theme = "shine";
    }

    async function loadChartData(type) {
        const response = await fetch(\'index.php?rex-api-call=stats_charts&type=\' + type + \'" . $date_qs . "\');
        return await response.json();
    }

    var chart_visits_daily = echarts.init(document.getElementById(\'chart_visits_daily\'), theme);
    loadChartData(\'main\').then(data => {
        var chart_visits_daily_option = {
            title: {},
            tooltip: {
                trigger: \'axis\',
            },
            dataZoom: [{
                id: \'dataZoomX\',
                type: \'slider\',
                xAxisIndex: [0],
                filterMode: \'filter\'
            }],
            grid: {
                left: \'5%\',
                right: \'5%\',
            },
            toolbox: {
                show: ' . $show_toolbox . ',
                orient: \'vertical\',
                top: \'10%\',
                feature: {
                    dataZoom: {
                        yAxisIndex: "none"
                    },
                    dataView: {
                        readOnly: false
                    },
                    magicType: {
                        type: ["line", "bar", \'stack\']
                    },
                    restore: {},
                    saveAsImage: {}
                }
            },
            legend: {
                data: data.legend,
                type: \'scroll\',
                right: \'5%\',
                align: \'left\',
            },
            xAxis: {
                data: data.xaxis,
                type: \'category\',
            },
            yAxis: {},
            series: data.series
        };
        chart_visits_daily.setOption(chart_visits_daily_option);
    });

    var chart_visits_monthly = echarts.init(document.getElementById(\'chart_visits_monthly\'), theme);
    var monthlyLoaded = false;
    function loadMonthly() {
        if (!monthlyLoaded) {
            loadChartData(\'monthly\').then(data => {
                var chart_visits_monthly_option = {
                    title: {},
                    tooltip: {
                        trigger: \'axis\',
                    },
                    dataZoom: [{
                        id: \'dataZoomX\',
                        type: \'slider\',
                        xAxisIndex: [0],
                        filterMode: \'filter\'
                    }],
                    grid: {
                        left: \'5%\',
                        right: \'5%\',
                    },
                    toolbox: {
                        show: ' . $show_toolbox . ',
                        orient: \'vertical\',
                        top: \'10%\',
                        feature: {
                            dataZoom: {
                                yAxisIndex: "none"
                            },
                            dataView: {
                                readOnly: false
                            },
                            magicType: {
                                type: ["line", "bar", \'stack\']
                            },
                            restore: {},
                            saveAsImage: {}
                        }
                    },
                    legend: {
                        data: data.legend,
                        right: \'5%\',
                        type: \'scroll\',
                    },
                    xAxis: {
                        data: data.xaxis,
                        type: \'category\',
                    },
                    yAxis: {},
                    series: data.series
                };
                chart_visits_monthly.setOption(chart_visits_monthly_option);
                monthlyLoaded = true;
            });
        }
    }

    var chart_visits_yearly = echarts.init(document.getElementById(\'chart_visits_yearly\'), theme);
    var yearlyLoaded = false;
    function loadYearly() {
        if (!yearlyLoaded) {
            loadChartData(\'yearly\').then(data => {
                var chart_visits_yearly_option = {
                    title: {},
                    tooltip: {
                        trigger: \'axis\',
                    },
                    dataZoom: [{
                        id: \'dataZoomX\',
                        type: \'slider\',
                        xAxisIndex: [0],
                        filterMode: \'filter\'
                    }],
                    grid: {
                        left: \'5%\',
                        right: \'5%\',
                    },
                    toolbox: {
                        show: ' . $show_toolbox . ',
                        orient: \'vertical\',
                        top: \'10%\',
                        feature: {
                            dataZoom: {
                                yAxisIndex: "none"
                            },
                            dataView: {
                                readOnly: false
                            },
                            magicType: {
                                type: ["line", "bar", \'stack\']
                            },
                            restore: {},
                            saveAsImage: {}
                        }
                    },
                    legend: {
                        data: data.legend,
                        right: \'5%\',
                        type: \'scroll\',
                    },
                    xAxis: {
                        data: data.xaxis,
                        type: \'category\',
                    },
                    yAxis: {},
                    series: data.series
                };
                chart_visits_yearly.setOption(chart_visits_yearly_option);
                yearlyLoaded = true;
            });
        }
    }

    var visits_heatmap = echarts.init(document.getElementById(\'chart_visits_heatmap\'), theme);
    loadChartData(\'heatmap\').then(data => {
        var option_heatmap = {
            title: {},
            tooltip: {
                show: true,
                formatter: function(p) {
                    var format = echarts.format.formatTime(\'dd.MM.yyyy\', p.data[0]);
                    return format + \'<br><b>\' + p.data[1] + \' Aufrufe</b>\';
                }
            },
            toolbox: {
                show: ' . $show_toolbox . ',
                orient: \'vertical\',
                top: \'10%\',
                feature: {
                    dataView: {
                        readOnly: false
                    },
                    restore: {},
                    saveAsImage: {}
                }
            },
            calendar: {
                top: \'90\',
                left: \'5%\',
                right: \'5%\',
                cellSize: [\'auto\', 15],
                range: ' . date('Y') . ',
                itemStyle: {
                    borderWidth: 0.5
                },
                yearLabel: {
                    show: false
                },
                monthLabel: {
                    nameMap: [
                        \'Jan\', \'Feb\', \'Mar\', \'Apr\', \'Mai\', \'Jun\',
                        \'Jul\', \'Aug\', \'Sep\', \'Okt\', \'Nov\', \'Dez\'
                    ],
                },
                dayLabel: {
                    nameMap: [
                        \'So\', \'Mo\', \'Di\', \'Mi\', \'Do\', \'Fr\', \'Sa\'
                    ]
                }
            },
            series: {
                data: data.data,
                type: \'heatmap\',
                coordinateSystem: \'calendar\',
            },
            visualMap: {
                type: \'continuous\',
                itemWidth: 20,
                itemHeight: 250,
                min: 0,
                max: data.max,
                calculable: true,
                orient: \'horizontal\',
                left: \'center\',
                top: \'top\'
            },
        };
        visits_heatmap.setOption(option_heatmap);
    });

    var chart_browser = echarts.init(document.getElementById(\'chart_browser\'), theme);
    loadChartData(\'browser\').then(data => {
        var chart_browser_option = {
            title: {},
            tooltip: {
                trigger: \'item\',
                formatter: "{b}: <b>{c}</b> ({d}%)"
            },
            legend: {
                show: false,
                orient: \'vertical\',
                left: \'left\',
            },
            toolbox: {
                show: ' . $show_toolbox . ',
                orient: \'vertical\',
                top: \'10%\',
                feature: {
                    dataView: {
                        readOnly: false
                    },
                    saveAsImage: {}
                }
            },
            series: [{
                type: \'pie\',
                radius: \'85%\',
                data: data,
                labelLine: {
                    show: false
                },
                label: {
                    show: true,
                    position: \'inside\',
                    formatter: \'{b}: {c} \\n ({d}%)\',
                },
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: \'rgba(0, 0, 0, 0.5)\'
                    }
                }
            }]
        };
        chart_browser.setOption(chart_browser_option);
    });

    var chart_browsertype = echarts.init(document.getElementById(\'chart_browsertype\'), theme);
    loadChartData(\'browsertype\').then(data => {
        var chart_browsertype_option = {
            title: {},
            tooltip: {
                trigger: \'item\',
                formatter: "{b}: <b>{c}</b> ({d}%)"
            },
            legend: {
                show: false,
                orient: \'vertical\',
                left: \'left\',
            },
            toolbox: {
                show: ' . $show_toolbox . ',
                orient: \'vertical\',
                top: \'10%\',
                feature: {
                    dataView: {
                        readOnly: false
                    },
                    saveAsImage: {}
                }
            },
            series: [{
                type: \'pie\',
                radius: \'85%\',
                data: data,
                labelLine: {
                    show: false
                },
                label: {
                    show: true,
                    position: \'inside\',
                    formatter: \'{b}: {c} \\n ({d}%)\',
                },
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: \'rgba(0, 0, 0, 0.5)\'
                    }
                }
            }]
        };
        chart_browsertype.setOption(chart_browsertype_option);
    });

    var chart_os = echarts.init(document.getElementById(\'chart_os\'), theme);
    loadChartData(\'os\').then(data => {
        var chart_os_option = {
            title: {},
            tooltip: {
                trigger: \'item\',
                formatter: "{b}: <b>{c}</b> ({d}%)"
            },
            legend: {
                show: false,
                orient: \'vertical\',
                left: \'left\',
            },
            toolbox: {
                show: ' . $show_toolbox . ',
                orient: \'vertical\',
                top: \'10%\',
                feature: {
                    dataView: {
                        readOnly: false
                    },
                    saveAsImage: {}
                }
            },
            series: [{
                type: \'pie\',
                radius: \'85%\',
                data: data,
                labelLine: {
                    show: false
                },
                label: {
                    show: true,
                    position: \'inside\',
                    formatter: \'{b}: {c} \\n ({d}%)\',
                },
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: \'rgba(0, 0, 0, 0.5)\'
                    }
                }
            }]
        };
        chart_os.setOption(chart_os_option);
    });

    var chart_brand = echarts.init(document.getElementById(\'chart_brand\'), theme);
    loadChartData(\'brand\').then(data => {
        var chart_brand_option = {
            title: {},
            tooltip: {
                trigger: \'item\',
                formatter: "{b}: <b>{c}</b> ({d}%)"
            },
            legend: {
                show: false,
                orient: \'vertical\',
                left: \'left\',
            },
            toolbox: {
                show: ' . $show_toolbox . ',
                orient: \'vertical\',
                top: \'10%\',
                feature: {
                    dataView: {
                        readOnly: false
                    },
                    saveAsImage: {}
                }
            },
            series: [{
                type: \'pie\',
                radius: \'85%\',
                data: data,
                labelLine: {
                    show: false
                },
                label: {
                    show: true,
                    position: \'inside\',
                    formatter: \'{b}: {c} \\n ({d}%)\',
                },
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: \'rgba(0, 0, 0, 0.5)\'
                    }
                }
            }]
        };
        chart_brand.setOption(chart_brand_option);
    });

    var chart_model = echarts.init(document.getElementById(\'chart_model\'), theme);
    loadChartData(\'model\').then(data => {
        var chart_model_option = {
            title: {},
            tooltip: {
                trigger: \'item\',
                formatter: "{b}: <b>{c}</b> ({d}%)"
            },
            legend: {
                show: false,
                orient: \'vertical\',
                left: \'left\',
            },
            toolbox: {
                show: ' . $show_toolbox . ',
                orient: \'vertical\',
                top: \'10%\',
                feature: {
                    dataView: {
                        readOnly: false
                    },
                    saveAsImage: {}
                }
            },
            series: [{
                type: \'pie\',
                radius: \'85%\',
                data: data,
                labelLine: {
                    show: false
                },
                label: {
                    show: true,
                    position: \'inside\',
                    formatter: \'{b}: {c} \\n ({d}%)\',
                },
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: \'rgba(0, 0, 0, 0.5)\'
                    }
                }
            }]
        };
        chart_model.setOption(chart_model_option);
    });

    var chart_weekday = echarts.init(document.getElementById(\'chart_weekday\'), theme);
    loadChartData(\'weekday\').then(data => {
        var chart_weekday_option = {
            title: {},
            tooltip: {
                trigger: \'axis\',
                formatter: "{b}: <b>{c}</b>"
            },
            axisPointer: {
                type: \'shadow\'
            },
            grid: {
                containLabel: true,
                left: \'3%\',
                right: \'3%\',
                bottom: \'3%\',
            },
            xAxis: [{
                type: \'category\',
                data: [\'Mo\', \'Di\', \'Mi\', \'Do\', \'Fr\', \'Sa\', \'So\'],
                axisTick: {
                    alignWithLabel: true
                }
            }],
            yAxis: [{
                type: \'value\'
            }],
            toolbox: {
                show: ' . $show_toolbox . ',
                orient: \'vertical\',
                top: \'10%\',
                feature: {
                    dataZoom: {
                        yAxisIndex: "none"
                    },
                    dataView: {
                        readOnly: false
                    },
                    magicType: {
                        type: ["line", "bar"]
                    },
                    restore: {},
                    saveAsImage: {}
                }
            },
            series: [{
                type: \'bar\',
                data: data,
                label: {
                    show: false,
                },
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: \'rgba(0, 0, 0, 0.5)\'
                    }
                }
            }]
        };
        chart_weekday.setOption(chart_weekday_option);
    });

    var chart_hour = echarts.init(document.getElementById(\'chart_hour\'), theme);
    loadChartData(\'hour\').then(data => {
        var chart_hour_option = {
            title: {},
            tooltip: {
                trigger: \'axis\',
                formatter: "{b} Uhr: <b>{c}</b>"
            },
            axisPointer: {
                type: \'shadow\'
            },
            grid: {
                containLabel: true,
                left: \'3%\',
                right: \'3%\',
                bottom: \'3%\',
            },
            xAxis: [{
                type: \'category\',
                data: [\'00\', \'01\', \'02\', \'03\', \'04\', \'05\', \'06\', \'07\', \'08\', \'09\', \'10\', \'11\', \'12\', \'13\', \'14\', \'15\', \'16\', \'17\', \'18\', \'19\', \'20\', \'21\', \'22\', \'23\'],
                axisTick: {
                    alignWithLabel: true
                }
            }],
            yAxis: [{
                type: \'value\'
            }],
            toolbox: {
                show: ' . $show_toolbox . ',
                orient: \'vertical\',
                top: \'10%\',
                feature: {
                    dataZoom: {
                        yAxisIndex: "none"
                    },
                    dataView: {
                        readOnly: false
                    },
                    magicType: {
                        type: ["line", "bar"]
                    },
                    restore: {},
                    saveAsImage: {}
                }
            },
            series: [{
                type: \'bar\',
                data: data,
                label: {
                    show: false,
                },
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: \'rgba(0, 0, 0, 0.5)\'
                    }
                }
            }]
        };
        chart_hour.setOption(chart_hour_option);
    });

    var chart_pagecount = echarts.init(document.getElementById(\'chart_pagecount\'), theme);
    loadChartData(\'pagecount\').then(data => {
        var chart_pagecount_option = {
            title: {},
            tooltip: {
                trigger: \'axis\',
                formatter: "{b} Seiten besucht: <b>{c} mal</b>"
            },
            axisPointer: {
                type: \'shadow\'
            },
            grid: {
                containLabel: true,
                left: \'3%\',
                right: \'3%\',
                bottom: \'3%\',
            },
            xAxis: [{
                type: \'category\',
                data: data.labels,
                axisTick: {
                    alignWithLabel: true
                }
            }],
            yAxis: [{
                type: \'value\'
            }],
            toolbox: {
                show: ' . $show_toolbox . ',
                orient: \'vertical\',
                top: \'10%\',
                feature: {
                    dataZoom: {
                        yAxisIndex: "none"
                    },
                    dataView: {
                        readOnly: false
                    },
                    magicType: {
                        type: ["line", "bar"]
                    },
                    restore: {},
                    saveAsImage: {}
                }
            },
            series: [{
                type: \'bar\',
                data: data.values,
                label: {
                    show: false,
                },
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: \'rgba(0, 0, 0, 0.5)\'
                    }
                }
            }]
        };
        chart_pagecount.setOption(chart_pagecount_option);
    });

    var chart_visitduration = echarts.init(document.getElementById(\'chart_visitduration\'), theme);
    loadChartData(\'visitduration\').then(data => {
        var chart_visitduration_option = {
            title: {},
            tooltip: {
                trigger: \'axis\',
                formatter: "{b} <br> <b>{c} mal</b>"
            },
            axisPointer: {
                type: \'shadow\'
            },
            grid: {
                containLabel: true,
                left: \'3%\',
                right: \'3%\',
                bottom: \'3%\',
            },
            xAxis: [{
                type: \'category\',
                data: data.labels,
                axisTick: {
                    alignWithLabel: true
                }
            }],
            yAxis: [{
                type: \'value\'
            }],
            toolbox: {
                show: ' . $show_toolbox . ',
                orient: \'vertical\',
                top: \'10%\',
                feature: {
                    dataZoom: {
                        yAxisIndex: "none"
                    },
                    dataView: {
                        readOnly: false
                    },
                    magicType: {
                        type: ["line", "bar"]
                    },
                    restore: {},
                    saveAsImage: {}
                }
            },
            series: [{
                type: \'bar\',
                data: data.values,
                label: {
                    show: false,
                },
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: \'rgba(0, 0, 0, 0.5)\'
                    }
                }
            }]
        };
        chart_visitduration.setOption(chart_visitduration_option);
    });

    var chart_lastpage = echarts.init(document.getElementById(\'chart_lastpage\'), theme);
    loadChartData(\'lastpage\').then(data => {
        var chart_lastpage_option = {
            title: {},
            tooltip: {
                trigger: \'axis\',
                formatter: "{b} <br> Anzahl: <b>{c}</b>"
            },
            axisPointer: {
                type: \'shadow\'
            },
            grid: {
                containLabel: true,
                left: \'3%\',
                right: \'3%\',
                bottom: \'3%\',
            },
            xAxis: [{
                type: \'category\',
                data: data.labels,
                axisTick: {
                    alignWithLabel: true
                }
            }],
            yAxis: [{
                type: \'value\'
            }],
            toolbox: {
                show: ' . $show_toolbox . ',
                orient: \'vertical\',
                top: \'10%\',
                feature: {
                    dataZoom: {
                        yAxisIndex: "none"
                    },
                    dataView: {
                        readOnly: false
                    },
                    magicType: {
                        type: ["line", "bar"]
                    },
                    restore: {},
                    saveAsImage: {}
                }
            },
            series: [{
                type: \'bar\',
                data: data.values,
                label: {
                    show: false,
                },
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: \'rgba(0, 0, 0, 0.5)\'
                    }
                }
            }]
        };
        chart_lastpage.setOption(chart_lastpage_option);
    });

    var chart_country = echarts.init(document.getElementById(\'chart_country\'), theme);
    loadChartData(\'country\').then(data => {
        var chart_country_option = {
            title: {},
            tooltip: {
                trigger: \'axis\',
                formatter: "{b} <br> Anzahl: <b>{c}</b>"
            },
            axisPointer: {
                type: \'shadow\'
            },
            grid: {
                containLabel: true,
                left: \'3%\',
                right: \'3%\',
                bottom: \'3%\',
            },
            xAxis: [{
                type: \'category\',
                data: data.labels,
                axisTick: {
                    alignWithLabel: true
                }
            }],
            yAxis: [{
                type: \'value\'
            }],
            toolbox: {
                show: ' . $show_toolbox . ',
                orient: \'vertical\',
                top: \'10%\',
                feature: {
                    dataZoom: {
                        yAxisIndex: "none"
                    },
                    dataView: {
                        readOnly: false
                    },
                    magicType: {
                        type: ["line", "bar"]
                    },
                    restore: {},
                    saveAsImage: {}
                }
            },
            series: [{
                type: \'bar\',
                data: data.values,
                label: {
                    show: false,
                },
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: \'rgba(0, 0, 0, 0.5)\'
                    }
                }
            }]
        };
        chart_country.setOption(chart_country_option);
    });

    $(\'a[data-toggle="tab"]\').on(\'shown.bs.tab\', function(e) {
        chart_visits_daily.resize();
        chart_visits_monthly.resize();
        chart_visits_yearly.resize();
        loadMonthly();
        loadYearly();
    })

    $(document).on(\'rex:ready\', function() {
        $(\'.dt_order_second\').DataTable({
            "paging": true,
            "pageLength": 10,
            "lengthChange": true,
            "lengthMenu": [5, 10, 50, 100],
            "order": [
                [1, "desc"]
            ],
            "search": {
                "caseInsensitive": true
            }
        });

        $(\'.dt_order_first\').DataTable({
            "paging": true,
            "pageLength": 10,
            "lengthChange": true,
            "lengthMenu": [5, 10, 50, 100],
            "order": [
                [0, "desc"]
            ],
            "search": {
                "caseInsensitive": true
            }
        });

        $(\'.dt_order_default\').DataTable({
            "paging": true,
            "pageLength": 10,
            "lengthChange": true,
            "lengthMenu": [5, 10, 50, 100],
            "search": {
                "caseInsensitive": true
            }
        });

        $(\'.dt_bots\').DataTable({
            "paging": true,
            "pageLength": 10,
            "lengthChange": true,
            "lengthMenu": [5, 10, 50, 100],
            "search": {
                "caseInsensitive": true
            },
            "order": [
                [3, "desc"]
            ]
        });
    });
</script>';

        return new rex_api_result(true, $html);
    }
}