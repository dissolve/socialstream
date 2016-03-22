<?php
namespace IndieWeb\as2stream;


class AS2StreamConverter
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
            $prop = $in['properties'];
            unset($in['properties']);
            $in = array_merge($in, $prop);
        }

        if (isset($in['url']) && is_array($in['url'])) {
            $in['url'] = array_unique($in['url']);
            foreach ($in['url'] as &$url) {
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
        // if there is an array of classes, we have to pick one, so we look for knowwn ones first
        //  if we still cannot decide, we just pick the first one
        if (isset($in['type']) && is_array($in['type'])) {
            if(in_array('h-adr', $in['type'])) {
                $in['type'] = 'h-adr';
            } elseif(in_array('h-card', $in['type'])) {
                $in['type'] = 'h-card';
            } elseif(in_array('h-entry', $in['type'])) {
                $in['type'] = 'h-entry';
            } elseif(in_array('h-event', $in['type'])) {
                $in['type'] = 'h-event';
            } elseif(in_array('h-feed', $in['type'])) {
                $in['type'] = 'h-feed';
            } elseif(in_array('h-geo', $in['type'])) {
                $in['type'] = 'h-geo';
            } elseif(in_array('h-item', $in['type'])) {
                $in['type'] = 'h-item';
            } elseif(in_array('h-listing', $in['type'])) {
                $in['type'] = 'h-listing';
            } elseif(in_array('h-product', $in['type'])) {
                $in['type'] = 'h-product';
            } elseif(in_array('h-recipe', $in['type'])) {
                $in['type'] = 'h-recipe';
            } elseif(in_array('h-resume', $in['type'])) {
                $in['type'] = 'h-resume';
            } elseif(in_array('h-review', $in['type'])) {
                $in['type'] = 'h-review';
            } elseif(in_array('h-review-aggregate', $in['type'])) {
                $in['type'] = 'h-review-aggregate';
            } else {
                $in['type'] = $in['type'][0];
            }
        }
        if (isset($in['type']) && !is_array($in['type'])) {
            $new_val = preg_replace('/^h-/', '', $in['type']);
            $in['type'] = $new_val;
        }

/*
        if (isset($in['photo']) && !is_array($in['photo'])) {
            $url = $in['photo'];
            $in['photo'] = array(
                'url' => $this->sanitizeUrl($url),
                'type' => 'image'
            );
        }
        if (isset($in['photo']) && is_array($in['photo']) && !$this->isHash($in['photo'])) {
            foreach ($in['photo'] as &$photo) {
                $url = $photo;
                $photo = array(
                    'url' => $this->sanitizeUrl($url),
                    'type' => 'image'
                );
            }
        }

        if (isset($in['video']) && !is_array($in['video'])) {
            $url = $in['video'];
            $in['video'] = array(
                'url' => $this->sanitizeUrl($url),
                'type' => 'video'
            );
        }
        if (isset($in['video']) && is_array($in['video']) && !$this->isHash($in['video'])) {
            foreach ($in['video'] as &$video) {
                $url = $video;
                $video = array(
                    'url' => $this->sanitizeUrl($url),
                    'type' => 'video'
                );
            }
        }

        if (isset($in['audio']) && !is_array($in['audio'])) {
            $url = $in['audio'];
            $in['audio'] = array(
                'url' => $this->sanitizeUrl($url),
                'type' => 'audio'
            );
        }
        if (isset($in['audio']) && is_array($in['audio']) && !$this->isHash($in['audio'])) {
            foreach ($in['audio'] as &$audio) {
                $url = $audio;
                $audio = array(
                    'url' => $this->sanitizeUrl($url),
                    'type' => 'audio'
                );
            }
        }

        if (isset($in['in-reply-to']) && !is_array($in['in-reply-to'])) {
            $url = $in['in-reply-to'];
            $in['in-reply-to'] = array(
                'url' => $this->sanitizeUrl($url)
            );
        }
        if (isset($in['in-reply-to']) && is_array($in['in-reply-to']) && !$this->isHash($in['in-reply-to'])) {
            foreach ($in['in-reply-to'] as &$reply) {
                $url = $reply;
                $reply = array(
                    'url' => $this->sanitizeUrl($url)
                );
            }
        }
        if (isset($in['syndication']) && !is_array($in['syndication'])) {
            $url = $in['syndication'];
            $in['syndication'] = array(
                'url' => $this->sanitizeUrl($url)
            );
        }
        if (isset($in['syndication']) && is_array($in['syndication']) && !$this->isHash($in['syndication'])) {
            foreach ($in['syndication'] as &$syndication) {
                $url = $syndication;
                $syndication = array(
                    'url' => $this->sanitizeUrl($url)
                );
            }
        }
*/


        if (isset($in['children']) && $this->isHash($in['children'])) {
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
        if (!isset($split_url['host']) || empty($split_url['host'])) {
            $split_url['host'] = parse_url($this->url_base, PHP_URL_HOST);
            $split_url['scheme'] = parse_url($this->url_base, PHP_URL_SCHEME);
        }
        $url = $split_url['scheme'] . "://" . $split_url['host'] . $split_url['path'] .
            (isset($split_url['query']) ? '?' . $split_url['query'] : '' ) .
            (isset($split_url['fragment']) ? '#' . $split_url['fragment'] : '' );

        return $url;
    }


    public function toAs2($mf, $base_url = "", $lang = 'en', $context = null)
    {
        $this->url_base = $base_url ;
        $cleaned = $this->cleanNode($mf);

        //brutal hack until lang is obtained from the mf2 parser

        if ($this->isHash($cleaned)) {
            $cleaned['lang'] = $lang;
        } elseif ( is_array($cleaned) ) {
            foreach ($cleaned as &$entry) {
                $entry['lang'] = $lang;
            }
            return array(
                'children' => $cleaned
            );

        }

        if ($context) {
            $cleaned['@context'] = $context;
        }


        return $cleaned;

    }


    private function expandKeyVal($key, $val)
    {
        $result = '';
        if (in_array($key, array('published','updated', 'created', 'start', 'end', "rev", "reviewed", "accessed"))) {
            $result .= '<span class="dt-' . $key . '">' . $val . '</span>' . "\n";
        } else if ($key == 'url') {
            $result .= '<a class="u-' . $key . '" href="' . $val . '">' . $val . '</a>' . "\n";
        } else {
            $result .= '<span class="p-' . $key . '">' . $val . '</span>' . "\n";
        }
        return $result;
    }

    private function expandInternalProperties($data)
    {
        if (isset($data['type'])) {
            unset($data['type']);
        }
        $result = '';
        foreach ($data as $key => $val) {
            if ($key == 'children') {
                $result .= $this->expandChildren($data);
            } elseif (!is_array($val)) {
                $result .= $this->expandKeyVal($key, $val);
            } elseif ($key == 'content') {
                $result .= '<span class="e-' . $key . '">' . $val['value'] . '</span>' . "\n";
            } elseif ($this->isHash($val)) {
                $result .= $this->object2jf2($val, array('p-' . $key));
            } else {
                foreach ($val as $subval) {
                    $result .= $this->expandKeyVal($key, $subval);
                }
            }
        }
        return $result;
    }

    private function expandChildren($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $result = array();
        foreach ($data as $child) {
            $result[] = $this->object2jf2($child);
        }
        return $result;
    }


    private function object2jf2($data, $classes = array())
    {
        $result = array();
        if (isset($data['nameMap'])) {
            $data['name'] = $data['nameMap']['en'];
            //todo: pass in language or we could just pick one randomly
            unset($data['nameMap']);
        }
        if (isset($data['id']) && !isset($data['url'])) {
            $data['url'] = $data['id'];
            //todo: pass in language or we could just pick one randomly
            unset($data['id']);
        }

        if (isset($data['nameMap'])){
            $result['name'] = $data['nameMap']['en'];
        }
        if (isset($data['name'])){
            $result['name'] = $data['name'];
        }
        if (isset($data['published'])){
            $result['published'] = $data['published'];
        }
        if (isset($data['image'])){

            if (!is_array($data['image'])) {
                $result['photo'] = $data['image'];
            } elseif (isset($data['image']['href'])){
                $result['photo'] = $data['image']['href'];
            }
        }
        if (isset($data['id'])){
            $result['url'] = $data['id'];
        }
        if (isset($data['@id'])){
            $result['url'] = $data['@id'];
        }
        
        if (isset($data['actor'])){
            $result['author'] = $this->object2jf2($data['actor']);
        }
        if ( isset($data['items'])) {
            $result['children'] = $this->expandChildren($data['items']);
        }

        if (isset($data['type'])) {
            if($data['type'] == 'Collection'){
                $result['type'] = 'feed';
            }elseif($data['type'] == 'Add'){
                $result['type'] = 'entry';
                if(isset($result['name'])){
                    $result['summary'] = $result['name'];
                    unset($result['name']);
                }
                if (isset($data['object'])){
                    if (isset($data['object']['type'])){
                        if ($data['object']['type'] == 'Image'){
                            $result['photo'] = $data['object']['id'];
                        }
                    }
                }
            }elseif($data['type'] == 'Like'){
                $result['type'] = 'entry';
                if (isset($data['object']) && isset($data['object']['url'])){
                    $result['like-of'] = $data['object']['url'];
                }
            }elseif($data['type'] == 'Person'){
                $result['type'] = 'card';
            } 
        }


            //$classes[] = 'h-' . $data['type'];
            //$result .= '<div class="' . implode(' ', $classes) . '">' . "\n";
            //$result .= $this->expandInternalProperties($data);
            //$result .= '</div>' . "\n";
        //} else {
            //$result = $this->expandChildren($data);
        //}

        return $result;
    }


    /* TODO
     *  category is sometimes url, at least for me
     * */
    public function toJf2($js)
    {
        $data = json_decode($js, true);

        $result = $this->object2jf2($data);

        return $result;
    }
}

function jf2_to_as2($jf2, $base_url = "", $lang = 'en', $context = null)
{
    $converter = new AS2StreamConverter();
    $cleaned = $converter->toAs2($mf, $base_url, $lang, $context);

    return json_encode($cleaned, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

function as2_to_jf2($as2)
{
    $converter = new AS2StreamConverter();
    $expanded = $converter->toJf2($as2);

    return json_encode($expanded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
