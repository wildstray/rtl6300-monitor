<?php
$config = require 'config.inc.php';
parse_str($_SERVER['QUERY_STRING'], $params);

function measure_ping($config): ?float
{
    $url = $config['speed']['ping_url'];
    $referer = $config['speed']['referer'] ?? "";
    $timeout = $config['speed']['timeout'] ?? 5000;
    $attempts = $config['speed']['ping_attempts'] ?? 3;
    $times = [];

    for ($i = 0; $i < $attempts; $i++) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL               => $url,
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_NOBODY            => true,
            CURLOPT_TIMEOUT_MS        => $timeout,
            CURLOPT_CONNECTTIMEOUT_MS => 2000,
            CURLOPT_SSL_VERIFYPEER    => true,
            CURLOPT_REFERER           => $referer,
        ]);
        curl_exec($ch);
        $elapsed = curl_getinfo($ch, CURLINFO_CONNECT_TIME_T) / 1000;
        $err     = curl_errno($ch);
        curl_close($ch);

        if (!$err) {
            $times[] = $elapsed;
        }
        usleep(50000);
    }
    if (empty($times)) return null;

    sort($times);
    $idx = (int) floor(count($times) * 0.25);
    return round($times[$idx], 1);
}

function measure_download($config): ?float
{
    $url = $config['speed']['download_url'];
    $referer = $config['speed']['referer'] ?? "";
    $timeout = $config['speed']['timeout'] ?? 5000;

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL               => $url,
        CURLOPT_RETURNTRANSFER    => true,
        CURLOPT_TIMEOUT_MS        => $timeout,
        CURLOPT_CONNECTTIMEOUT_MS => 2000,
        CURLOPT_NOPROGRESS        => true,
        CURLOPT_SSL_VERIFYPEER    => true,
        CURLOPT_ENCODING          => 'identity',
        CURLOPT_HTTPHEADER        => ['Accept-Encoding: identity'],
        CURLOPT_REFERER           => $referer,
    ]);

    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $speed = curl_getinfo($ch, CURLINFO_SPEED_DOWNLOAD_T);
    $size = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD_T);
    $err = curl_errno($ch);
    curl_close($ch);
    if ($code !== 200) return null;

    return round($speed * 8 / 1e6, 1);
}

function measure_upload($config): ?float
{
    $url = $config['speed']['upload_url'];
    $referer = $config['speed']['referer'] ?? "";
    $timeout = $config['speed']['timeout'] ?? 5000;
    $size = $config['speed']['upload_size'] ?? 5000000;
    $payload = random_bytes($size);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL               => $url,
        CURLOPT_RETURNTRANSFER    => true,
        CURLOPT_BINARYTRANSFER    => true,
        CURLOPT_POST              => true,
        CURLOPT_UPLOAD            => true,
        CURLOPT_POSTFIELDS        => $payload,
        CURLOPT_TIMEOUT_MS        => $timeout,
        CURLOPT_CONNECTTIMEOUT_MS => 2000,
        CURLOPT_NOPROGRESS        => true,
        CURLOPT_SSL_VERIFYPEER    => true,
        CURLOPT_REFERER           => $referer,
    ]);

    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $speed = curl_getinfo($ch, CURLINFO_SPEED_UPLOAD_T);
    $size = curl_getinfo($ch, CURLINFO_SIZE_UPLOAD_T);
    $err = curl_errno($ch);
    curl_close($ch);
    if (!in_array($code, array(100, 200))) return null;
    return round($speed * 8 / 1e6, 1);
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');
$result = [];
if (!function_exists('curl_init')) {
    echo json_encode($result), PHP_EOL;
}
$key = ftok(__FILE__, 'a');
$semaphore = sem_get($key, 1);
while (!sem_acquire($semaphore)) { usleep(50000); }
$test = $params['test'] ?? 'download';
switch ($test) {
    case 'ping':
        $result['value'] = measure_ping($config);
        break;
    case 'dl':
        $result['value'] = measure_download($config);
        break;
    case 'ul':
        $result['value'] = measure_upload($config);
        break;
}
sem_release($semaphore);
echo json_encode($result), PHP_EOL;
