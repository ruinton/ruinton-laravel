<?php


namespace Ruinton\Service;


use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ServiceResult
{
    protected $tableName;
    protected $status;
    protected $message;
    protected $data;
    protected $errors;
    protected $meta;

    public function __construct($tableName = null)
    {
        $this->reset();
        $this->tableName = $tableName;
    }

    public function setTableName($tableName) {
        $this->tableName = $tableName;
    }

    public function status(int $statusCode)
    {
        $this->status = $statusCode;
        return $this;
    }

    public function message(string $message)
    {
        $this->message = $message;
        return $this;
    }

    public function data($data, $container)
    {
        $this->data = [$container => $data];
        return $this;
    }

    public function appendData($data, $container)
    {
        if($this->data === null) {
            $this->data($data, $container);
        }else {
            $this->data[$container] = $data;
        }
        return $this;
    }

    public function error($message, $sender)
    {
        $this->errors[$sender] = $message;
        return $this;
    }

    public function errors($message)
    {
        $this->errors = $message;
        return $this;
    }

    public function meta($meta)
    {
        $this->meta = $meta;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return mixed
     */
    public function clearData()
    {
        $this->data = null;
        return $this;
    }

    public function isSuccess()
    {
        return $this->status == 200;
    }

    public function toArray()
    {
        $result = [];
        $result['status'] = $this->status < 300;
        $result['message'] = $this->message;
        if($this->data != null)
        {
            $result['data'] = $this->data;
        }
        if($this->meta != null)
        {
            $result['meta'] = $this->meta;
        }
        if(!empty($this->errors))
        {
            $result['errors'] = $this->errors;
        }
//        $this->reset();
        return $result;
    }

    public function toJsonResponse() : JsonResponse {
        return response()->json($this->toArray());
    }

    public function reset()
    {
        $this->status = 200;
        $this->message = 'operation successful';
        $this->data = null;
        $this->errors = [];
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getPagination()
    {
        return $this->meta['page'];
    }

    /**
     * @return mixed
     */
    public function getDataAsModel()
    {
        try{
            return $this->data[Str::singular($this->tableName)];
        }catch (\Exception $e){
            return null;
        }
    }

    /**
     * @return mixed
     */
    public function getDataAsModelList()
    {
        try{
            return $this->data[$this->tableName];
        }catch (\Exception $e){
            return [];
        }
    }

    /**
     * @return mixed
     */
    public function getMeta()
    {
        return $this->meta;
    }
}
