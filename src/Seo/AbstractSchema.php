<?php


namespace App\Classes\Seo;
use Artesaos\SEOTools\Facades\JsonLdMulti;

abstract class AbstractSchema
{
    public function generate() {
        JsonLdMulti::addValue("@context", "https://schema.org");
        $this->generateScheme();
    }

    protected abstract function generateScheme();
}
