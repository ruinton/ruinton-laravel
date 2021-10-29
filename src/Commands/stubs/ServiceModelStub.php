<?php

namespace DummyNamespace;

use DummyModel as Model;
use Ruinton\Service\RestApiModelService;

class DummyClass extends RestApiModelService
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }
}
