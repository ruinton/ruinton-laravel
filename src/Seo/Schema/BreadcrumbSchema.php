<?php


namespace App\Classes\Seo\Schema;


use App\Classes\Seo\AbstractSchema;
use Artesaos\SEOTools\Facades\JsonLdMulti;

class BreadcrumbSchema extends AbstractSchema
{
    protected $breadcrumbs;

    /**
     * BreadcrumbSchema constructor.
     * @param $breadcrumbs
     * @param bool $mainNav
     */
    public function __construct($breadcrumbs, $mainNav = true)
    {
        $this->breadcrumbs = $breadcrumbs;
        if($mainNav) {
            array_unshift($this->breadcrumbs,[
                "name"  => "صفحه اصلی",
                "url"   => "https://poulgilan.com/"
            ]);
        }
    }


    protected function generateScheme()
    {
        JsonLdMulti::addValue("@type", "BreadcrumbList");
        $navs = [];
        foreach ($this->breadcrumbs as $key => $breadcrumb) {
            array_push($navs, [
                "@type"     => "ListItem",
                "position"  => $key + 1,
                "name"      => $breadcrumb['name'],
                "item"      => $breadcrumb['url']
            ]);
        }
        JsonLdMulti::addValue("itemListElement", $navs);
    }
}

//{
//    "@context": "https://schema.org/",
//  "@type": "BreadcrumbList",
//  "itemListElement": [{
//    "@type": "ListItem",
//    "position": 1,
//    "name": "صفحه اصلی",
//    "item": "https://poulgilan.com/"
//  },{
//    "@type": "ListItem",
//    "position": 2,
//    "name": "بلاگ",
//    "item": "https://poulgilan.com/blog"
//  },{
//    "@type": "ListItem",
//    "position": 3,
//    "name": "مراقبت از باتری موبایل",
//    "item": "https://poulgilan.com/blog/مراقبت-از-باتری-موبایل"
//  }]
//}
