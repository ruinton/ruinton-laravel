<?php

namespace Ruinton\Geo\Migration;

use Illuminate\Support\Facades\DB;

class TimescaleSchema
{
    public static function createHypertable($name, ...$keys) {
        DB::statement("SELECT create_hypertable('$name','".implode("','", $keys)."')");
    }
    public static function createHypertableWithChunkSize($name, $size, ...$keys) {
        DB::statement("SELECT create_hypertable('$name','".implode("','", $keys)."', chunk_time_interval => $size)");
    }
}
