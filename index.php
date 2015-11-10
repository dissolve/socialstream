<?php
if (!isset($_GET['url']) && !isset($_GET['content'])) {
?>
<!DOCTYPE html>
<html>
<head>
<style>
input[type="submit"]{
display:block;
background:lightblue;
margin-top:10px;
border-radius:9px;
}
form {
margin-left:20px;
}
textarea {
height:150px;
width:500px;
}
h2::before {
border: 2px solid black;
content: "";
display: block;
width: 520px;
}
h2::after {
border: 2px solid lightgray;
content: "";
display: block;
width: 520px;
}
footer {
font-size:0.9em
}
label {
display:block;
}
</style>
</head>
 <body>
  <section>
   <h2>Convert MF2 to JF2</h2>
   <form>
    <input name="url" type="text" placeholder="url" />
    <input type="hidden" name="op" value="mf2-jf2" />
    <input type="submit" value="Convert to JF2" />
   </form>
   <form>
    <textarea name="content" type="text" placeholder="mf2 data" ></textarea>
    <input type="hidden" name="op" value="mf2-jf2" />
    <input type="submit" value="Convert to JF2" />
   </form>
  </section>
  <section>
   <h2>Convert JF2 to MF2</h2>
   <form>
    <input name="url" type="text" placeholder="url" />
    <input type="hidden" name="op" value="jf2-mf2" />
    <label for="url-as-html">Return as rendered html?</label>
    <input id="url-as-html" type="checkbox" name="ashtml" value="1" />
    <input type="submit" value="Convert to MF2" />
   </form>
   <form>
    <textarea name="content" type="text" placeholder="jf2 data" ></textarea>
    <input type="hidden" name="op" value="jf2-mf2" />
    <label for="content-as-html">Return as rendered html?</label>
    <input id="content-as-html" type="checkbox" name="ashtml" value="1" />
    <input type="submit" value="Convert to MF2" />
   </form>
  </section>
  <section>
   <h2>Convert MF2 to jsonapi</h2>
   <form>
    <input name="url" type="text" placeholder="url" />
    <input type="hidden" name="op" value="mf2-jsonapi" />
    <input type="submit" value="Convert to jsonapi" />
   </form>
   <form>
    <textarea name="content" type="text" placeholder="mf2 data" ></textarea>
    <input type="hidden" name="op" value="mf2-jsonapi" />
    <input type="submit" value="Convert to jsonapi" />
   </form>
  </section>
<footer>
Please report bugs on <a href="http://github.com/dissolve/socialstream/issues">github</a>
</footer>
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
    if(isset($_GET['ashtml']) && $_GET['ashtml']){

        //todo: add some simple stylesheets
        $result = '<!DOCTYPE html><html><meta charset="utf-8"><body>';
        $result .= IndieWeb\socialstream\revert($js);
        $result .= '</body></html>';

    } else {
        header('Content-Type: text/plain; charset=UTF-8');
        $result = IndieWeb\socialstream\revert($js);
    }

} else {
     $result = 'error';
}

// TODO add round trip testing




echo($result);
