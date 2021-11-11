<?php

namespace Ruinton\Geo\Migration;

use Illuminate\Database\Schema\Blueprint;

class TimescaleBlueprint
{

    public function createHypertable($name, ...$keys) {

    }

    public static function installMacro() {
        $timescaleBlueprint = new TimescaleBlueprint();
        Blueprint::macro('createHypertable', function ($name, ...$keys) use ($timescaleBlueprint) {
            $timescaleBlueprint->createHypertable($name, ...$keys);
        });
    }
}
