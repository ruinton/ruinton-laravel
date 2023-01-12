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

    public function createMedia(UploadedFile $file, $mediaType, Model $baseModel, $tenant = 'base')
    {
        $modelName = strtolower($baseModel->getTable());
        $fileName = uniqid().'-'.$modelName.'.'.$file->extension();
        $storage_dir = '/public/'.$tenant.'/media/'.$modelName.'/temp/';
        $path = "app/public/$tenant/media/$modelName/temp/";
        $url = '/storage/'.$tenant.'/media/'.$modelName.'/temp/'.$fileName;

        $mimeTypeReflection = new \ReflectionClass(MimeTypes::class);
        $mimeTypeList = $mimeTypeReflection->getConstants();
        if(!in_array($file->getMimeType(), $mimeTypeList)) {
            return false;
        }
        $result = $baseModel->media()->getRelated()::create([
            'media_type_id' => $mediaType,
            'mime_type_id'  => MimeTypes::getIndexByName($file->getMimeType()),
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
