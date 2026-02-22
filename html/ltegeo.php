<?php
// ltegeo.php
$config = require 'config.inc.php';
parse_str($_SERVER['QUERY_STRING'], $params);
$mcc = (int)$params['mcc'];
$mnc = (int)$params['mnc'];
$enb = (int)$params['enb'];
$manager = new MongoDB\Driver\Manager($config['mongodb']['connection_string']);
$query = new MongoDB\Driver\Query(['mcc' => $mcc, 'mnc' => $mnc, 'enb' => $enb]);
$database = $config['mongodb']['database'];
$collection='sites';
$cursor = $manager->executeQuery("$database.$collection", $query);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');
if ($cursor->isDead())
{
    http_response_code(404);
    echo json_encode([]), PHP_EOL;
    die();
}
foreach ($cursor as $doc) {
    echo json_encode($doc), PHP_EOL;
}
