<?php


namespace Ruinton\Service;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Ruinton\Enums\FilterOperators;
use Ruinton\Parser\QueryParam;
use Ruinton\Traits\DefaultEventListener;
use Spatie\Multitenancy\Models\Tenant;

class RestApiModelService implements ServiceInterface
{
    use DefaultEventListener;

    protected Model $model;
    protected bool $hasTenant = true;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getOrCreateParams(?QueryParam $params) {
        if($params) {
            return $params;
        }else {
            return new QueryParam();
        }
    }

    public function getModelName() : string
    {
        return Str::singular($this->model->getTable());
    }

    public function getTableName() : string
    {
        return $this->model->getTable();
    }

    public function all(?QueryParam $queryParam = null, bool $pagination = true) : ServiceResult
    {
        $serviceResult = new ServiceResult($this->model->getTable());
        $baseQuery = $this->model->newQuery();
        $this->applyParamsOnQuery($baseQuery, $queryParam);
//        $serviceResult->appendData($baseQuery->toSql(), 'sql');
//        $serviceResult->appendData($baseQuery->getBindings(), 'params');
        if($pagination)
        {
            $result = $baseQuery->paginate(
                $queryParam->getPageSize(),
                $queryParam->getColumns(),
                'page',
                $queryParam->getPageNumber()
            );
            $serviceResult->appendData($result->items(), $this->model->getTable())
                ->meta([
                    'page' => [
                        'first_item'        => $result->firstItem(),
                        'last_item'         => $result->lastItem(),
                        'total_items'       => $result->total(),
                        'current_page'      => $result->currentPage(),
                        'last_page'         => $result->lastPage(),
                        'per_page'          => $result->perPage(),
                    ]
                ]);
        }
        else
        {
            $result = $baseQuery->get();
            $serviceResult->data($result, $this->model->getTable());
        }
        return $serviceResult;
    }

    public function find($id = null, ?QueryParam $queryParam = null): ServiceResult
    {
        $serviceResult = new ServiceResult($this->model->getTable());
        try
        {
            $baseQuery = $this->model->newQuery();
            $this->applyParamsOnQuery($baseQuery, $queryParam);
            if($id != null)
            {
                $baseQuery->where($this->model->getTable().'.'.$this->model->getKeyName(), $id);
            }
            $model = $baseQuery->first();
            if($model)
            {
                $serviceResult->appendData($model, Str::snake(class_basename($this->model)));
            }
            else
            {
                $serviceResult->status(404)->message('cannot find '.class_basename($this->model))
                    ->error('model not exists', 'data');
            }
        }
        catch (\Exception $e)
        {
            $serviceResult->status(500)->message('unknown error')->error($e->getMessage(), 'service');
        }
        return $serviceResult;
    }

    public function create(array $data, ?QueryParam $queryParam = null): ServiceResult
    {
        $serviceResult = new ServiceResult($this->model->getTable());
        try
        {
            $newModel = $this->model->replicate();
            $newModel->fill($data);
            $this->beforeCreate($newModel, $queryParam, $data);
//            $newModel->created_at = Carbon::now()->formatDatetime();
            if($newModel->save())
            {
                $this->linkMediaFields($data, $newModel);
                $this->afterCreate($newModel, $queryParam, $data);
                $serviceResult->status(200)->message(class_basename($this->model).' created successfully');
                if($queryParam != null)
                {
                    $serviceResult->data(
                        ($queryParam->hasColumns()) ? $newModel : $newModel->setVisible($queryParam->getColumns()),
                        Str::snake(class_basename($this->model)));
                }else{
                    $serviceResult->data(
                        ($newModel),
                        Str::snake(class_basename($this->model)));
                }
            }
            else
            {
                $serviceResult->status(500)->message('cannot create '.class_basename($this->model));
            }
        }
        catch (\Exception $e)
        {
            $serviceResult->status(500)->message('unknown error')->error($e->getMessage(), 'service');
        }
        return $serviceResult;
    }

