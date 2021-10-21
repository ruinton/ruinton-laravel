<?php


namespace Ruinton\Enums;


final class YesNoStatus
{
    public const YES = 'دارد';
    public const NO = 'ندارد';

    public static function FindByIndex(int $index)
    {
        switch ($index) {
            case 0:
                return YesNoStatus::NO;
            case 1:
                return YesNoStatus::YES;
        }
    }
}
