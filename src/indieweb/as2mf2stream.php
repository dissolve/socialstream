<?php
namespace IndieWeb\as2mf2stream;


class AS2MF2StreamConverter
{
    private $url_base;
    private function buildActivity($in)
    {
        $result = array();

        if(isset($in['author'])){
            $author = $in['author'];
            if(is_array($author)){
                $aid = $author['url'];

                if(isset($author['uid'])){
                    $aid = $author['uid'];
                }
                $author_name = $author['name'];

                $actor = array(
                    'type' => "Person",
                    'id' => $aid,
                    'url' => $aid,
                    'name' => $author_name
                );
            } else {
                $author_name = 'Someone';
                $actor = $author;
            } 
        }

        if(isset($in['published'])){
            $result['published'] = $in['published'];
        }
        if(isset($in['updated'])){
            $result['updated'] = $in['updated'];
        }
        if(isset($in['uid'])){
            $result['id'] = $in['uid'];
        }
        if(isset($in['url'])){
            if(is_array($in['url'])){
                $result['id'] = $in['url'][0];
            } else {
                $result['id'] = $in['url'];
            }
            $result['url'] = $in['url'];
        }

        // todo recognize type
        if(isset($in['rsvp'])){

            if( $in['rsvp'] == 'Yes' || $in['rsvp'] == 'YES' || $in['rsvp'] == 'yes'){
                $result['type'] = 'Accept';
                $result['name'] = $author_name . ' accepted an invitation';
                $result['object'] = array('type' => 'Event', 'id' => $in['in-reply-to']);

            } elseif( $in['rsvp'] == 'No' || $in['rsvp'] == 'NO' || $in['rsvp'] == 'no'){
                $result['type'] = 'Reject';
                $result['name'] = $author_name . ' rejected an invitation';

            } else {
                $result['type'] = 'TentativeAccept';
                $result['name'] = $author_name . ' responded maybe to an invitation';
            }
            $result['actor'] = $actor;
            $result['object'] = array('type' => 'Invite', 
                        'object' => array('type' => 'Event', 'id' => $in['in-reply-to']));


        } elseif(isset($in['in-reply-to'])){

            $object = array();
            $object['type'] = 'Note';
            $object['inReplyTo'] = $in['in-reply-to']; //only if this is just a url
            if(isset($in['content'])){
                if(isset($in['content']['value'])){
                    $object['content'] = $in['content']['value'];
                    $object['name'] = $in['content']['value'];
                } else {
                    $object['content'] = $in['content'];
                    $object['name'] = $in['content'];
                }
                if(isset($in['content']['content-type'])){
                    $object['mediaType'] = $in['content']['content-type'];
                }
            }

            if(isset($in['url'])){
                if(is_array($in['url'])){
                    $object['id'] = $in['url'][0];
                } else {
                    $object['id'] = $in['url'];
                }
                $object['url'] = $in['url'];
            }
            if(isset($in['uid'])){
                $object['id'] = $in['uid'];
            }

            $result['type'] = "Create";
            $result['name'] = $author_name . ' created a reply';
            $result['actor'] = $actor;
            $result['object'] = $object;

        } elseif(isset($in['like-of'])){

            $result['type'] = 'Like';
            $result['name'] = $author_name . ' liked a post';
            $result['actor'] = $actor;
            $result['object'] = $in['like-of']; //only if this is just a url

        } else {

            $object = array();
            $object['type'] = 'Note';
            if(isset($in['content'])){
                if(isset($in['content']['value'])){
                    $object['content'] = $in['content']['value'];
                    $object['name'] = $in['content']['value'];
                } else {
                    $object['content'] = $in['content'];
                    $object['name'] = $in['content'];
                }
                if(isset($in['content']['content-type'])){
                    $object['mediaType'] = $in['content']['content-type'];
                }
            }

            if(isset($in['url'])){
                if(is_array($in['url'])){
                    $object['id'] = $in['url'][0];
                } else {
                    $object['id'] = $in['url'];
                }
                $object['url'] = $in['url'];
            }
            if(isset($in['uid'])){
                $object['id'] = $in['uid'];
            }
            $result['type'] = "Create";
            $result['name'] = $author_name . ' created a note';
            $result['actor'] = $actor;
            $result['object'] = $object;
        }

        if(isset($in['comment'])){
            $result['replies'] = array();
            $result['replies']['type'] = "Collection";
            $result['replies']['name'] = "Responses";
            $result['replies']['items'] = array();

            foreach($in['comment'] as $comment){
                $result['replies']['items'] = $this->buildActivity($comment);

            }
        }


        return $result ;

    }
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