    public function update($id, array $data, ?QueryParam $queryParam = null): ServiceResult
    {
        $serviceResult = new ServiceResult($this->model->getTable());
        try
        {
            $query = $this->model::query();
            $this->applyParamsOnQuery($query, $queryParam);
            if($id != null)
            {
                $query->where($this->model->getTable().'.'.$this->model->getKeyName(), $id);
            }
            /** @var Model $updateModel */
            $updateModel = $query->first();
            if($updateModel)
            {
                $updateModel->fill($data);
                $this->linkMediaFields($data, $updateModel);
                $this->beforeUpdate($updateModel, $queryParam, $id, $data);
//                $updateModel->updated_at = Carbon::now()->formatDatetime();
                if($updateModel->save())
                {
                    $this->afterUpdate($updateModel, $queryParam, $id, $data);
                    $serviceResult->status(200)->message(class_basename($this->model).' updated successfully')
                        ->data($updateModel,
                            Str::snake(class_basename($this->model)));
                }
                else
                {
                    $serviceResult->status(500)->message('cannot update '.class_basename($this->model));
                }
            }
            else
            {
                $serviceResult->status(404)->message('cannot update '.class_basename($this->model))
                    ->error('model not exists', 'data');
            }
        }
        catch (\Exception $e)
        {
            $serviceResult->status(500)->message('unknown error')->error($e->getMessage(), 'service');
        }
        return $serviceResult;
    }

    public function delete($id, ?QueryParam $queryParam = null) : ServiceResult
    {
        $serviceResult = new ServiceResult($this->model->getTable());
        try
        {
            $query = $this->model::query();
            $this->applyParamsOnQuery($query, $queryParam);
            if($id !== null) {
                $query->where($this->model->getTable() . '.' . $this->model->getKeyName(), $id);
            }
            /** @var Model $deleteModel */
            $deleteModel = $query->first();
            if($deleteModel)
            {
                $this->deleteLinkedMedia($id);
                $this->beforeDelete($deleteModel, $queryParam, $id);
                if($deleteModel->delete())
                {
                    $this->afterDelete($deleteModel, $queryParam, $id);
                    $serviceResult->status(200)->message(class_basename($this->model).' deleted successfully')
                        ->data($deleteModel,
                            Str::snake(class_basename($this->model)));
                }
                else
                {
                    $serviceResult->status(500)->message('cannot delete '.class_basename($this->model));
                }
            }
            else
            {
                $serviceResult->status(404)->message('cannot delete '.class_basename($this->model))
                    ->error('model not exists', 'data');
            }
        }
        catch (\Exception $e)
        {
            $serviceResult->status(500)->message('unknown error')->error($e->getMessage(), 'service');
        }
        return $serviceResult;
    }

    public function bulkUpdate(array $data) : ServiceResult
    {
        $serviceResult = new ServiceResult($this->model->getTable());
        if(is_array($this->model->getKeyName())) {
            DB::transaction(function () use ($data) {
                foreach ($data as $row)
                {
                    $query = DB::table($this->model->getTable());
                    foreach ($this->model->getKeyName() as $key) {
                        $query->where($this->model->getTable().'.'.$key,
                            '=',
                            $row[$key]);
                    }
                    $query->update($row);
                }
            });
        }else {
            DB::transaction(function () use ($data) {
                foreach ($data as $row)
                {
                    DB::table($this->model->getTable())
                        ->where($this->model->getTable().'.'.$this->model->getKeyName(),
                            '=',
                            $row[$this->model->getKeyName()])
                        ->update($row);
                }
            });
        }

        return $serviceResult->status(200)->message('records updated');
    }

    public function bulkUpdateOrInsert(array $data) : ServiceResult
    {
        $serviceResult = new ServiceResult($this->model->getTable());
        if(is_array($this->model->getKeyName())) {
            DB::transaction(function () use ($data) {
                foreach ($data as $row)
                {
                    $attributes = [];
                    $query = DB::table($this->model->getTable());
                    foreach ($this->model->getKeyName() as $key) {
                        $query->where($this->model->getTable().'.'.$key,
                            '=',
                            $row[$key]);
                        $attributes[$key] = $row[$key];
                    }
                    $query->updateOrInsert($attributes, $row);
                }
            });
        }else {
            DB::transaction(function () use ($data) {
                foreach ($data as $row)
                {
                    DB::table($this->model->getTable())
                        ->where($this->model->getTable().'.'.$this->model->getKeyName(),
                            '=',
                            $row[$this->model->getKeyName()])
                        ->updateOrInsert($row);
                }
            });
        }

        return $serviceResult->status(200)->message('records updated');
    }

