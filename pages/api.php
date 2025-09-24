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

$page = rex_request('api', 'string', '');

switch ($page) {
    case 'stats_load_full':
        $api = new rex_api_stats_load_full();
        $result = $api->execute();
        break;

    case 'stats_detail':
        $api = new rex_api_stats_detail();
        $result = $api->execute();
        break;

    case 'stats_charts':
        $api = new rex_api_stats_charts();
        $result = $api->execute();
        break;

    default:
        $result = new rex_api_result(false, 'Unknown API endpoint');
        break;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'ok' => $result->isSuccessfull(),
    'data' => $result->getMessage()
]);