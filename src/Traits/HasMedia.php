<?php

namespace Ruinton\Traits;

use Illuminate\Support\Str;
use Ruinton\Models\Media;

trait HasMedia
{
    public function media()
    {
        return $this->belongsToMedia();
    }

    public function mediaTableName() {
        return Str::singular($this->getTable()).'_media';
    }

    public function belongsToMedia($mediaType = null) {
        if($mediaType) {
            return $this->belongsToMany(Media::class, $this->mediaTableName(), null, 'media_id')
                ->where($this->mediaTableName().'.media_type_id', '=', $mediaType)
                ->withPivot(Str::singular($this->getTable()).'_id as model_id', 'media_type_id');
        }else {
            return $this->belongsToMany(Media::class, $this->mediaTableName(), null, 'media_id')
                ->withPivot(Str::singular($this->getTable()).'_id as model_id', 'media_type_id');
        }
    }
}
