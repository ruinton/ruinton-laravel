<?php
namespace App\Classes\Helpers\Uploader;

use App\Classes\Enums\MimeTypes;

class UploadHelper{

    //Config
    private $policy = [
        'maxSize'                => 10000000,     //10 MegaBytes
        'allowedExtensions'      => ["gif", "jpeg", "jpg", "png","pdf","doc","docx","ppt","pptx","zip","rar","xls","xlsx"],
        'imageMaxSize'           => 5000000,      // 5 MegaBytes
        'imageAllowedExtensions' => ["gif", "jpeg", "jpg", "png"],
    ];
    private $compress = [
        'image' => 80,
        'thumbnail' => 60
    ];

    public function uploadImageWithPolicy($file,$target_dir,$file_name=null,$resizeTo=null,$thumb_name=null,
                                $thumb_size=null,$maxSize=0,$allowedExtensions=null) : UploadResult
    {
        if(!isset($file)) return new UploadResult(false, "File not found");
        if(empty($file)) return new UploadResult(false, "File is empty");
        if($file_name==null) $file_name=$file["name"];
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        //Apply size policy
        $maxSize = ($maxSize > 0 ? $maxSize : $this->policy['imageMaxSize']);
        if ($file["size"] > $maxSize) {
            return new UploadResult(false, "File size is larger than limits. Maximum file size allowed : $maxSize bytes");
        }
        //Apply extension policy
        $allowedExtensions = ($allowedExtensions != null ? $allowedExtensions : $this->policy['imageAllowedExtensions']);
        $temp = explode(".", $file["name"]);
        $extension = strtolower(end($temp));
        if ((($file["type"] == MimeTypes::IMAGE_JPEG)
                || ($file["type"] == MimeTypes::IMAGE_PNG)
                || ($file["type"] == MimeTypes::IMAGE_GIF)
                || ($file["type"] == MimeTypes::IMAGE_X_PNG))
            && in_array($extension, $allowedExtensions)) {
            if ($file["error"] > 0) return new UploadResult(false, "There is an error in file");
            else {
                $imageSize = $file['size'];
                $thumbSize = 0;
                $result = "";
                if(empty($resizeTo)){
                    if(!empty($thumb_name) && !empty($thumb_size)){
                        $thumbSize = $this->createThumbnail($file, $thumb_name, $thumb_size, $target_dir);
                        $result = " - Thumbnail created";
                    }
                    if(!move_uploaded_file($file["tmp_name"], $target_dir."/".$file_name.'.'.$extension)){
                        return new UploadResult(false, "Unknown error. File not uploaded");
                    }
                    $result = "Image uploaded".$result;
                }
                else{
                    list($swidth, $sheight, $stype, $sattr) = getimagesize($file['tmp_name']);
                    $width=$resizeTo;
                    $height=$sheight*($width/$swidth);
                    if($file["type"]==MimeTypes::IMAGE_PNG || $file["type"]==MimeTypes::IMAGE_X_PNG){
                        $bg = imagecreatefrompng($file['tmp_name']);
                        $image = imagecreatetruecolor(imagesx($bg), imagesy($bg));
                        imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
                        imagealphablending($image, TRUE);
                        imagecopy($image, $bg, 0, 0, 0, 0, imagesx($bg), imagesy($bg));
                        imagedestroy($bg);
                    }else if($file["type"]==MimeTypes::IMAGE_JPEG){
                        $image = imagecreatefromjpeg($file['tmp_name']);
                    }else{
                        return new UploadResult(false, "File format is not valid. Valid formats are: "
                            .implode(", ", $this->policy['imageAllowedExtensions']));
                    }
                    $tn = imagecreatetruecolor($width, $height);
                    imagecopyresampled($tn, $image, 0, 0, 0, 0, $width, $height, $swidth, $sheight);
                    imagejpeg($tn, $target_dir."/".$file_name, $this->compress['image']);
                    $imageSize = filesize($target_dir."/".$file_name);
                    $result = "Image optimized and uploaded";
                    if(!empty($thumb_name) && !empty($thumb_size)){
                        $thumbSize = $this->createThumbnail($file, $thumb_name, $thumb_size, $target_dir, $image);
                        $result .= " - Thumbnail created";
                    }
                }
                return new UploadResult(true, $result, $imageSize, $thumbSize);
            }
        }
        else
        {
            return new UploadResult(false, "File format is not valid. Valid formats are: "
                .implode(", ", $this->policy['imageAllowedExtensions']));
        }
    }

