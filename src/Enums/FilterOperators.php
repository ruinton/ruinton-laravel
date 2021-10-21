<?php


namespace Ruinton\Enums;


final class FilterOperators
{
    public const EQUALS = '=';
    public const NOT_EQUALS = '<>';
    public const LESS_THAN = '<';
    public const GRATER_THAN = '>';
    public const LESS_THAN_EQUAL = '<=';
    public const GRATER_THAN_EQUAL = '>=';

    public const HAS = 'has';
    public const IN = 'in';
    public const NOT_IN = 'not in';
    public const LIKE = 'like';

    public const IS_NULL = 'is null';
    public const IS_NOT_NULL = 'is not null';
    public const CLOSURE = 'closure';

    public const FilterNames = [
        'eq' => self::EQUALS,
        'neq' => self::NOT_EQUALS,
        'lt' => self::LESS_THAN,
        'gt' => self::GRATER_THAN,
        'lte' => self::GRATER_THAN_EQUAL,
        'gte' => self::GRATER_THAN_EQUAL,
        'has' => self::HAS,
        'in' => self::IN,
        'nin' => self::NOT_IN,
        'like' => self::LIKE,
        'isnull' => self::IS_NULL,
        'isnotnull' => self::IS_NOT_NULL,
        'closure' => self::CLOSURE,
    ];

    public static function getOperatorByFilterName($name) {
        try{
            dump($name);
            return self::FilterNames[$name];
        }catch (\Exception $e) {
            return self::LIKE;
        }
    }
}
