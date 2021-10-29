<?php


namespace Ruinton\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Ruinton\Parser\QueryParam;

trait CountUpdaterEventListener
{
    /** @var Model */
    protected $counterModel;
    protected $counterIdReferenceColumn;

    protected function setCounterModel(Model $model, string $counterIdReferenceColumn)
    {
        $this->counterModel = $model;
        $this->counterIdReferenceColumn = $counterIdReferenceColumn;
    }

    protected function afterCreate(Model $model, ?QueryParam $queryParam)
    {
        if($this->counterModel != null)
        {
            $query = $this->counterModel->newQuery();
            $query
                ->where($this->counterModel->getTable().'.'.$this->counterModel->getKeyName(),
                    '=', $model[$this->counterIdReferenceColumn])
                ->update([
                    'count' => DB::raw('count+1')
                ]);
        }
    }

    protected function afterDelete(Model $model, ?QueryParam $queryParam)
    {
        if($this->counterModel != null)
        {
            $query = $this->counterModel->newQuery();
            $query
                ->where($this->counterModel->getTable().'.'.$this->counterModel->getKeyName(),
                    '=', $model[$this->counterIdReferenceColumn])
                ->update([
                    'count' => DB::raw('if(count > 0, count-1, 0)')
                ]);
        }
    }
}