    public function bulkDelete(array $data) : ServiceResult
    {
        $serviceResult = new ServiceResult($this->model->getTable());
        $ids = implode(', ', $data);
        DB::table($this->model->getTable())
            ->whereIn($this->model->getTable().'.'.$this->model->getKeyName(), $ids)
            ->delete();
        return $serviceResult->status(200)->message('records deleted');
    }

    protected function applyParamsOnQuery(Builder $query, ?QueryParam $params) {
        if($params) {
            if ($params->hasColumns()) {
                $query->select($params->getColumns());
            }
            foreach ($params->getFilterFields() as $key => $filter) {
                if(is_callable($filter[0]) && strcmp($filter[1], FilterOperators::CLOSURE) === 0) {
                    $query->where($filter[0]);
                }
                else if(Str::contains($key, '.'))
                {
                    $query->where($key, $filter[1], $filter[0]);
                }
                else{
                    if(strcmp($filter[1], FilterOperators::IS_NULL) === 0) {
                        $query->whereNull($this->model->getTable().'.'.$key);
                    }
                    else if(strcmp($filter[1], FilterOperators::IS_NOT_NULL) === 0) {
                        $query->whereNotNull($this->model->getTable().'.'.$key);
                    }
                    else if(strcmp($filter[1], FilterOperators::HAS) === 0) {
                        $query->whereHas($key, $filter[0]);
                    }
                    else if(strcmp($filter[1], FilterOperators::IN) === 0) {
                        $query->whereIn($this->model->getTable().'.'.$key, explode(",", $filter[0]));
                    }
                    else if(strcmp($filter[1], FilterOperators::NOT_IN) === 0) {
                        $query->whereNotIn($this->model->getTable().'.'.$key, explode(",", $filter[0]));
                    }
                    else {
                        $query->where($this->model->getTable() . '.' . $key, $filter[1], $filter[0]);
                    }
                }
            }
            foreach ($params->getJoins() as $joinModel => $joinFields)
            {
                if(count($joinFields) > 3){
                    $query->join($joinModel, $joinFields[0], $joinFields[1], $joinFields[2], $joinFields[3]);
                }else{
                    $query->join($joinModel, $joinFields[0], $joinFields[1], $joinFields[2]);
                }
            }
            if($params->getDistinct() != null)
            {
                $query->distinct($params->getDistinct());
            }
            if($params->getGroupBy() != null)
            {
                $query->groupBy($params->getGroupBy());
            }
            if($params->hasWith())
            {
                $query->with($params->getWith());
            }
            if($params->getTrashed())
            {
//                $query->with($params->getWith());
            }
            else if($params->hasSort())
            {
                foreach ($params->getSort() as $sort)
                {
                    $query->orderBy($sort[0], $sort[1]);
                }
            }
            else
            {
                $query->orderByDesc($this->model->getKeyName());
            }
            if($params->hasWithCount())
            {
                $query->withCount($params->getWithCount());
            }
        }
    }

    public function swapPriority(int $fromId = 0, int $toId = 0) : ServiceResult
    {
        $result = new ServiceResult();
        $from = $this->model->find($fromId);
        if ($from) {
            $to = $this->model->find($toId);
            if ($to) {
                $query = $this->model::query();
                $between = $query->select(['id'])->where('priority', '>', $from->priority)->where('priority', '<', $to->priority)->get();
                $swapList = [$from];
                array_push($swapList, ...$between);
                array_push($swapList, $to);
                $temp = $swapList;
                while (count($swapList) > 1) {
                    $actor = array_shift($swapList);
                    $target = $swapList[0];
                    $this->swapModelPriorities($actor, $target);
                }
                return $result->status(200)->message('model priorities swap completed')
                    ->data($from, 'from')
                    ->appendData($to, 'to')
                    ->appendData($between, 'between')
                    ->appendData($temp, 'swap');
            } else {
                return $result->status(404)->message('to model not fount');
            }
        } else {
            return $result->status(404)->message('from model not fount');
        }
    }

