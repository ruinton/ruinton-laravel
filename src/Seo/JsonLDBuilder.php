<?php


namespace App\Classes\Seo;


use Artesaos\SEOTools\Facades\JsonLdMulti;

class JsonLDBuilder
{
    /**
     * @var AbstractSchema[]
     */
    private $schemas = [];

    public function withSchema(AbstractSchema $schema) {
        array_push($this->schemas, $schema);
        return $this;
    }

    public function build() {
        foreach ($this->schemas as $schema) {
            JsonLdMulti::newJsonLd();
            $schema->generate();
        }
    }
}
