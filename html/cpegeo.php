<?php
// cpegeo.php
$config = require 'config.php';
header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
echo json_encode($config['home']['location']), PHP_EOL;