    protected function swapModelPriorities($from, $to) {
        $temp = $to->priority;
        $to->priority = $from->priority;
        $to->save();
        $from->priority = $temp;
        $from->save();
    }

    public function getMediaRules() {
        return [];
    }

    public function linkMediaFields(array $data, Model $model) {
        if(!isset($this->model['media'])) return;
        /** @var MediaService $mediaService */
        $mediaService = App::make(MediaService::class);
        $mediaFields = array_keys($this->getMediaRules());
        foreach ($data as $key => $media) {
            if(!$this->isMedia($media, $key, $mediaFields)) continue;
            $mediaList = [];
            if(isset($media[0])) {
                $mediaList = $media;
            }else {
                $mediaList = [$media];
            }
            $mediaIds = [];
            foreach ($mediaList as $mediaItem) {
                array_push($mediaIds, $mediaItem['id']);
            }
            $result = new ServiceResult($this->model->getTable());
//            return $result->status(404)->data($mediaIds, 'ids');
            $mediaService->linkMedia($model, $mediaIds);
        }
    }

    public function isMedia($data, $key, $mediaFields) {
        if(!is_array($data)) return false;
        if(count($data) < 1) return false;
        if(!in_array($key, $mediaFields)) return false;
        return true;
    }

    public function deleteLinkedMedia(int $id = 0)
    {
        if(!isset($this->model['media'])) return;
        /** @var MediaService $mediaService */
        $mediaService = App::make(MediaService::class);
        $mediaService->deleteLinkedMedia($id, $this->model);
    }

    public function createMedia(array $files) : ServiceResult
    {
        /** @var MediaService $mediaService */
        $mediaService = App::make(MediaService::class);
        $serviceResult = new ServiceResult($this->model->getTable());
        $mediaRules = $this->getMediaRules();
        foreach ($files as $key => $file)
        {
            $fieldKey = $key;
            if(str_contains($key, '-')) {
                $fieldKey = explode('-', $key)[0];
            }
            if(!in_array($fieldKey, array_keys($mediaRules))) continue;
            if(isset($mediaRules[$fieldKey]['mimeType'])) {
                $mimeTypes = [$mediaRules[$fieldKey]['mimeType']];
                if (is_array($mediaRules[$fieldKey]['mimeType'])) {
                    $mimeTypes = $mediaRules[$fieldKey]['mimeType'];
                }
                if (!in_array($file->getMimeType(), $mimeTypes)) continue;
            }
            $media = $mediaService->createMedia($file, $mediaRules[$fieldKey]['type'], $this->model, $this->hasTenant ? Tenant::current()->id : 'base');
            $serviceResult->appendData($media, $fieldKey);
        }

        $serviceResult->status(200)->message('Media uploaded')->data($serviceResult->getData(),
            Str::snake(class_basename($this->model)));;
        return $serviceResult;
    }


    public function deleteMedia(int $id = 0) : ServiceResult
    {
        /** @var MediaService $mediaService */
        $mediaService = App::make(MediaService::class);
        $serviceResult = new ServiceResult($this->model->getTable());
        try {
            $model = $mediaService->deleteMedia($id, $this->model);
            $serviceResult->status(200)->message('Media deleted')->data($model, $this->model->getTable());
        }
        catch (\Exception $e)
        {
            $serviceResult->status(402)->message('unknown error')->error($e->getMessage(), 'service');
        }
        return $serviceResult;
    }

    public function createServiceResult($status, $message, $data, $queryParam = null) {
        $serviceResult = new ServiceResult($this->model->getTable());
        $serviceResult->status($status)->message($message);
        if ($queryParam !== null) {
            $serviceResult->data(($queryParam->hasColumns()) ? $data : $data->setVisible($queryParam->getColumns()), Str::snake(class_basename($this->model)));
        } else {
            $serviceResult->data($data, Str::snake(class_basename($this->model)));
        }
        return $serviceResult;
    }
}
