<?php

// This API endpoint for "stats_load_full" has been deprecated/disabled.
// The addon now only serves detail modals via the `stats_detail` API.

rex_response::cleanOutputBuffers();
rex_response::setStatus(410);
rex_response::sendContent(json_encode(["ok" => false, "msg" => "stats_load_full disabled; use stats_detail instead"]));
exit;
