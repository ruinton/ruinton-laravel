<?php


namespace Ruinton\Traits;

use Ruinton\Parser\QueryParam;

trait DefaultEventListener
{
    protected function beforeUpdate(\Illuminate\Database\Eloquent\Model $model, ?QueryParam $queryParam, $id = null, $data = [])
    {

    }

    protected function afterUpdate(\Illuminate\Database\Eloquent\Model $model, ?QueryParam $queryParam, $id = null, $data = [])
    {

    }

    protected function beforeDelete(\Illuminate\Database\Eloquent\Model $model, ?QueryParam $queryParam, $id = null)
    {

    }

    protected function afterDelete(\Illuminate\Database\Eloquent\Model $model, ?QueryParam $queryParam, $id = null)
    {

    }

    protected function beforeCreate(\Illuminate\Database\Eloquent\Model $model, ?QueryParam $queryParam, $data = [])
    {

    }

    protected function afterCreate(\Illuminate\Database\Eloquent\Model $model, ?QueryParam $queryParam, $data = [])
    {

    }
}
