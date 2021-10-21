<?php


namespace Ruinton\Enums;


final class MimeTypeIndexes
{
    public const TEXT_ALL               = 1;
    public const TEXT_PLAIN             = 2;
    public const TEXT_COMMA_SEPARATED   = 3;

    public const IMAGE_ALL              = 4;
    public const IMAGE_JPEG             = 5;
    public const IMAGE_PNG              = 6;
    public const IMAGE_X_PNG            = 7;
    public const IMAGE_GIF              = 8;

    public const AUDIO_ALL              = 9;
    public const AUDIO_MPEG             = 10;
    public const AUDIO_OGG              = 11;
    public const AUDIO_MP4              = 12;

    public const VIDEO_ALL              = 13;
    public const VIDEO_3GP              = 14;
    public const VIDEO_3GP2             = 15;
    public const VIDEO_MP4              = 16;
    public const VIDEO_AVI              = 17;

    public const APPLICATION_MP4        = 18;
    public const APPLICATION_ZIP        = 19;
    public const APPLICATION_PDF        = 20;
    public const APPLICATION_SQL        = 21;
    public const APPLICATION_EXCEL      = 22;
    public const APPLICATION_WORD       = 23;
    public const APPLICATION_POWERPOINT = 24;
    public const APPLICATION_ACCESS     = 25;
    public const APPLICATION_VISIO      = 26;
    public const APPLICATION_EXCELX     = 27;
    public const APPLICATION_WORDX      = 28;
    public const APPLICATION_POWERPOINTX= 29;
    public const APPLICATION_ACCESSX    = 30;

    public final static function getIndexByName(string $mimeName)
    {
        $mimeClass = new \ReflectionClass(MimeTypes::class);
        $index = array_search($mimeName, array_values($mimeClass->getConstants()));
        if($index === false)
        {
            return null;
        }
        return $index + 1;
    }

}
