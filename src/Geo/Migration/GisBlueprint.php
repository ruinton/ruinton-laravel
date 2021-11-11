<?php

namespace Ruinton\Geo\Migration;

use Illuminate\Database\Schema\Blueprint;

class GisBlueprint
{

    public function point($name, $hasZ = false) {

    }

    public static function installMacro() {
        $gisBlueprint = new GisBlueprint();
        Blueprint::macro('point', function ($name, $hasZ = false) use ($gisBlueprint) {
            $gisBlueprint->point($name, $hasZ);
        });
    }
}
