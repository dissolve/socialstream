<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
include('jf2.jsonld');
/*
    * need to look at each of these that were removed more closely
{
  "@context": [
    "http://www.w3.org/ns/activitystreams", 
    {
      "mf": "http://microformats.org/wiki/",
      "vcard": "http://www.w3.org/2006/vcard/ns#",
      "jf2": "https://github.com/w3c-social/Social-Syntax-Brainstorming/wiki/jf2#",
      "iwc": "http://indiewebcamp.com/",

<?php // aliases ?>
    "url": "@id",
    "type": "@type",
    "lang": "@language",
    "content-type": "@type",
    "value": "@value",

    "children": "jf2:children",

    "image": "jf2:image",

    "Geo": "mf:h-geo",

<?php //base mf/mf2 attibutes ?>
    "altitude": "mf:microformats2#p-altitude",
    "author": "mf:microformats2#p-author",
    "category": "mf:microformats2#p-category",
    "content": "mf:microformats2#e-content",
    "description": "mf:microformats2#p-description",
    "duration": "mf:microformats2#dt-duration",
    "end": "mf:microformats2#dt-end",
    "in-reply-to": "mf:microformats2#u-in-reply-to",
    "item": "mf:microformats2#p-item",

    "latitude": "mf:microformats2#p-latitude",
    "location": "mf:microformats2#p-location",
    "logo": "mf:microformats2#u-logo",

    "longitude": "mf:microformats2#p-longitude",
    "name": "mf:microformats2#p-name",
    "organization-name": "vcard:organization-name",
    "photo": "mf:microformats2#u-photo",
    "published": "mf:microformats2#dt-published",
    "start": "mf:microformats2#dt-start",
    "summary": "mf:microformats2#p-summary",
    "updated": "mf:microformats2#dt-updated",


<?php // mf back compat aliasing ?>
    "dtend": "end",
    "dtstart": "start",
    "entry-title": "name",

}
 */
