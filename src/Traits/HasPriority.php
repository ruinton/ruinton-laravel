<?php

namespace Ruinton\Traits;

trait HasPriority
{
    public static function boot()
    {
        parent::boot();
        self::created(function($model){
            $model->priority = $model->id;
            $model->save();
        });
    }
}
