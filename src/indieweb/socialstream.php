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
    
    if(isset($in['html'])){
        $in['content-type'] = "text/html";
        $in['value'] = $in['html'];
        unset($in['html']);
    } elseif (isset($in['value'])) {
        unset($in['value']);
    }

    if (isset($in['properties'])) {
        $prop = $in['properties'];
        unset($in['properties']);
        $in = array_merge($in , $prop);
    }

    if(isset($in['url']) && is_array($in['url'])){
        //TODO: dereference relative URLs
        $in['url'] = array_unique($in['url']);
    }

    if(isset($in['name']) && is_array($in['name'])){
        $in['name'] = array_unique($in['name']);
    }

    
    return $in;
}

function clean_array_after_recurse($in)
{
    if (isset($in['type']) && !is_array($in['type'])) {
        $new_val = preg_replace('/^h-/', '', $in['type']);
        $in['type'] = $new_val;
    }

    if(isset($in['photo']) && !is_array($in['photo'])){
        $url = $in['photo'];
        $in['photo'] = array(
                //TODO fix urlx
            'url' => $url,
            'type' => 'image'
        );
    }
    if(isset($in['photo']) && is_array($in['photo']) && !is_hash($in['photo'])){

        foreach($in['photo'] as &$photo){
            $url = $photo;
            $photo = array(
                //TODO fix urlx
                'url' => $url,
                'type' => 'image'
            );
        }
    }

    if(isset($in['video']) && !is_array($in['video'])){
        $url = $in['video'];
        $in['video'] = array(
                //TODO fix urlx
            'url' => $url,
            'type' => 'video'
        );
    }
    if(isset($in['video']) && is_array($in['video']) && !is_hash($in['video'])){

        foreach($in['video'] as &$video){
            $url = $video;
            $video = array(
                //TODO fix urlx
                'url' => $url,
                'type' => 'video'
            );
        }
    }

    if(isset($in['audio']) && !is_array($in['audio'])){
        $url = $in['audio'];
        $in['audio'] = array(
                //TODO fix urlx
            'url' => $url,
            'type' => 'audio'
        );
    }
    if(isset($in['audio']) && is_array($in['audio']) && !is_hash($in['audio'])){

        foreach($in['audio'] as &$audio){
            $url = $audio;
            $audio = array(
                //TODO fix urlx
                'url' => $url,
                'type' => 'audio'
            );
        }
    }



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

