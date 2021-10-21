<?php


namespace App\Classes;


use App\Classes\Enums\MediaTypes;
use App\Classes\Enums\MimeTypeIndexes;
use App\Classes\Enums\MimeTypes;
use App\Services\Media\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class MediaRuintonModelService extends RuintonModelService
{
    /** @var MediaService */
    protected $mediaService;
    /** @var Model */
    protected $mediaRelationModel;

    public function __construct(Model $model,
                                MediaService $mediaService, Model $mediaRelationModel)
    {
        parent::__construct($model);
        $this->mediaService = $mediaService;
        $this->mediaRelationModel = $mediaRelationModel;
    }

    public function delete($id, ?QueryParam $queryParam = null)
    {
        $this->deleteMedia($id);
        return parent::delete($id, $queryParam);
    }

    /**
     * @param int $id
     * @param UploadedFile[] $files
     * @return ResultBuilder
     */
    public function createMedia(int $id, array $files)
    {
        try {
            $query = $this->model::query();
            $query->where($this->model->getTable() . '.' . $this->model->getKeyName(), $id);
            /** @var Model $updateModel */
            $updateModel = $query->first();
            if ($updateModel) {
                $mediaTypes = $this->defaultMediaTypes();
                foreach ($files as $key => $file)
                {
                    if(!in_array($file->getMimeType(), array_keys($mediaTypes))) continue;
                    $this->mediaService->createMedia($id, $file, $mediaTypes[$file->getMimeType()],
                        $this->model, $this->mediaRelationModel);
                }
            }
        }
        catch (\Exception $e)
        {
            $this->result->status(402)->message('unknown error')->error($e->getMessage(), 'service');
        }
        return $this->result;
    }

    /**
     * @param int $id
     * @param UploadedFile[] $files
     * @return ResultBuilder
     */
    public function updateMedia(int $id, array $files)
    {
        try {
            $query = $this->model::query();
            $query->where($this->model->getTable() . '.' . $this->model->getKeyName(), $id);
            /** @var Model $updateModel */
            $updateModel = $query->first();
            if ($updateModel) {
                $mediaTypes = $this->defaultMediaTypes();
                foreach ($files as $key => $file)
                {
                    if(!in_array($file->getMimeType(), array_keys($mediaTypes))) continue;
                    $this->mediaService->updateMedia($id, $file, $mediaTypes[$file->getMimeType()],
                        $this->model, $this->mediaRelationModel);
                }
            }
        }
        catch (\Exception $e)
        {
            $this->result->status(402)->message('unknown error')->error($e->getMessage(), 'service');
        }
        return $this->result;
    }

    /**
     * @param int $id
     * @param int $mediaType
     * @return ResultBuilder
     */
    public function deleteMedia(int $rId, int $id = 0, int $mediaType = MediaTypes::ANY)
    {
        try {
            $query = $this->model::query();
            $query->where($this->model->getTable() . '.' . $this->model->getKeyName(), $rId);
            /** @var Model $deleteModel */
            $deleteModel = $query->first();
            if ($deleteModel) {
                $this->mediaService->deleteMedia($rId, $id, $mediaType, $this->model, $this->mediaRelationModel);
            }
        }
        catch (\Exception $e)
        {
            $this->result->status(402)->message('unknown error')->error($e->getMessage(), 'service');
        }
        return $this->result;
    }

    protected function defaultMediaTypes()
    {
        return [
                MimeTypes::IMAGE_JPEG   => [MediaTypes::UNKNOWN_IMAGE, MimeTypeIndexes::IMAGE_JPEG  ],
                MimeTypes::IMAGE_PNG    => [MediaTypes::UNKNOWN_IMAGE, MimeTypeIndexes::IMAGE_PNG   ],
                MimeTypes::VIDEO_MP4    => [MediaTypes::UNKNOWN_VIDEO, MimeTypeIndexes::VIDEO_MP4   ],
                MimeTypes::VIDEO_AVI    => [MediaTypes::UNKNOWN_VIDEO, MimeTypeIndexes::VIDEO_AVI   ],
                MimeTypes::AUDIO_MPEG   => [MediaTypes::UNKNOWN_AUDIO, MimeTypeIndexes::AUDIO_MPEG  ],
                MimeTypes::AUDIO_MP4    => [MediaTypes::UNKNOWN_AUDIO, MimeTypeIndexes::AUDIO_MP4   ],
                MimeTypes::AUDIO_OGG    => [MediaTypes::UNKNOWN_AUDIO, MimeTypeIndexes::AUDIO_OGG   ],
            ];
    }
}
