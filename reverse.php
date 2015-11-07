<?php
/*
if (!isset($_GET['url'])) {
    echo "query with ?url=";
    die();
}
 */

require __DIR__ . '/vendor/autoload.php';

//$url = $_GET['url'];
//$url = 'http://stream.thatmustbe.us/?url=ben.thatmustbe.me/static/test2.html';
$url = 'http://stream.thatmustbe.us/?url=ben.thatmustbe.me/note/2015/9/16/23/';


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $js = curl_exec($ch);


//echo($js);
//echo '------------------' . "\n\n";
$mf2 = IndieWeb\socialstream\revert($js);
//echo($mf2);
$mf = Mf2\parse($mf2);
//echo '------------------' . "\n\n";
$result = IndieWeb\socialstream\convert($mf, '', 'en-US', 'http://stream.thatmustbe.us/jf2.php');
echo($result);

