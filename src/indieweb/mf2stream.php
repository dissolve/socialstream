<?php
namespace IndieWeb\mf2stream;

function reference_format($obj)
{
    $references = array();
    foreach($obj['items'] as $key => $val){
        $obj['items'][$key] = reference_format_recurse($val);
        $references = array_merge($references,$obj['items'][$key]['references']);
        unset($obj['items'][$key]['references']);
    }
    $obj['references'] = $references;
    return $obj;
}

function reference_format_recurse($obj)
{
    if(!is_array($obj)){
        return $obj;
    }
    $references = array();
    foreach($obj as $key => $val){
        if(isset($val['properties']) && 
            isset($val['properties']['url']) && 
            isset($val['properties']['url'][0]) && 
            isset($val['type']) &&
            !is_array($val['properties']['url'][0])){

            $parsed_obj = reference_format_recurse($val);
            if(isset($parsed_obj['references'])){
                $references = array_merge($references,$parsed_obj['references']);
                unset($parsed_obj['references']);
            }

            $references[$val['properties']['url'][0]] = $parsed_obj;
            $obj[$key] = $val['properties']['url'][0];
        } else {
            $obj[$key] = reference_format_recurse($val);
            if(isset($obj[$key]['references'])){
                $references = array_merge($references,$obj[$key]['references']);
                unset($obj[$key]['references']);
            }
        }
    }
    $obj['references'] = $references;
    return $obj;
}
