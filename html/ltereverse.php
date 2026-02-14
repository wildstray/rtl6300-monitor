<?php
// ltereverse.php
$config = require 'config.php';
parse_str($_SERVER['QUERY_STRING'], $params);
$lon = (float)$params['lon'];
$lat = (float)$params['lat'];
$uri = 'mongodb://kasa/lteitaly';
$manager = new MongoDB\Driver\Manager($config['mongodb']['connection_string']);
$query = new MongoDB\Driver\Query(['location' => ['$near' => ['$geometry' =>['type' => 'Point','coordinates'=>[$lon, $lat]],'$maxDistance' => intval(10),'$minDistance' => intval(0)]]]);
$collection='sites';
$cursor = $manager->executeQuery("$database.$collection", $query);
header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
if ($cursor->isDead())
{
    http_response_code(404);
    echo json_encode([]), PHP_EOL;
    die();
}
echo json_encode($cursor->toArray()), PHP_EOL;
