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
    <input id="url-as-html" type="checkbox" name="ashtml" value="1" /><br>
    <label for="style-for-html">Include Styles in HTML</label>
    <input id="style-for-html" type="text" name="style" placeholder="Url or actual styles"/>
    <label for="target-for-html">Target Parent frame (Top is default)</label>
    <input id="target-for-html" type="checkbox" name="targetparent" value="1" /><br>
    <input type="submit" value="Convert to MF2" />
   </form>
   <form>
    <textarea name="content" type="text" placeholder="jf2 data" ></textarea>
    <input type="hidden" name="op" value="jf2-mf2" />
    <label for="content-as-html">Return as rendered html?</label>
    <input id="content-as-html" type="checkbox" name="ashtml" value="1" /><br>
    <label for="style-for-html">Include Styles in HTML</label>
    <input id="style-for-html" type="text" name="style" placeholder="Url or actual styles"/>
    <label for="target-for-html">Target Parent frame (Top is default)</label>
    <input id="target-for-html" type="checkbox" name="targetparent" value="1" /><br>
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
  <section>
   <h2>Convert MF2 to AS2</h2>
   <form>
    <input name="url" type="text" placeholder="url" />
    <input type="hidden" name="op" value="mf2-as2" />
    <input type="submit" value="Convert to AS2" />
   </form>
   <form>
    <textarea name="content" type="text" placeholder="mf2 data" ></textarea>
    <input type="hidden" name="op" value="mf2-as2" />
    <input type="submit" value="Convert to AS2" />
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

//make sure the URL we are given has a protocol.
// if not just give it http://
if (isset($_GET['url'])) {
    if(!preg_match('%^https?://%', $_GET['url'])){
        $_GET['url'] = 'http://' .  $_GET['url'];

    }
}

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
        $result = IndieWeb\jf2stream\convert($mf, $base, 'en-US', 'http://www.w3.org/ns/jf2');
    } else {
        $result = IndieWeb\jf2stream\jsonapiconvert($mf, $base);
    }

} elseif ($op == 'mf2-as2'){
    header('Content-Type: application/activity+json');
    //header('Content-Type: application/json');
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
    $result = IndieWeb\as2mf2stream\mf2_to_as2($mf, $base);
    

} elseif ($op == 'mf2-compact') {
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
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    $cleaned = IndieWeb\mf2stream\reference_format($mf);
    $result =  json_encode($cleaned, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} elseif ($op == 'as2-jf2') {
    if (isset($_GET['url'])) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/activity+json'));
        curl_setopt($ch, CURLOPT_URL, $_GET['url']);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $as2 = curl_exec($ch);

    } else {
        $as2 = $_GET['content'];
    }

    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    $result = IndieWeb\as2stream\as2_to_jf2($as2);

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
        $result = '<!DOCTYPE html><html><head><meta charset="utf-8">';
        if(isset($_GET['style']) && !empty($_GET['style'])){
            if(preg_match('/^https?:\/\//', $_GET['style'])){
                $result .= '<link rel="stylesheet" src="'.str_replace(array('<', '>'), array('%3C', '%3E'), $_GET['style']).'" />';
            } else {
                $result .= '<style>/*<![CDATA[*/'.str_replace('<', '&lt;', $_GET['style']).'/*]]>*/</style>';
            }
        }
        if(isset($_GET['targetparent']) && $_GET['targetparent'] == '1'){
            $result .= '<base target="_parent" />';
        } else {
            $result .= '<base target="_top" />';
        }
        $result .= '</head><body>';
        $result .= IndieWeb\jf2stream\revert($js);
        $result .= '</body></html>';

    } else {
        header('Content-Type: text/plain; charset=UTF-8');
        $result = IndieWeb\jf2stream\revert($js);
    }

} else {
     $result = 'error';
}

// TODO add round trip testing




echo($result);
