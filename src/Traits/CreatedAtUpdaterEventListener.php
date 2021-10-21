<?php


namespace App\Classes\Traits;


use App\Classes\QueryParam;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Model;

trait CreatedAtUpdaterEventListener
{

    protected function beforeCreate(Model $model, ?QueryParam $queryParam)
    {
        if($model->created_at === null || strcmp($model->created_at, '0000-00-00 00:00:00') === 0) {
            $model->created_at = Carbon::now()->format('Y-m-d H:i:s');
        }
    }
}
