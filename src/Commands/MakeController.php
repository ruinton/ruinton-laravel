<?php

namespace Ruinton\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeController extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ruinton:controller
    {service? : Generate controller for service}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service controller with ruinton';

    public function handle()
    {
        $service = $this->argument('service');

        if(empty($service)) {
            $modelTemp = scandir(app_path('Services'));
            $modelList = [];
            foreach ($modelTemp as $temp) {
                if($temp === '.' || $temp === '..') continue;
                array_push($modelList, rtrim($temp, ".php"));
            }
            $service = $this->choice('Select service class', $modelList);
        }

        $model = rtrim($service, 'Service');
        $name = $this->qualifyClass($model.'Controller');
        $service = $this->qualifyService($service);
        $path = $this->getPath($name);
        $this->makeDirectory($path);
        $data = $this->sortImports($this->buildClass($name));
        $data = $this->replaceService($data, $service);
        $this->files->put($path, $data);
        $this->info("Created service controller in $path for $service service.");
    }

    protected function qualifyService($name)
    {
        $name = ltrim($name, '\\/');

        $name = str_replace('/', '\\', $name);

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        return $this->qualifyClass(
            $this->getServiceNamespace(trim($rootNamespace, '\\')).'\\'.$name
        );
    }

    protected function replaceService($stub, $model)
    {
        return str_replace(['DummyService', '{{ class }}', '{{class}}'], $model, $stub);
    }

    protected function getStub()
    {
        return dirname(__FILE__).'/stubs/ServiceControllerStub.php';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . "\Http\Controllers\Api";
    }

    protected function getServiceNamespace($rootNamespace)
    {
        return $rootNamespace . "\Services";
    }
}
