<?php
// arccache.php
$config = require 'config.php';
$url=$_SERVER['QUERY_STRING'];
$ua=$_SERVER['HTTP_USER_AGENT'];
$data = $_POST;
$headers = ["Content-type: application/x-www-form-urlencoded", "User-Agent: $ua"];
$options = [
    'http' => [
        'header' => $headers,
        'method' => 'POST',
        'content' => http_build_query($data),
        'ignore_errors' => true,
    ],
];
$ilf=$data['InputLineFeatures'];
$database = $config['mongodb']['database'];
$collection='arccache';
$manager = new MongoDB\Driver\Manager($config['mongodb']['connection_string']);
$query = new MongoDB\Driver\Query(['ilf' => $ilf]);
$cursor = $manager->executeQuery("$database.$collection", $query);
header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
if ($cursor->isDead())
{
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    $data = ['ilf' => $ilf, 'response' => $response];
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->insert($data);
    try {
        $manager->executeBulkWrite("$database.$collection", $bulk);
    } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
        http_response_code(500);
        echo "Insert failed: " . $e->getMessage() . PHP_EOL;
        die();
    }
    $indexKey = ['ilf' => 1];
    $createIndexCommand = new MongoDB\Driver\Command([
        'createIndexes' => $collection,
        'indexes' => [[
            'key' => $indexKey,
            'name' => 'ilf_index',
            'unique' => true,
        ]]
    ]);
    try {
        $manager->executeCommand($database, $createIndexCommand);
    } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
        http_response_code(500);
        echo "Index failed: " . $e->getMessage() . PHP_EOL;
        die();
    }
    echo $response, PHP_EOL;
}
foreach ($cursor as $doc) {
    echo $doc->response, PHP_EOL;
}
