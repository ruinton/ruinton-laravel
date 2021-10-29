<?php

namespace Ruinton\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\Artisan;

class MakeService extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ruinton:service
    {model? : Generate service for models}
    {--c|controller : Make a controller linked to generated service}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model service with ruinton';

    public function handle()
    {
        $withController = $this->option('controller');
        $model = $this->argument('model');

        if(empty($model)) {
            $modelTemp = scandir(app_path('Models'));
            $modelList = [];
            foreach ($modelTemp as $temp) {
                if($temp === '.' || $temp === '..') continue;
                array_push($modelList, rtrim($temp, ".php"));
            }
            $model = $this->choice('Select model class', $modelList);
        }

        $name = $this->qualifyClass($model.'Service');
        $parts = explode('/', $model);
//        $this->info('model is '.$model.' has '.$parts);
        $qualifiedModel = $this->qualifyModel(end($parts));
        $path = $this->getPath($name);
        $this->makeDirectory($path);
        $data = $this->sortImports($this->buildClass($name));
        $data = $this->replaceModel($data, $qualifiedModel);
        $this->files->put($path, $data);
        $this->info("created service model in $path for $qualifiedModel model.");

        if($withController) {
            $this->call(MakeController::class, ['service' => $model.'Service']);
        }
    }

    protected function replaceModel($stub, $model)
    {
        return str_replace(['DummyModel', '{{ class }}', '{{class}}'], $model, $stub);
    }

    protected function getStub()
    {
        return dirname(__FILE__).'/stubs/ServiceModelStub.php';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . "\Services";
    }
}
