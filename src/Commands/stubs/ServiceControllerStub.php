<?php

namespace DummyNamespace;

use Ruinton\Routing\RestApiServiceController;
use DummyService as Service;

class DummyClass extends RestApiServiceController
{
    public function __construct(Service $service)
    {
        parent::__construct($service);
    }
}
