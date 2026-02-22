<?php
// config.php
return [
    'mongodb' => [
        'connection_string' => 'mongodb://kasa',
        'database' => 'lteitaly',
    ],
    'config' => [
        'home' => [45.101725, 7.303470],
        'cpeurl' => 'http://ncc/ltemon/rtlcache.php?http://172.20.168.1/restful',
        'gisurl' => 'http://ncc/ltemon/arccache.php?https://elevation.arcgis.com/arcgis/rest/services/Tools/ElevationSync/GPServer/Profile/execute',
        'refresh' => 5000,
    ],
];
