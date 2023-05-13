<?php


namespace Ruinton\Service;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Ruinton\Enums\MimeTypes;
use Ruinton\Helpers\Uploader\UploadHelper;
use Ruinton\Models\Media;
use Ruinton\Models\MediaType;


class MediaService
{
    protected UploadHelper $uploadHelper;

    public function __construct(UploadHelper $uploadHelper)
    {
        $this->uploadHelper = $uploadHelper;
    }

    public function createMedia(UploadedFile $file, $mediaType, Model $baseModel, $tenant = 'base', $maxWidth = null, $maxHeight = null, $watermark = false, $optimize = false, $optimizeFormat = 'webp', $compressionRatio = 80)
    {
        $modelName = strtolower($baseModel->getTable());
        $clientFileName = $file->getClientOriginalName();
        $fileName = uniqid().'-'.$modelName.'.'.$file->extension();
        $storage_dir = '/public/'.$tenant.'/media/'.$modelName.'/temp/';
        $path = "app/public/$tenant/media/$modelName/temp/";
        $url = '/storage/'.$tenant.'/media/'.$modelName.'/temp/'.$fileName;

        $mimeTypeReflection = new \ReflectionClass(MimeTypes::class);
        $mimeTypeList = $mimeTypeReflection->getConstants();
        if(!in_array($file->getMimeType(), $mimeTypeList)) {
            return false;
        }
        $filePath = storage_path($path.$fileName);
        if ($maxWidth !== null || $maxHeight !== null || $optimize || $watermark) {
            $result = $this->optimizeFile($file, $filePath, $maxWidth, $maxHeight, $watermark, $optimizeFormat, $compressionRatio);
            if ($result) {
                $clientFileName = str_replace('.'.$file->extension(), '.'.$optimizeFormat, $clientFileName);
                $fileName = str_replace('.'.$file->extension(), '.'.$optimizeFormat, $fileName);
                $url = '/storage/'.$tenant.'/media/'.$modelName.'/temp/'.$fileName;
                $filePath = storage_path($path.$fileName);
            }
        } else {
            $file->storePubliclyAs($storage_dir, $fileName);
        }
        $fileSize = filesize($filePath);
        $result = $baseModel->media()->getRelated()::create([
            'media_type_id' => $mediaType,
            'mime_type_id'  => MimeTypes::getIndexByName(mime_content_type($filePath)),
            'name'          => $clientFileName,
            'size'          => $fileSize,
            'created_at'    => Carbon::now(),
            'path'          => $path.$fileName,
            'url'           => $url
        ]);
        $this->updateStatistics($mediaType, +1, $fileSize);
        return $result;
    }

    public function optimizeFile(UploadedFile $file, $savePath, $maxWidth = null, $maxHeight = null, $watermark = false, $optimizeFormat = 'webp', $compressionRatio = 80) {
        list($swidth, $sheight, $stype, $sattr) = getimagesize($file->getPathname());
        $width = $swidth;
        $height = $sheight;
        if ($maxWidth && $maxWidth < $swidth) {
            $width=$maxWidth;
            $height=$sheight*($width/$swidth);
        }
        if ($maxHeight && $maxHeight < $sheight) {
            $height=$maxHeight;
            $width=$swidth*($height/$sheight);
        }
        if($file->getMimeType()==MimeTypes::IMAGE_PNG || $file->getMimeType()==MimeTypes::IMAGE_X_PNG){
            $bg = imagecreatefrompng($file->getPathname());
            $image = imagecreatetruecolor(imagesx($bg), imagesy($bg));
            imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
            imagealphablending($image, TRUE);
            imagecopy($image, $bg, 0, 0, 0, 0, imagesx($bg), imagesy($bg));
            imagedestroy($bg);
        }else if($file->getMimeType()==MimeTypes::IMAGE_JPEG){
            $image = imagecreatefromjpeg($file->getPathname());
        }else{
            return false;
        }
        $tn = imagecreatetruecolor($width, $height);
        imagecopyresampled($tn, $image, 0, 0, 0, 0, $width, $height, $swidth, $sheight);
        $savePath = str_replace('.'.$file->extension(), '.'.$optimizeFormat, $savePath);
        if ($optimizeFormat === 'webp') {
            imagewebp($tn, $savePath, $compressionRatio);
        } else {
            imagejpeg($tn, $savePath, $compressionRatio);
        }
        return true;
    }

    public function deleteMedia($id, Model $relationModel)
    {
        $this->unlinkMedia($id, $relationModel);
        $media = $relationModel->media()->getRelated()::find($id);
        if($media->delete())
        {
            /** @var Media $media */
            File::delete(storage_path($media->path));
            $this->updateStatistics($media->media_type_id, -1, -1 * $media->size);
        }
        return $media;
    }

    public function linkMedia(Model $baseModel, $mediaIds)
    {
        $result = $baseModel->media()->syncWithoutDetaching($mediaIds);
        return $result;
    }

    public function linkMediaAndMove(Model $baseModel, $mediaIds)
    {
        $result = $baseModel->media()->syncWithoutDetaching($mediaIds);
//        $newModel = $relationModel->replicate();
//        $newModel->fill([
//            'media_id' => $mediaId,
//            $baseModel->getTable().'_id' => $id
//        ]);
//        $result = $newModel->save();
        $mediaList = $baseModel->media()->getRelated()::query()->whereIn('id', $mediaIds)->get();
        foreach ($mediaList as $media) {
            $newPath = storage_path(str_replace('temp', $baseModel[$baseModel->getKeyName()], $media->path));
            $directory = storage_path(explode("temp", $media->path)[0] . $baseModel[$baseModel->getKeyName()]);
            if(!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
            File::move(storage_path($media->path), $newPath);
            $media->url = str_replace('temp', $baseModel[$baseModel->getKeyName()], $media->url);
            $media->path = $newPath;
            $media->save();
        }
//        if($result) {
//            $media = Media::find($mediaId);
//            $newPath = str_replace('temp', $id, $media->path);
//            File::move($media->path, $newPath);
//            $media->url = str_replace('temp', $id, $media->url);
//            $media->path = $newPath;
//            $media->save();
//        }
        return $result;
    }

    public function unlinkMedia($id, ?Model $model)
    {
        if($model != null)
        {
            $tableName = $model->getConnection()->getDatabaseName() .'.'.Str::singular($model->getTable()).'_media';
            DB::table($tableName)
                ->where('media_id', '=', $id)
                ->delete();
        }
    }

    public function deleteLinkedMedia($id, ?Model $model)
    {
        if($model != null)
        {
            $modelName = Str::singular($model->getTable());
            $tableName = $model->getConnection()->getDatabaseName() .'.'.$modelName.'_media';
            $media = DB::table($tableName)
                ->where($modelName.'_id', '=', $id)
                ->get();
            foreach ($media as $m) {
                $this->deleteMedia($m->media_id, $model);
            }
        }
    }

    protected function updateStatistics(int $mimeTypeIndex, int $countEffect, int $sizeEffect)
    {
        $query = MediaType::query();
        $mediaType = $query->where('id', '=', $mimeTypeIndex)->first();
        $calculatedCount = $mediaType->count + $countEffect;
        if($mediaType) {
            $mediaType->count = $calculatedCount < 0 ? 0 : $calculatedCount;
            $mediaType->size = $mediaType->size + $sizeEffect;
            $mediaType->save();
        }
    }
}
