<?php
if (!isset($_GET['url'])) {
    echo "query with ?url=";
    die();
}

header('Content-Type: application/json');

require __DIR__ . '/vendor/autoload.php';

$url = $_GET['url'];
$mf = Mf2\fetch($url);

$scheme = parse_url($url, PHP_URL_SCHEME);
if($scheme == null){

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);

    $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

    curl_close($ch);

    $scheme = parse_url($url, PHP_URL_SCHEME);

}
$host = parse_url($url, PHP_URL_HOST);

$base = '';
if(!empty($scheme) && !empty($host)){
    $base = $scheme . '://' . $host;
}

$result = IndieWeb\socialstream\convert($mf, $base, 'en-US');

echo($result);