    public function createThumbnail($file, $thumb_name, $thumb_size, $target_dir, $image = null){
        if($image == null){
            if($file["type"]==MimeTypes::IMAGE_PNG || $file["type"]==MimeTypes::IMAGE_X_PNG){
                $bg = imagecreatefrompng($file['tmp_name']);
                $image = imagecreatetruecolor(imagesx($bg), imagesy($bg));
                imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
                imagealphablending($image, TRUE);
                imagecopy($image, $bg, 0, 0, 0, 0, imagesx($bg), imagesy($bg));
                imagedestroy($bg);
            }else if($file["type"]==MimeTypes::IMAGE_JPEG){
                $image = imagecreatefromjpeg($file['tmp_name']);
            }else{
                return 0;
            }
        }
        list($swidth, $sheight, $stype, $sattr) = getimagesize($file['tmp_name']);
        $width=$thumb_size;
        $height=$sheight*($width/$swidth);
        $tn = imagecreatetruecolor($width, $height);
        imagecopyresampled($tn, $image, 0, 0, 0, 0, $width, $height, $swidth, $sheight);
        imagejpeg($tn, $target_dir."/".$thumb_name, $this->compress['thumbnail']);
        return filesize($target_dir."/".$thumb_name);
    }

    public function uploadFileWithPolicy($file, string $target_dir, string $file_name=null, int $maxSize=0, array $allowedExtensions = null) : UploadResult
    {
        if(!isset($file)) return new UploadResult(false, "File not found");
        if(empty($file)) return new UploadResult(false, "File is empty");
        if($file_name==null) $file_name=$file["name"];
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        //Apply size policy
        $maxSize = ($maxSize > 0 ? $maxSize : $this->policy['maxSize']);
        if ($file["size"] > $maxSize) {
            return new UploadResult(false, "File size is larger than limits. Maximum file size allowed : $maxSize");
        }
        //Apply extension policy
        $allowedExtensions = ($allowedExtensions != null ? $allowedExtensions : $this->policy['allowedExtensions']);
        $temp = explode(".", $file["name"]);
        $extension = strtolower(end($temp));
        if (in_array($extension, $allowedExtensions)) {
            if ($file["error"] > 0) return new UploadResult(false, "There is an error in file");
            else {
                if(move_uploaded_file($file["tmp_name"], $target_dir."/".$file_name.".".$extension)){
                    return new UploadResult(true, "Upload successful");
                }
                return new UploadResult(false, "Unknown error. File not uploaded");
            }
        } else return new UploadResult(false, "File is not valid");
    }

    public function setFilePolicy(int $maxSize, array $allowedExtensions){
        $this->policy = [
            'maxSize'           => $maxSize,
            'allowedExtensions' => $allowedExtensions
        ];
    }

    public function setImagePolicy(int $maxSize, array $allowedExtensions){
        $this->policy = [
            'imageMaxSize'           => $maxSize,
            'imageAllowedExtensions' => $allowedExtensions
        ];
    }

    public function setCompression(int $imageCompression, array $thumbnailCompression){
        $this->compress = [
            'image'         => $imageCompression,
            'thumbnail'     => $thumbnailCompression
        ];
    }

    public function uploadFile($file, string $target_dir, string $file_name=null) : UploadResult
    {
        return $this->uploadFileWithPolicy($file, $target_dir, $file_name);
    }

    public function uploadImageWithThumbnailOptimized($file, $target_dir, $file_name, $resizeTo, $thumb_name, $thumb_size) : UploadResult
    {
        return $this->uploadImageWithPolicy($file, $target_dir, $file_name, $resizeTo, $thumb_name, $thumb_size);
    }

    public function uploadImageWithThumbnail($file, $target_dir, $file_name, $thumb_name, $thumb_size) : UploadResult
    {
        return $this->uploadImageWithPolicy($file, $target_dir, $file_name, null, $thumb_name, $thumb_size);
    }

    public function uploadImageOptimized($file, $target_dir, $file_name, $resizeTo) : UploadResult
    {
        return $this->uploadImageWithPolicy($file, $target_dir, $file_name, $resizeTo);
    }
    public function uploadImage($file, $target_dir, $file_name) : UploadResult
    {
        return $this->uploadImageWithPolicy($file, $target_dir, $file_name);
    }
}
?>
