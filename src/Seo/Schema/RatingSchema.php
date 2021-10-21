<?php


namespace App\Classes\Seo\Schema;


use App\Classes\Seo\AbstractSchema;
use Artesaos\SEOTools\Facades\JsonLdMulti;

class RatingSchema extends AbstractSchema
{
    protected $type;
    protected $name;
    protected $ratingValue;
    protected $bestRating;
    protected $ratingCount;

    public function __construct(
        $ratingValue,
        $bestRating,
        $ratingCount,
        $name = "مجتمع آموزشی پل گیلان",
        $type = "CreativeWorkSeries"
    )
    {
        $this->type = $type;
        $this->name = $name;
        $this->ratingValue = $ratingValue;
        $this->bestRating = $bestRating;
        $this->ratingCount = $ratingCount;
    }

    protected function generateScheme()
    {
        JsonLdMulti::addValue("@type", $this->type);
        JsonLdMulti::addValue("name", $this->name);
        JsonLdMulti::addValue("aggregateRating", [
            "@type"         => "AggregateRating",
            "ratingValue"   => $this->ratingValue,
            "bestRating"    => $this->bestRating,
            "ratingCount"   => $this->ratingCount
        ]);
    }
}

//"@context": "https://schema.org/",
//"@type": "CreativeWorkSeries",
//"name": "آموزش تعمیرات موبایل",
//"aggregateRating": {
//    "@type": "AggregateRating",
//    "ratingValue": "4.6",
//    "bestRating": "5",
//    "ratingCount": "75"
//}
