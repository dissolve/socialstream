<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
?>
{
  "@context": {
    "mf": "http://microformats.org/wiki/",
    "jf2": "https://github.com/w3c-social/Social-Syntax-Brainstorming/wiki/jf2#",
    "iwc": "http://indiewebcamp.com/",
    "url": "@id",
    "type": "@type",
    "lang": "@language",

    //jf2 reserve words
    "children": "jf2:children",
    "content-type": "jf2:content-type",
    "value": "jf2:value",

    //jf2 reserve values
    "image": "jf2:image",

    //base mf2 objects
    "adr": "mf:h-adr",
    "card": "mf:h-card",
    "entry": "mf:h-entry",
    "event": "mf:h-event",
    "feed": "mf:h-feed",
    "geo": "mf:h-geo",
    "item": "mf:h-item",
    "listing": "mf:h-listing",
    "product": "mf:h-product",
    "recipe": "mf:h-recipe",
    "resume": "mf:h-resume",
    "review": "mf:h-review",
    "review-aggregate": "mf:h-review-aggregate",
    "cite": "mf:h-cite",

    //base mf/mf2 attibutes
    "additional-name": "mf:microformats2#p-additional-name",
    "adr": "mf:microformats2#p-adr",
    "affiliation": "mf:microformats2#p-affiliation",
    "altitude": "mf:microformats2#p-altitude",
    "anniversary": "mf:microformats2#dt-anniversary",
    "audio": "mf:microformats2#u-audio",
    "author": "mf:microformats2#p-author",
    "author": "mf:microformats2#p-author",
    "bday": "mf:microformats2#dt-bday",
    "best": "mf:microformats2#p-best",
    "brand": "mf:microformats2#p-brand",
    "category": "mf:microformats2#p-category",
    "contact": "mf:microformats2#p-contact",
    "content": "mf:microformats2#e-content",
    "count": "mf:microformats2#p-count",
    "country-name": "mf:microformats2#p-country-name",
    "description": "mf:microformats2#p-description",
    "duration": "mf:microformats2#dt-duration",
    "education": "mf:microformats2#p-education",
    "email": "mf:microformats2#u-email",
    "end": "mf:microformats2#dt-end",
    "experience": "mf:microformats2#p-experience",
    "extended-address": "mf:microformats2#p-extended-address",
    "family-name": "mf:microformats2#p-family-name",
    "gender-identity": "mf:microformats2#p-gender-identity",
    "geo": "mf:microformats2#p-geo",
    "given-name": "mf:microformats2#p-given-name",
    "honorific-prefix": "mf:microformats2#p-honorific-prefix",
    "honorific-suffix": "mf:microformats2#p-honorific-suffix",
    "identifier": "mf:microformats2#u-identifier",
    "impp": "mf:microformats2#u-impp",
    "in-reply-to": "mf:microformats2#u-in-reply-to",
    "ingredient": "mf:microformats2#p-ingredient",
    "instructions": "mf:microformats2#e-instructions",
    "item": "mf:microformats2#p-item",
    "job-title": "mf:microformats2#p-job-title",
    "key": "mf:microformats2#u-key",
    "label": "mf:microformats2#p-label",
    "latitude": "mf:microformats2#p-latitude",
    "locality": "mf:microformats2#p-locality",
    "location": "mf:microformats2#p-location",
    "logo": "mf:microformats2#u-logo",
    "longitude": "mf:microformats2#p-longitude",
    "name": "mf:microformats2#p-name",
    "nickname": "mf:microformats2#p-nickname",
    "note": "mf:microformats2#p-note",
    "nutrition": "mf:microformats2#p-nutrition",
    "org": "mf:microformats2#p-org",
    "organization-name": "mf:microformats2#p-organization-name",
    "organization-unit": "mf:microformats2#p-organization-unit",
    "photo": "mf:microformats2#u-photo",
    "post-office-box": "mf:microformats2#p-post-office-box",
    "postal-code": "mf:microformats2#p-postal-code",
    "price": "mf:microformats2#p-price",
    "published": "mf:microformats2#dt-published",
    "rating": "mf:microformats2#p-rating",
    "region": "mf:microformats2#p-region",
    "rev": "mf:microformats2#dt-rev",
    "review": "mf:microformats2#p-review",
    "reviewed": "mf:microformats2#dt-reviewed",
    "reviewer": "mf:microformats2#p-reviewer",
    "role": "mf:microformats2#p-role",
    "sex": "mf:microformats2#p-sex",
    "skill": "mf:microformats2#p-skill",
    "sort-string": "mf:microformats2#p-sort-string",
    "start": "mf:microformats2#dt-start",
    "street-address": "mf:microformats2#p-street-address",
    "summary": "mf:microformats2#p-summary",
    "tel": "mf:microformats2#p-tel",
    "tz": "mf:microformats2#p-tz",
    "uid": "mf:microformats2#u-uid",
    "updated": "mf:microformats2#dt-updated",
    "video": "mf:microformats2#u-video",
    "votes": "mf:microformats2#p-votes",
    "worst": "mf:microformats2#p-worst",
    "yield": "mf:microformats2#p-yield",

    //h-cite specific mf2
    "publication": "mf:h-cite#publication",
    "accessed": "mf:h-cite#dt-accessed",

    // mf back compat aliasing
    "dtend": "end",
    "dtstart": "start",
    "entry-title": "name",
    "fn": "name",

    //rel values that have alternates in mf
    "in-reply-to": "mf:rel-in-reply-to",
    "syndication": "mf:rel-syndication",
    "shortlink": "mf:rel-shortlink",

    //draft properties from IWC
    "rsvp": "mf:rsvp",


    // mf2 experimental
    "x-dietary-preference": "mf:microformats2-experimental-properties#p-x-dietary-preference",
    "x-pronoun-nominative": "mf:microformats2-experimental-properties#p-x-pronoun-nominative",
    "x-pronoun-oblique": "mf:microformats2-experimental-properties#p-x-pronoun-oblique",
    "x-pronoun-possessive": "mf:microformats2-experimental-properties#p-x-pronoun-posessive",
    "x-sexual-preference": "mf:microformats2-experimental-properties#p-x-sexual-preference",
    "x-username": "mf:microformats2-experimental-properties#h-x-username",

    // iwc properties
    "comment": "iwc:comments",
    "like-of": "iwc:like",
    "repost-of": "iwc:repost",
    "invitee": "iwc:invite"

  }
}

