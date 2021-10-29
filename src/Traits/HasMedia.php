<?php

namespace Ruinton\Traits;

use Ruinton\Models\Media;

trait HasMedia
{
    public function media()
    {
        return $this->belongsToMedia();
    }

    public function belongsToMedia($mediaType = null) {
        if($mediaType) {
            return $this->belongsToMany(Media::class, $this->name.'_media', null, 'media_id')
                ->where('media.media_type_id', '=', $mediaType);
        }else {
            return $this->belongsToMany(Media::class, $this->name.'_media', null, 'media_id');
        }
    }
}
