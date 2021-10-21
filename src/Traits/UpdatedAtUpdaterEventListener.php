<?php


namespace App\Classes\Traits;


use App\Classes\QueryParam;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Model;

trait UpdatedAtUpdaterEventListener
{

    protected function beforeUpdate(Model $model, ?QueryParam $queryParam)
    {
        if($model->updated_at === null || strcmp($model->updated_at, '0000-00-00 00:00:00') === 0) {
            $model->updated_at = Carbon::now()->format('Y-m-d H:i:s');
        }
    }
}
