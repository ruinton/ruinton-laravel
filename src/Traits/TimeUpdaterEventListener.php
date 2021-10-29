<?php


namespace Ruinton\Traits;

use Illuminate\Database\Eloquent\Model;

trait TimeUpdaterEventListener
{

    use CreatedAtUpdaterEventListener, UpdatedAtUpdaterEventListener;
}
