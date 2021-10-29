<?php

namespace Ruinton\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property integer $count
 * @property float $size
 * @property Media[] $media
 */
class MediaType extends Model
{
    public $timestamps = false;
    /**
     * @var array
     */
    protected $fillable = ['name', 'count', 'size'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function media()
    {
        return $this->hasMany(Media::class);
    }
}
