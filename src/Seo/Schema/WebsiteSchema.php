<?php


namespace App\Classes\Seo\Schema;


use App\Classes\Seo\AbstractSchema;
use Artesaos\SEOTools\Facades\JsonLdMulti;

class WebsiteSchema extends AbstractSchema
{
    protected $name;
    protected $url;

    /**
     * WebsiteSchema constructor.
     * @param $name
     * @param $url
     * @param $actions
     */
    public function __construct(
        $name = "مجتمع آموزشی پل گیلان",
        $url = "https://poulgilan.com/"
    )
    {
        $this->name = $name;
        $this->url = $url;
    }


    protected function generateScheme()
    {
        JsonLdMulti::addValue("@type", "WebSite");
        JsonLdMulti::addValue("name", $this->name);
        JsonLdMulti::setUrl($this->url);
        JsonLdMulti::addValue("potentialAction", [
            "@type"         =>"SearchAction",
            "target"        => "https://poulgilan.com/search?keyword={search_term_string}",
            "query-input"   => "required name=search_term_string"
        ]);
    }
}

//{
//    "@context": "https://schema.org/",
//  "@type": "WebSite",
//  "name": "مجتمع آموزشی پل گیلان",
//  "url": "https://poulgilan.com/",
//  "potentialAction": {
//    "@type": "SearchAction",
//    "target": "https://poulgilan.com/search?keyword={search_term_string}",
//    "query-input": "required name=search_term_string"
//  }
//}
