<?php


namespace Ruinton\Service;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Ruinton\Enums\MimeTypeIndexes;
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

    public function createMedia(UploadedFile $file, $mediaType, Model $baseModel)
    {
        $modelName = strtolower($baseModel->getTable());
        $fileName = uniqid().'-'.$modelName.'.'.$file->extension();
        $storage_dir = '/public/media/'.$modelName.'/temp/';
        $dir = "app\\public\\media\\".$modelName."\\temp\\";
        $path = storage_path($dir);
        $url = url('/storage/media/'.$modelName.'/temp/'.$fileName);

        $mimeTypeReflection = new \ReflectionClass(MimeTypes::class);
        $mimeTypeList = $mimeTypeReflection->getConstants();
        if(!in_array($file->getMimeType(), $mimeTypeList)) {
            return false;
        }
        $result = Media::create([
            'media_type_id' => $mediaType,
            'mime_type_id'  => MimeTypeIndexes::getIndexByName($file->getMimeType()),
            'name'          => $file->getClientOriginalName(),
            'size'          => $file->getSize(),
            'created_at'    => Carbon::now(),
            'path'          => $path.$fileName,
            'url'           => $url
        ]);
        $this->updateStatistics($mediaType, +1, $file->getSize());
        $file->storePubliclyAs($storage_dir, $fileName);
        return $result;
    }

    public function deleteMedia($id, Model $relationModel) : ServiceResult
    {
        $this->unlinkMedia($id, $relationModel);
        $media = Media::find($id);
        if($media->delete())
        {
            /** @var Media $media */
            File::delete($media->path);
            $this->updateStatistics($media->media_type_id, -1, -1 * $media->size);
        }
        return $media;
    }

    public function linkMedia(Model $baseModel, $mediaIds)
    {
        $result = $baseModel->media()->sync($mediaIds);
//        $newModel = $relationModel->replicate();
//        $newModel->fill([
//            'media_id' => $mediaId,
//            $baseModel->getTable().'_id' => $id
//        ]);
//        $result = $newModel->save();
        $mediaList = Media::query()->whereIn('id', $mediaIds)->get();
        foreach ($mediaList as $media) {
            $newPath = str_replace('temp', $baseModel[$baseModel->getKeyName()], $media->path);
            $directory = explode("temp", $media->path)[0] . $baseModel[$baseModel->getKeyName()];
            File::makeDirectory($directory, 0755, true);
            File::move($media->path, $newPath);
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

    public function unlinkMedia($id, ?Model $relationModel)
    {
        if($relationModel != null)
        {
            $relationModel->newQuery()->where('media_id', '=', $id)->delete();
        }
    }

    protected function updateStatistics(int $mimeTypeIndex, int $countEffect, int $sizeEffect)
    {
        $query = MediaType::query();
        $mediaType = $query->where('id', '=', $mimeTypeIndex)->first();
        if($mediaType) {
            $mediaType->count = $mediaType->count + $countEffect;
            $mediaType->size = $mediaType->size + $sizeEffect;
            $mediaType->save();
        }
    }
}
