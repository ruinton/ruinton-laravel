<?php


namespace Ruinton\Parser;


use Ruinton\Enums\FilterOperators;
use Ruinton\Enums\SortOrder;

class QueryParam
{
    static $PAGE_SIZE = 10;
    static $PAGE_NUMBER = 1;

    protected $page;
    protected $filter;
    protected $visible = ['*'];
    protected $columns = ['*'];
    protected $joins;
    protected $sort;
    protected $data;
    protected $group;
    protected $with;
    protected $distinct;
    protected $trashed;

    public function __construct()
    {
        $this->reset();
    }

    public function reset() {
        $this->page = [
            'size'  => self::$PAGE_SIZE,
            'number' => self::$PAGE_NUMBER
        ];
        $this->filter = null;
        $this->visible = ['*'];
        $this->columns = ['*'];
        $this->joins = null;
        $this->sort = null;
        $this->data = null;
        $this->group = null;
        $this->with = null;
        $this->distinct = null;
        $this->trashed = false;
    }

    public function showTrashed() {
        $this->trashed = true;
    }

    public function addFilter($filterName, $filterValue, $operator = FilterOperators::LIKE)
    {
        try{
            $this->filter[$filterName] = [$filterValue, $operator];
        }catch (\Exception $e) {
            $this->filter = [
                $filterName => [$filterValue, $operator]
            ];
        }
    }

    public function addData($key, $value)
    {
        try{
            $this->data[$key] = $value;
        }catch (\Exception $e) {
            $this->data = [
                $key => $value
            ];
        }
    }

    public function addSort(string $column, $desc = SortOrder::DESCENDING)
    {
        if(empty($this->sort)) {
            $this->sort = [
                [$column, $desc]
            ];
        }else {
            array_push($this->sort, [$column, $desc]);
        }
    }

    public function addWith(string $relation)
    {
        try{
            array_push($this->with, $relation);
        }catch (\Exception $e) {
            $this->with = [$relation];
        }
    }

    public function hasWith(): bool
    {
        return $this->with !== null;
    }

    public function hasSort(): bool
    {
        return count($this->sort ?? []) > 0;
    }

    public function hasFilter($filterKey): bool
    {
        return isset($this->filter[$filterKey]);
    }

    public function hasData($key): bool
    {
        return isset($this->data[$key]);
    }

    public function removeFilter($filterKey): void
    {
        if($this->hasFilter($filterKey))
        {
            unset($this->filter[$filterKey]);
        }
    }

    public function getFilter($filterKey)
    {
        return $this->filter[$filterKey];
    }

    public function getTrashed()
    {
        return $this->trashed;
    }

    public function renameFilter($filterKey, $renameKey)
    {
        if($this->hasFilter($filterKey))
        {
            $popped = $this->filter[$filterKey];
            unset($this->filter[$filterKey]);
            $this->addFilter($renameKey, $popped);
        }
    }

    public function popFilter($filterKey)
    {
        if($this->hasFilter($filterKey))
        {
            $popped = $this->filter[$filterKey];
            unset($this->filter[$filterKey]);
            return $popped;
        }
    }

    public function getData($key)
    {
        if($this->hasData($key))
        {
            return $this->data[$key];
        }
    }

    public function setPagination($page, $size)
    {
        $this->page = [
            'size'  => $size,
            'index' => $page
        ];
    }

    public function setDistinct($field)
    {
        $this->distinct = $field;
    }

    public function setPageNumber($page)
    {
        $this->page['number'] = $page;
    }

    public function setPageSize($size)
    {
        $this->page['size'] = $size;
    }

    public function setGroupBy($columns)
    {
        $this->group = $columns;
    }

    /**
     * @return array
     */
    public function getPage(): array
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getPageNumber(): int
    {
        return $this->page['number'];
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->page['size'];
    }

    /**
     * @return int
     */
    public function getDataFields(): array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getFilterFields(): array
    {
        return $this->filter ?? [];
    }

    /**
     * @return array
     */
    public function getVisible(): array
    {
        return $this->visible;
    }

    /**
     * @return array
     */
    public function getSort(): array
    {
        return $this->sort;
    }

    /**
     * @param array $visible
     */
    public function setVisible(array $visible): void
    {
        $this->visible = $visible;
    }

    public function hasColumns(): bool
    {
        return $this->columns[0] !== '*';
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param array $fields
     */
    public function setColumns(array $fields): void
    {
        $this->columns = $fields;
    }

    /**
     * @return array
     */
    public function getJoins(): array
    {
        return $this->joins ?? [];
    }

    /**
     * @param array $joins
     */
    public function setJoins(array $joins): void
    {
        $this->joins = $joins;
    }

    /**
     * @return mixed
     */
    public function getDistinct()
    {
        return $this->distinct;
    }

    /**
     * @return mixed
     */
    public function getGroupBy()
    {
        return $this->group;
    }

    /**
     * @return mixed
     */
    public function getWith()
    {
        return $this->with;
    }

    /**
     * @param mixed $with
     */
    public function setWith($with): void
    {
        $this->with = $with;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @param array $filter
     */
    public function setFilter(array $filter): void
    {
        $this->filter = $filter;
    }

}
