<?php
namespace IndieWeb\socialstream;


class StreamCleaner
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
        //TODO Do something here
        return $url;
    }


    public function clean($mf, $base_url = "", $lang = 'en')
    {
        $url_base = $base_url ;
        $cleaned = $this->cleanNode($mf);
        if ($this->isHash($cleaned)) {
            $cleaned['language'] = $lang;
        }

        return $cleaned;

    }


    /* TODO
     *  in-reply-to
     *  syndication
     *  category
     * are all urls
}


function convert($mf, $base_url = "", $lang = 'en')
{
    $cleaner = new StreamCleaner();
    $cleaned = $cleaner->clean($mf, $base_url, $lang);

    return json_encode($cleaned, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
