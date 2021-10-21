<?php
/**
 * Created by PhpStorm.
 * User: R_Alizadeh
 * Date: 3/18/2019
 * Time: 8:59 AM
 */

namespace App\Classes\Helpers\Uploader;


class UploadResult
{
    /**
     * @var bool $status
     */
    private $status;
    private $imageSize;
    private $thumbSize;

    /**
     * @var string $message
     */
    private $message;

    public function __construct(bool $status, string $message, $imageSize = 0, $thumbSize = 0)
    {
        $this->status = $status;
        $this->message = $message;
        $this->imageSize = $imageSize;
        $this->thumbSize = $thumbSize;
    }

    /**
     * @return bool
     */
    public function hasUploaded(): bool
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getImageSize(): int
    {
        return $this->imageSize;
    }

    /**
     * @return int
     */
    public function getThumbSize(): int
    {
        return $this->thumbSize;
    }
}
