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
    if (isset($in['value'])) {
        unset($in['value']);
    }
    if (isset($in['properties'])) {
        $prop = $in['properties'];
        unset($in['properties']);
        $in = array_merge($in , $prop);
        
    }
    if(isset($in['url']) && is_array($in['url'])){
        $in['url'] = array_unique($in['url']);
    }
    if(isset($in['name']) && is_array($in['name'])){
        $in['name'] = array_unique($in['name']);
    }
    
    return $in;
}

function clean_array_after_recurse($in)
{
    if (isset($in['children']) && is_hash($in['children'])) {
        $in['children'] = array($in['children']); 
    }
    // JSON-LD looking-ish garbage... there have been people complaining about '@' in values
    // so i'm not going down this road.  JSON-LD people can easily do this change themselves
    // if they want to process as JSON-LD
    /*
    if (isset($in['url']) && !is_array($in['url']) && !isset($in['@id'])) {
        $in['@id'] = $in['url']; 
    }
     */
    if (isset($in['type']) && !is_array($in['type'])) {
        $new_val = preg_replace('/^h-/', '', $in['type']);
        //do more logic here
        $in['type'] = $new_val;
        //unset($in['type']);
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

function is_hash(array $array) {
    return (bool)count(array_filter(array_keys($array), 'is_string'));
}

function convert($mf, $lang = 'en')
{
    $cleaned = clean_node($mf);
    //if(is_hash($cleaned)){
        //$cleaned['@language'] = $lang;
    //}

    return json_encode($cleaned, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

