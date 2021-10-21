<?php


namespace App\Classes\Seo\Schema;


use App\Classes\Seo\AbstractSchema;
use Artesaos\SEOTools\Facades\JsonLdMulti;

class OrganizationSchema extends AbstractSchema
{
    protected $type;
    protected $name;
    protected $alternateName;
    protected $url;
    protected $logo;
    protected $numbers;
    protected $socialLinks;

    public function __construct(
        $type           = "CollegeOrUniversity",
        $name           = "مجتمع آموزشی پل گیلان",
        $alternateName  = "پل گیلان",
        $url            = "https://poulgilan.com/",
        $logo           = "https://poulgilan.com/assets/img/POUL-logo-new.png",
        $numbers        = ["01333333145", "01333343030", "01333343047", "01333343048"],
        $socialLinks    = [
            "https://www.instagram.com/poulgilan/",
            "https://www.youtube.com/channel/UCwQfNHUNsWvQnGTfxML-WAA/feed",
            "https://www.pinterest.com/poulgilan/",
            "https://poulgilan.com/",
            "https://www.facebook.com/PoulGilan/",
            "https://www.linkedin.com/company/poul-gilan"
        ]
    )
    {
        $this->type = $type;
        $this->name = $name;
        $this->alternateName = $alternateName;
        $this->url = $url;
        $this->logo = $logo;
        $this->numbers = $numbers;
        $this->socialLinks = $socialLinks;
    }

    protected function generateScheme()
    {
        JsonLdMulti::addValue("@type", $this->type);
        JsonLdMulti::addValue("name", $this->name);
        JsonLdMulti::addValue("alternateName", $this->alternateName);
        JsonLdMulti::setUrl($this->url);
        JsonLdMulti::addValue("logo", $this->logo);
        $contacts = [];
        foreach ($this->numbers as $number) {
            array_push($contacts, [
                "@type"             => "ContactPoint",
                "telephone"         => $number,
                "contactType"       => "customer service",
                "areaServed"        => "IR",
                "availableLanguage" => "Persian"
            ]);
        }
        JsonLdMulti::addValue("contactPoint", $contacts);
        JsonLdMulti::addValue("sameAs", $this->socialLinks);
    }
}

//[
//    "@context"      => "https://schema.org",
//    "@type"         => "CollegeOrUniversity",
//    "name"          => "مجتمع آموزشی پل گیلان",
//    "alternateName" => "پل گیلان",
//    "url"           => "https://poulgilan.com/",
//    "logo"          => "https://poulgilan.com/assets/img/POUL-logo-new.png",
//    "contactPoint"  => [[
//        "@type"             => "ContactPoint",
//        "telephone"         => "01333333145",
//        "contactType"       => "customer service",
//        "areaServed"        => "IR",
//        "availableLanguage" => "Persian"
//    ],[
//        "@type"             => "ContactPoint",
//        "telephone"         => "01333343030",
//        "contactType"       => "customer service",
//        "areaServed"        => "IR",
//        "availableLanguage" => "Persian"
//    ],[
//        "@type"             => "ContactPoint",
//        "telephone"         => "01333343047",
//        "contactType"       => "customer service",
//        "areaServed"        => "IR",
//        "availableLanguage" => "Persian"
//    ],[
//        "@type"             => "ContactPoint",
//        "telephone"         => "01333343048",
//        "contactType"       => "customer service",
//        "areaServed"        => "IR",
//        "availableLanguage" => "Persian"
//    ]],
//    "sameAs"  => [
//        "https://www.instagram.com/poulgilan/",
//        "https://www.youtube.com/channel/UCwQfNHUNsWvQnGTfxML-WAA/feed",
//        "https://www.pinterest.com/poulgilan/",
//        "https://poulgilan.com/",
//        "https://www.facebook.com/PoulGilan/"
//    ]
//]
