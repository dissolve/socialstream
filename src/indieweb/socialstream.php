<?php
namespace IndieWeb\socialstream;

function clean_node($in)
{
    //single item handling
    if (!is_array($in)) {
        return clean_item($in);
    }

    //array handling
    $in = clean_array_before_recurse($in);

    //$res = array();
    foreach ($in as &$item) {
        $item = clean_node($item);
    }

    $in = clean_array_after_recurse($in);

    if (count($in) == 1) {
        return array_shift($in);
        // no need to recurse here as this item
        // has to have already been cleaned by recursive call above
    }

    if (empty($in)) {
        return null;
    }

    return $in;

}

function clean_array_before_recurse($in)
{
    if (isset($in['alternates'])) {
        unset($in['alternates']);
    }
    if (isset($in['rels'])) {
        unset($in['rels']);
    }
    if (isset($in['type'])) {
        unset($in['type']);
    }
    if (isset($in['value'])) {
        unset($in['value']);
    }
    return $in;
}

function clean_array_after_recurse($in)
{
    if (isset($in['url']) && !is_array($in['url']) && !isset($in['@id'])) {
       $in['@id'] = $in['url']; 
    }
    return $in;
}

function clean_item($in)
{
    if (is_string($in)) {
        $in = preg_replace('/\s+/', ' ', $in);
    }

    return $in;
}

function convert($mf)
{
    return json_encode(clean_node($mf), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

