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
            return $this->connection('tenant')->belongsToMany(Media::class, $this->mediaTableName(), null, 'media_id')
                ->where('media.media_type_id', '=', $mediaType);
        }else {
            return $this->connection('tenant')->belongsToMany(Media::class, $this->mediaTableName(), null, 'media_id');
        }
    }
}
