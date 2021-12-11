<?php

namespace Ruinton\Traits;

use Illuminate\Support\Str;
use Ruinton\Models\Media;
use Spatie\Multitenancy\Models\Tenant;

trait HasMediaTenant
{
    public function media()
    {
        return $this->belongsToMedia();
    }

    public function mediaTableName() {
        return Tenant::current()->getDatabaseName().'.'.Str::singular($this->getTable()).'_media';
    }

    public function belongsToMedia($mediaType = null) {
        if($mediaType) {
            return $this->belongsToMany(Media::class, $this->mediaTableName(), null, 'media_id')
                ->where('media.media_type_id', '=', $mediaType);
        }else {
            return $this->belongsToMany(Media::class, $this->mediaTableName(), null, 'media_id');
        }
    }
}
