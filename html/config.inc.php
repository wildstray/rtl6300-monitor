<?php
// config.php
return [
    'mongodb' => [
        'connection_string' => 'mongodb://mongodb',
        'database' => 'lteitaly',
    ],
    'config' => [
        'home' => [45.0705, 7.6868],
        'cpeurl' => 'http://ncc/ltemon/rtlcache.php?http://172.20.168.1/restful',
        'gisurl' => 'http://ncc/ltemon/arccache.php?https://elevation.arcgis.com/arcgis/rest/services/Tools/ElevationSync/GPServer/Profile/execute',
        'refresh' => 5000,
    ],
    'speed' => [
        'ping_url' => 'https://www.cloudflare.com/cdn-cgi/trace',
        'ping_attempts' => 3,
        'timeout' => 5000,
        'download_url' => 'https://speed.cloudflare.com/__down?bytes=75000000',
        'upload_url' => 'https://speed.cloudflare.com/__up',
        'upload_size' => 15000000,
        'referer' => 'https://speed.cloudflare.com/',
    ],
];
