<?php

namespace Ruinton\Geo\Migration;

use Illuminate\Support\Facades\DB;

class PostGisSchema
{
    public static function createGeometryPoint($table, $name) {
        DB::statement("ALTER TABLE $table ADD COLUMN $name geometry(Point,4326)");
    }

    public static function createGeometryLineString($table, $name) {
        DB::statement("ALTER TABLE $table ADD COLUMN $name geometry(LineString,4326)");
    }

    public static function createGeometryPolygon($table, $name) {
        DB::statement("ALTER TABLE $table ADD COLUMN $name geometry(POLYGON,4326)");
    }

    public static function createGeometryMultiPolygon($table, $name) {
        DB::statement("ALTER TABLE $table ADD COLUMN $name geometry(MULTIPOLYGON,4326)");
    }

    public static function createGistIndex($table, $name, ...$columns) {
        DB::statement("CREATE INDEX {$table}_{$name}_idx ON $table USING GIST (".implode(',', $columns).");");
    }

    public static function createBrinIndex($table, $name, ...$columns) {
        DB::statement("CREATE INDEX {$table}_{$name}_idx ON $table USING BRIN (".implode(',', $columns).");");
    }
}
