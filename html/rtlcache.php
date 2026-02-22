<?php
// rtlcache.php
$config = require 'config.inc.php';
$url=$_SERVER['QUERY_STRING'];
$ua=$_SERVER['HTTP_USER_AGENT'];
$data = $_POST;
$cpeurl = $config['config']['cpeurl'];
$mc = substr($url, strpos($url, '/restful') + strlen('/restful'));

function rtlcache($url, $manager, $database, $collection) {
    $options = [
        'http' => [
            'method' => 'GET',
            'ignore_errors' => true,
        ],
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    $status = json_decode($response)->Status;
    $mc = json_decode($response)->ModuleCommand;
    $result = json_decode($response)->Result;

    if ($status == 'fail') {
        return $response;
    }

    $data = ['Status' => $status, 'ModuleCommand' => $mc, 'Result' => $result];
    $filter = ['ModuleCommand' => $mc];
    $update = ['$set' => $data];
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update($filter, $update, ['upsert' => true]);

    try {
        $manager->executeBulkWrite("$database.$collection", $bulk);
    } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
        http_response_code(500);
        echo "Insert failed: " . $e->getMessage() . PHP_EOL;
        die();
    }

    $command = new MongoDB\Driver\Command([
        'listIndexes' => $collection,
    ]);
    try {
        $cursor = $manager->executeCommand($database, $command);
    } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
        http_response_code(500);
        echo "listIndexes failed: " . $e->getMessage() . PHP_EOL;
        die();
    }
    $indexes = array_map(fn($index) => $index->name, $cursor->toArray());
    if (in_array('mc_index', $indexes)) {
        return $response;
    }

    $indexKey = ['ModuleCommand' => 1];
    $command = new MongoDB\Driver\Command([
        'createIndexes' => $collection,
        'indexes' => [[
            'key' => $indexKey,
            'name' => 'mc_index',
            'unique' => true,
        ]]
    ]);
    try {
        $manager->executeCommand($database, $command);
    } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
        http_response_code(500);
        echo "createIndexes failed: " . $e->getMessage() . PHP_EOL;
        die();
    }
    return $response;
}

$database = $config['mongodb']['database'];
$collection='rtlcache';
$manager = new MongoDB\Driver\Manager($config['mongodb']['connection_string']);
$query = new MongoDB\Driver\Query(['ModuleCommand' => $mc]);
$cursor = $manager->executeQuery("$database.$collection", $query);
ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');
if ($cursor->isDead())
{
    $response = rtlcache($url, $manager, $database, $collection);
    echo $response, PHP_EOL;
    ob_end_flush();
    exit;
}
foreach ($cursor as $doc) {
    echo json_encode(['Status' => $doc->Status, 'ModuleCommand' => $doc->ModuleCommand, 'Result' => $doc->Result]), PHP_EOL;
}
ob_end_flush();
flush();
fastcgi_finish_request();
rtlcache($url, $manager, $database, $collection);

