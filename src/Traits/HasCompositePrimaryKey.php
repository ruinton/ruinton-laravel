<?php


namespace Ruinton\Traits;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait HasCompositePrimaryKey
{
    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Get the primary key for the model.
     * The keyName string is concatenation of primaryKey using '_', with '_' at start
     *
     * @return string
     */
//    public function getKeyName()
//    {
//        return '_'.implode('_', $this->primaryKey);
//    }

    /**
     * Get the value of the model'ames primary key.
     * The attribute value is '-' separated values.
     * Attribute array is also set when this is called, so the key name will be properly replaced by the widget template.
     * @return mixed
     */
    public function getKey()
    {
        $attributes = [];
        foreach ($this->primaryKey as $key) {
            $attributes[] = $this->getAttribute($key);
        }
        $value = implode('-',$attributes);
        $this->setAttribute($this->getKeyName(),$value);
        //return implode('-',$attributes);
        return $value;
    }

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        foreach ($this->primaryKey as $key) {
            if (isset($this->$key))
                $query->where($key, '=', $this->$key);
            else
                throw new Exception(__METHOD__ . 'Missing part of the primary key: ' . $key);
        }
        return $query;
    }
    /**
     * Execute a query for a single record by ID.
     *
     * @param  array  $ids Array of keys, like [column => value].
     * @param  array  $columns
     * @return mixed|static
     */
    public static function find($composite_id, $columns = ['*'])
    {
        $ids = explode('-',$composite_id);
        $me = new self;
        $query = $me->newQuery();
        foreach ($me->primaryKey as $index => $key) {
            $query->where($key, '=', $ids[$index]);
        }
        return $query->first($columns);
    }
    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param mixed $ids
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findOrFail($composite_id, $columns = ['*'])
    {

        $result = self::find($composite_id, $columns);
        if (!is_null($result)) {
            return $result;
        }
        throw (new ModelNotFoundException)->setModel(
            __CLASS__
        );
    }
    /**
     * Reload the current model instance with fresh attributes from the database.
     *
     * @return $this
     */
    public function refresh()
    {
        if (!$this->exists) {
            return $this;
        }
        $this->setRawAttributes(
            static::findOrFail($this->getKey())->attributes
        );
        $this->load(collect($this->relations)->except('pivot')->keys()->toArray());
        return $this;
    }
}
