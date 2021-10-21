<?php


namespace App\Classes\Traits;


use App\Classes\QueryParam;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Model;

trait TimeUpdaterEventListener
{

    use CreatedAtUpdaterEventListener, UpdatedAtUpdaterEventListener;
}
