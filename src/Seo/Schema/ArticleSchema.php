<?php


namespace App\Classes\Seo\Schema;


use App\Classes\Seo\AbstractSchema;
use Artesaos\SEOTools\Facades\JsonLdMulti;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Model;

class ArticleSchema extends AbstractSchema
{
    protected $model;
    protected $descriptionField, $nameField;

    public function __construct(Model $model, $nameField="name", $descriptionField="meta_description")
    {
        $this->model = $model;
        $this->nameField = $nameField;
        $this->descriptionField = $descriptionField;
    }

    protected function generateScheme()
    {
        JsonLdMulti::addValue("@type", "Article");
        JsonLdMulti::addValue("mainEntityOfPage", [
            "@type" => "WebPage",
            "@id"   => route("blog.post", ["id" => $this->model['id'], "name" => urlencode($this->model['meta_url'])])
        ]);
        JsonLdMulti::addValue("headline", $this->model[$this->nameField]);
        JsonLdMulti::addValue("description", $this->model[$this->descriptionField]);
        JsonLdMulti::addValue("image", count($this->model['media']) > 0 ? $this->model['media'][0]['url'] : '');
        JsonLdMulti::addValue("author", [
            "@type" => "Organization",
            "name"  => "مجتمع آموزشی پل گیلان"
        ]);
        JsonLdMulti::addValue("publisher", [
            "@type" => "Organization",
            "name"  => "https://poulgilan.com/",
            "logo"  => [
                "@type" => "ImageObject",
                "url"   => "https://poulgilan.com/assets/img/POUL-logo-new.png"
            ]
        ]);
        JsonLdMulti::addValue("datePublished", Verta::parse($this->model['created_at'])->formatGregorian('Y-m-d'));
        JsonLdMulti::addValue("dateModified", Verta::parse($this->model['created_at'])->formatGregorian('Y-m-d'));
    }
}

//"@context": "https://schema.org",
//  "@type": "Article",
//  "mainEntityOfPage": {
//    "@type": "WebPage",
//    "@id": "https://poulgilan.com/blog/post/24/%D9%85%D8%B1%D8%A7%D9%82%D8%A8%D8%AA-%D8%A7%D8%B2-%D8%A8%D8%A7%D8%AA%D8%B1%DB%8C-%D9%85%D9%88%D8%A8%D8%A7%DB%8C%D9%84"
//  },
//  "headline": "نکات مهم برای مراقبت و نگهداری از باتری موبایل",
//  "description": "description",
//  "image": "https://poulgilan.com/userfiles/images/blog/power-bank-mobile-phone.jpg",
//  "author": {
//    "@type": "Organization",
//    "name": "مجتمع آموزشی پل گیلان"
//  },
//  "publisher": {
//    "@type": "Organization",
//    "name": "https://poulgilan.com/",
//    "logo": {
//        "@type": "ImageObject",
//      "url": "https://poulgilan.com/assets/img/POUL-logo-new.png"
//    }
//  },
//  "datePublished": "2020-12-02",
//  "dateModified": "2020-12-02"
//}
