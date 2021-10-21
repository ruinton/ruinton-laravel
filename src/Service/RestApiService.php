<?php


namespace Ruinton\Service;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Ruinton\Parser\QueryParam;
use Ruinton\Traits\DefaultEventListener;

abstract class RestApiService implements ServiceInterface
{
    use DefaultEventListener;

    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
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
        if($pagination)
        {
            $result = $baseQuery->paginate(
                $queryParam->getPageSize(),
                $queryParam->getVisible(),
                'page',
                $queryParam->getPageNumber()
            );
            $serviceResult->data($result->items(), $this->model->getTable())
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
                $serviceResult->data($model, Str::snake(class_basename($this->model)));
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
            $this->beforeCreate($newModel, $queryParam);
//            $newModel->created_at = Carbon::now()->formatDatetime();
            if($newModel->save())
            {
                $this->afterCreate($newModel, $queryParam);
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
                $this->beforeUpdate($updateModel, $queryParam);
//                $updateModel->updated_at = Carbon::now()->formatDatetime();
                if($updateModel->save())
                {
                    $this->afterUpdate($updateModel, $queryParam);
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
                $this->beforeDelete($deleteModel, $queryParam);
                if($deleteModel->delete())
                {
                    $this->afterDelete($deleteModel, $queryParam);
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
            foreach ($params->getFilterFields() as $key => $filter) {
                if(is_callable($filter)) {
                    $query->where($filter);
                }
                else if(Str::contains($key, '.'))
                {
                    $query->where($key, $filter[1], $filter[0]);
                }
                else{
                    $query->where($this->model->getTable().'.'.$key, $filter[1], $filter[0]);
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
                $query->distinct();
            }
            if($params->getGroupBy() != null)
            {
                $query->groupBy($params->getGroupBy());
            }
            if($params->getWith() != null)
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
            $query->select($params->getColumns());
        }
    }
}
