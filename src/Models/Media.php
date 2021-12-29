<?php

namespace Ruinton\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property int $media_type_id
 * @property int $mime_type_id
 * @property string $name
 * @property string $url
 * @property string $path
 * @property float $size
 * @property int $status
 * @property string $created_at
 * @property MediaType $mediaType
 */
class Media extends Model
{
//    protected $connection = 'tenant';
    public $timestamps = false;
    public ?string $database = null;
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['media_type_id', 'mime_type_id', 'name', 'url', 'path', 'size', 'status', 'created_at'];

    protected $hidden = ['media_type_id', 'mime_type_id', 'path', 'status', 'created_at', 'updated_at'];

    public function getUrlAttribute($value) {
        return url($value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mediaType()
    {
        return $this->belongsTo(MediaType::class);
    }
}