    private function toAs2_recurse($cleaned)
    {
        $as2 = array();

        if(isset($cleaned['type']) && $cleaned['type'] == 'entry'){
            $as2 = $this->buildActivity($cleaned);

        } elseif(isset($cleaned['type']) && $cleaned['type'] == 'feed'){
            $as2['type'] = "Collection";
            $as2['name'] = "A Collection";
            if(isset($cleaned['url'])){
                $as2['id'] = $cleaned['url'];
            }
            if(isset($cleaned['uid'])){
                $as2['id'] = $cleaned['uid'];
            }
            $as2['items'] = array();
            foreach($cleaned['children'] as $item){
                $child = $this->toAs2_recurse($item);
                if(!empty($child)){
                    $as2['items'][] = $child;
                }
            }
            if(count($as2['items']) == 1){
                $as2 = $as2['items'][0];
            }
        } elseif(is_array($cleaned) && !isset($cleaned['type'])) {
            $as2['type'] = "Collection";
            $as2['name'] = "A Collection";
            $as2['items'] = array();
            foreach($cleaned as $item){
                $child = $this->toAs2_recurse($item);
                if(!empty($child)){
                    $as2['items'][] = $child;
                }
            }
            if(count($as2['items']) == 1){
                $as2 = $as2['items'][0];
            }

        }



        return $as2;

    }

    public function toAs2($mf, $base_url = "")
    {
        $this->url_base = $base_url ;
        $cleaned = $this->cleanNode($mf);
        $as2 = array('@context' => "http://www.w3.org/ns/activitystreams");

        $as2 = array_merge($as2, $this->toAs2_recurse($cleaned));


        return $as2;

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
                $result .= $this->object2mf2($val, array('p-' . $key));
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
            $returned = $this->object2mf2($child);
            if($returned){
                $result[] = $returned;
            }
        }
        return $result;
    }


    private function object2mf2($data, $classes = array())
    {
        if(!is_array($data)){
            return $data;
        }
        $result = array();
        if (isset($data['nameMap'])) {
            $data['name'] = $data['nameMap']['en'];
            //todo: pass in language or we could just pick one randomly
            unset($data['nameMap']);
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
            unset($data['id']);
        }
        if (isset($data['@id'])){
            $result['url'] = $data['@id'];
            unset($data['@id']);
        }
        
        if (isset($data['actor'])){
            $result['author'] = $this->object2mf2($data['actor']);
        }
        if (isset($data['attributedTo'])){
            $result['author'] = $this->object2mf2($data['attributedTo']);
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
                            if(!isset($data['url'])){
                                $result['url'] = $data['object']['id'];
                            }
                        }
                    }
                }

            }elseif($data['type'] == 'Create'){
                $result['type'] = 'entry';
                if (isset($data['object'])) {
                    if(!is_array($data['object'])) {
                        $result['url'] = $data['object'];
                    } elseif (isset($data['object']['id'])){
                        $result['url'] = $data['object']['id'];
                    }
                }

            }elseif($data['type'] == 'Like'){
                $result['type'] = 'entry';
                if (isset($data['object'])) {
                    if(!is_array($data['object'])) {
                        $result['like-of'] = $data['object'];
                    } elseif (isset($data['object']['id'])){
                        $result['like-of'] = $data['object']['id'];
                    }
                }

            }elseif($data['type'] == 'Person'){
                $result['type'] = 'card';

            }elseif($data['type'] == 'Note'){
                $result['type'] = 'entry';
                if(isset($result['name'])){
                    $result['content'] = $result['name'];
                    unset($result['name']);
                }
            } else {
                //return null;  //Note: this is one option and would just remove all unrecognized types,
                                // will probably do this after other things have been formatted correctly
                $result['type'] = 'as2-' . $data['type'];
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
    public function toMf2($js)
    {
        $data = json_decode($js, true);

        $result = $this->object2mf2($data);

        return $result;
    }
}

function mf2_to_as2($mf2, $base_url = "")
{
    $converter = new AS2MF2StreamConverter();
    $cleaned = $converter->toAs2($mf2, $base_url);

    return json_encode($cleaned, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

function as2_to_mf2($as2)
{
    $converter = new AS2MF2StreamConverter();
    $expanded = $converter->toMf2($as2);

    return json_encode($expanded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
