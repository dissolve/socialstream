<?php
if (!isset($_GET['url']) && !isset($_GET['content'])) {
?>
<html>
 <body>
  <div>
   <h2>Convert MF2 to JF2</h2>
   <form>
    <input name="url" type="text" placeholder="url" />
    <input type="hidden" name="op" value="mf2-jf2" />
    <input type="submit" value="Convert to JF2" />
   </form>
or
   <form>
    <textarea name="content" type="text" placeholder="mf2 data" ></textarea>
    <input type="hidden" name="op" value="mf2-jf2" />
    <input type="submit" value="Convert to JF2" />
   </form>
  </div>
  <div>
   <h2>Convert JF2 to MF2</h2>
   <form>
    <input name="url" type="text" placeholder="url" />
    <input type="hidden" name="op" value="jf2-mf2" />
    <input type="submit" value="Convert to MF2" />
   </form>
or
   <form>
    <textarea name="content" type="text" placeholder="jf2 data" ></textarea>
    <input type="hidden" name="op" value="jf2-mf2" />
    <input type="submit" value="Convert to MF2" />
   </form>
  </div>
  <div>
   <h2>Convert MF2 to jsonapi</h2>
   <form>
    <input name="url" type="text" placeholder="url" />
    <input type="hidden" name="op" value="mf2-jsonapi" />
    <input type="submit" value="Convert to jsonapi" />
   </form>
or
   <form>
    <textarea name="content" type="text" placeholder="mf2 data" ></textarea>
    <input type="hidden" name="op" value="mf2-jsonapi" />
    <input type="submit" value="Convert to jsonapi" />
   </form>
  </div>
 </body>
</html>
<?php
    die();

}
if (!isset($_GET['op'])) {
    $op = 'mf2-jf2';
} else {
    $op = $_GET['op'];
}

require __DIR__ . '/vendor/autoload.php';

if ($op == 'mf2-jf2' || $op == 'mf2-jsonapi') {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');

    if (isset($_GET['url'])) {
        $url = $_GET['url'];
        $mf = Mf2\fetch($url);

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme == null) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
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
        if (!empty($scheme) && !empty($host)) {
            $base = $scheme . '://' . $host;
        }

    } else {
        $mf = Mf2\parse($_GET['content']);
        $base = '';
    }
    if ($op == 'mf2-jf2') {
        $result = IndieWeb\socialstream\convert($mf, $base, 'en-US', 'http://stream.thatmustbe.us/jf2.php');
    } else {
        $result = IndieWeb\socialstream\jsonapiconvert($mf, $base);
    }

} elseif ($op == 'jf2-mf2') {
    if (isset($_GET['url'])) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $_GET['url']);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $js = curl_exec($ch);

    } else {
        $js = $_GET['content'];
    }
    $result = '<html><body><pre>';
    $result .= htmlentities(IndieWeb\socialstream\revert($js));
    $result .= '</pre></body></html>';

} else {
     $result = 'error';
}

// TODO add round trip testing




echo($result);
