<?php


namespace App\Classes\Seo\Schema;


use App\Classes\Seo\AbstractSchema;
use Artesaos\SEOTools\Facades\JsonLdMulti;

class CourseSchema extends AbstractSchema
{
    protected $type;
    protected $name;
    protected $description;
    protected $provider;

    public function __construct($name, $description, $provider = [
        "@type" => "Organization",
        "name" => "مجتمع آموزشی پل گیلان",
        "sameAs" => "https://poulgilan.com/"
    ])
    {
        $this->type = "Course";
        $this->name = $name;
        $this->description = $description;
        $this->provider = $provider;
    }

    protected function generateScheme()
    {
        JsonLdMulti::addValue("@type", $this->type);
        JsonLdMulti::addValue("name", $this->name);
        JsonLdMulti::addValue("description", $this->description);
        JsonLdMulti::addValue("provider", $this->provider);
    }
}

//{
//    "@context": "http://schema.org/",
//   "@type":"Course",
//   "name":"آموزش تعمیرات موبایل",
//   "description":"در دوره آموزش تعمیرات موبایل شما به صورت کامل تعمیرات سخت افزار و نرم افزاری انواع موبایل ها را فرا میگیرید و در پایان این دوره شما به یک متخصص حرفه ای تعمیرات مویایل تبدیل میشود و میتوانید کسب و کار خود را راه اندازی نمایید ",
//   "provider":{
//    "@type":"Organization",
//       "name":"مجتمع آموزشی پل گیلان",
//       "sameAs":"https://poulgilan.com/"
//   }
//}
