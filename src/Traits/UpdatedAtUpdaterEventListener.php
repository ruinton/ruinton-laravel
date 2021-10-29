<?php


namespace Ruinton\Traits;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Ruinton\Parser\QueryParam;

trait UpdatedAtUpdaterEventListener
{

    protected function beforeUpdate(Model $model, ?QueryParam $queryParam)
    {
        if($model->updated_at === null || strcmp($model->updated_at, '0000-00-00 00:00:00') === 0) {
            $model->updated_at = Carbon::now()->format('Y-m-d H:i:s');
        }
    }
}
