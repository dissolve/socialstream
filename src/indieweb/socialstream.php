<?php
namespace IndieWeb\socialstream;

function single_item_array_reduce($in)
{
    if(is_array($in)){
        if(count($in) == 1){
            return single_item_array_reduce($in[0]);
        } else {
            foreach( $in as &$item){
                $item = single_item_array_reduce($item);
            }
            return $in;
        }
    } else {
        return $in;
    }
}

function convert($mf)
{
   return json_encode(single_item_array_reduce($mf),JSON_PRETTY_PRINT);
}

