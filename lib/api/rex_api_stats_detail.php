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
                $hour_data = $hour->getData();

                $fragment_hour = new rex_fragment();
                $fragment_hour->setVar('title', $addon->i18n('statistics_hours'));
                $fragment_hour->setVar('chart', '<div id="chart_hour_detail" style="width: 100%;height:500px"></div>', false);
                $fragment_hour->setVar('table', $hour->getList(), false);
                $html = $fragment_hour->parse('data_vertical.php');
                break;

            case 'country':
                $country = new Country();
                $country_data = $country->getChartData();

                $fragment_country = new rex_fragment();
                $fragment_country->setVar('title', "Länder");
                $fragment_country->setVar('chart', '<div id="chart_country_detail" style="width: 100%;height:500px"></div>', false);
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

        // Add JavaScript for the specific chart
        $show_toolbox = rex_config::get('statistics', 'statistics_show_chart_toolbox') ? 'true' : 'false';
        $is_de = (trim(rex::getUser()->getLanguage()) == '' || trim(rex::getUser()->getLanguage()) == 'de_de') && rex::getProperty('lang') == 'de_de';
        $de_lang = $is_de ? '
            language: {
                "search": "_INPUT_",
                "searchPlaceholder": "Suchen",
                "decimal": ",",
                "info": "Einträge _START_-_END_ von _TOTAL_",
                "emptyTable": "Keine Daten",
                "infoEmpty": "0 von 0 Einträgen",
                "infoFiltered": "(von _MAX_ insgesamt)",
                "lengthMenu": "_MENU_ anzeigen",
                "loadingRecords": "Lade...",
                "zeroRecords": "Keine passenden Datensätze gefunden",
                "thousands": ".",
                "paginate": {
                    "first": "<<",
                    "last": ">>",
                    "next": ">",
                    "previous": "<"
                },
            },
            ' : '';

        $html .= '<script>
    if (rex.theme == "dark" || window.matchMedia(\'(prefers-color-scheme: dark)\').matches && rex.theme == "auto") {
        var theme = "dark";
    } else {
        var theme = "shine";
    }

    async function loadDetailChartData(type) {
        const response = await fetch(\'index.php?page=statistics/api&api=stats_charts&type=\' + type + \'&date_start=' . urlencode($request_date_start) . '&date_end=' . urlencode($request_date_end) . '\');
        return await response.json();
    }

    // Initialize specific chart based on type
    switch("' . $type . '") {
        case "browsertype":
            var chart_browsertype_detail = echarts.init(document.getElementById(\'chart_browsertype_detail\'), theme);
            loadDetailChartData(\'browsertype\').then(data => {
                var chart_browsertype_detail_option = {
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
                chart_browsertype_detail.setOption(chart_browsertype_detail_option);
            });
            break;

        case "browser":
            var chart_browser_detail = echarts.init(document.getElementById(\'chart_browser_detail\'), theme);
            loadDetailChartData(\'browser\').then(data => {
                var chart_browser_detail_option = {
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
                chart_browser_detail.setOption(chart_browser_detail_option);
            });
            break;

        case "os":
            var chart_os_detail = echarts.init(document.getElementById(\'chart_os_detail\'), theme);
            loadDetailChartData(\'os\').then(data => {
                var chart_os_detail_option = {
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
                chart_os_detail.setOption(chart_os_detail_option);
            });
            break;

        case "brand":
            var chart_brand_detail = echarts.init(document.getElementById(\'chart_brand_detail\'), theme);
            loadDetailChartData(\'brand\').then(data => {
                var chart_brand_detail_option = {
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
                chart_brand_detail.setOption(chart_brand_detail_option);
            });
            break;

        case "weekday":
            var chart_weekday_detail = echarts.init(document.getElementById(\'chart_weekday_detail\'), theme);
            loadDetailChartData(\'weekday\').then(data => {
                var chart_weekday_detail_option = {
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
                chart_weekday_detail.setOption(chart_weekday_detail_option);
            });
            break;

        case "hour":
            var chart_hour_detail = echarts.init(document.getElementById(\'chart_hour_detail\'), theme);
            loadDetailChartData(\'hour\').then(data => {
                var chart_hour_detail_option = {
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
                chart_hour_detail.setOption(chart_hour_detail_option);
            });
            break;

        case "country":
            var chart_country_detail = echarts.init(document.getElementById(\'chart_country_detail\'), theme);
            loadDetailChartData(\'country\').then(data => {
                var chart_country_detail_option = {
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
                chart_country_detail.setOption(chart_country_detail_option);
            });
            break;

        case "visitduration":
            var chart_visitduration_detail = echarts.init(document.getElementById(\'chart_visitduration_detail\'), theme);
            loadDetailChartData(\'visitduration\').then(data => {
                var chart_visitduration_detail_option = {
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
                chart_visitduration_detail.setOption(chart_visitduration_detail_option);
            });
            break;
    }

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
            }' . $de_lang . '
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
            }' . $de_lang . '
        });

        $(\'.dt_order_default\').DataTable({
            "paging": true,
            "pageLength": 10,
            "lengthChange": true,
            "lengthMenu": [5, 10, 50, 100],
            "search": {
                "caseInsensitive": true
            }' . $de_lang . '
        });
    });
</script>';

        return new rex_api_result(true, $html);
    }
}