<?php
namespace IndieWeb\socialstream;


class JsonApiStreamCleaner
{
    private $url_base;
    private function cleanNode($in)
    {
        //single item handling
        if (!is_array($in)) {
            return $this->cleanItem($in);
        }

        //array handling
        $in = $this->cleanArrayBeforeRecurse($in);

        //$res = array();
        foreach ($in as &$item) {
            $item = $this->cleanNode($item);
        }

        $in = $this->cleanArrayAfterRecurse($in);

        if (isset($in['url'])) {
            if(is_array($in['url'])){
                $in['id'] = $in['url'][0];
            } else {
                $in['id'] = $in['url'];
            }
        }

        if (count($in) == 1 && !isset($in['url']) && !isset($in['content-type']) && !isset($in['type'])) {
            return array_shift($in);
            // no need to recurse here as this item
            // has to have already been cleaned by recursive call above
        }

        if (empty($in)) {
            return null;
        }

        return $in;

    }

    private function cleanArrayBeforeRecurse($in)
    {
        if (isset($in['alternates'])) {
            unset($in['alternates']);
        }

        if (isset($in['rels'])) {
            $in['rels'] = $in['links'];
            unset($in['rels']);
        }

        if (isset($in['html'])) {
            $in['content-type'] = "text/html";
            $in['value'] = $in['html'];
            unset($in['html']);
        } elseif (isset($in['value'])) {
            unset($in['value']);
        }

        if (isset($in['properties'])) {
            $in['attributes'] = $in['properties'];
            unset($in['properties']);
        }

        if (isset($in['children'])) {
            $in['relationships'] = $in['children'];
            unset($in['children']);
        }

        if (isset($in['url']) && is_array($in['url'])) {
            $in['url'] = array_unique($in['url']);
            foreach($in['url'] as &$url){
                $url = $this->sanitizeUrl($url);
            }
        }

        if (isset($in['name']) && is_array($in['name'])) {
            $in['name'] = array_unique($in['name']);
        }


        return $in;
    }

    private function cleanArrayAfterRecurse($in)
    {
        if (isset($in['type']) && !is_array($in['type'])) {
            $new_val = preg_replace('/^h-/', '', $in['type']);
            $in['type'] = $new_val;
        }


        return $in;
    }

    private function cleanItem($in)
    {
        if (is_string($in)) {
            $in = preg_replace('/\s+/', ' ', $in);
        }

        return $in;
    }

    private function isHash(array $array)
    {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }

    private function sanitizeUrl($url)
    {
        $split_url = parse_url($url);
        if(!isset($split_url['host']) || empty($split_url['host'])) {
            $split_url['host'] = parse_url($this->url_base, PHP_URL_HOST);
            $split_url['scheme'] = parse_url($this->url_base, PHP_URL_SCHEME);
        }
        $url = $split_url['scheme'] . "://" . $split_url['host'] . $split_url['path'] .
            (isset($split_url['query']) ? '?' . $split_url['query']  : '' ) .
            (isset($split_url['fragment']) ? '#' . $split_url['fragment']  : '' );
        
        return $url;
    }


    public function clean($mf, $base_url = "")
    {
        $this->url_base = $base_url ;
        $cleaned = $this->cleanNode($mf);


        if($context){
            $cleaned['@context'] = $context;
        }
        

        return $cleaned;

    }


    /* TODO
     *  category is sometimes url, at least for me
     * */
}


function jsonapiconvert($mf, $base_url = "")
{
    $cleaner = new JsonApiStreamCleaner();
    $cleaned = $cleaner->clean($mf, $base_url);

    return json_encode($cleaned, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
