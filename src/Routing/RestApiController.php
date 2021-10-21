<?php


namespace Ruinton\Routing;


use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class RestApiController extends RuintonController
{
    /** @var CrudService */
    protected $service;

    public function __construct(CrudService $service, ResultBuilder $result = null)
    {
        $this->service = $service;
        if($result === null) {
            $result = new ResultBuilder($this->service->getTableName());
        }
        parent::__construct($result);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param bool $pagination
     */
    public function index(Request $request, bool $pagination = true)
    {
        $queryParam = $this->parseQueryParams($request);
        if($request->query('export', null) !== null) {
            $queryParam->setPageSize(100000);
            $queryParam->setPageIndex(1);
            $result = $this->service->all($queryParam, $pagination);
            return $this->exportCsv($request, $result);
        }
        return $this->indexAction($queryParam, $pagination);
    }

    public function exportCsv(Request $request, ResultBuilder $result)
    {
        $fileName = 'workshop-users.csv';

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $models = $result->getDataAsModelList();
        $columns = array_keys($models[0]->attributesToArray());

        $callback = function() use($models, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($models as $model) {
                $model = $model->attributesToArray();
                $row = [];
                foreach ($columns as $column) {
                    $row[$column] = $model[$column];
                }
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function indexAction(?QueryParam $queryParam = null, bool $pagination = true)
    {
        $result = $this->service->all($queryParam, $pagination);
        return $this->response($result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request|array  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if($request->has('institute_id'))
        {
            $data = array_merge($this->getJson($request), ['institute_id' => $request->institute_id]);
        } else {
            $data = $this->getJson($request);
        }
        return $this->storeAction($data);
    }

    protected function storeAction($data)
    {
        if(empty($data))
        {
            $result = $this->result->status(504)->message('no data found in request');
        }
        else
        {
            $result = $this->service->create($data);
        }
        return $this->response($result);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        return $this->showAction($id);
    }

    protected function showAction($id)
    {
        $result = $this->service->find($id);
        return $this->response($result);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = $this->getJson($request);
        return $this->updateAction($data, $id);
    }

    protected function updateAction($data, $id)
    {
        if(intval($id) < 1)
        {
            $result = $this->result->status(402)->message('selected id is not in range');
        }
        else
        {
            if(empty($data))
            {
                $result = $this->result->status(504)->message('no data found in request');
            }
            else
            {
                $result = $this->service->update(intval($id), $data);
            }
        }
        return $this->response($result);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        return $this->destroyAction($id);
    }

    protected function destroyAction($id)
    {
        if(intval($id) < 1)
        {
            $result = $this->result->status(402)->message('selected id is not in range');
        }
        else
        {
            $result = $this->service->delete(intval($id));
        }
        return $this->response($result);
    }

    /**
     * Update a list of resources in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdate(Request $request)
    {
        $data = $this->getJson($request);
        return $this->bulkUpdateAction($data);
    }

    protected function bulkUpdateAction($data)
    {
        $result = $this->service->bulkUpdate($data);
        return $this->response($result);
    }

    /**
     * Update a list of resources in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdateOrInsert(Request $request)
    {
        $data = $this->getJson($request);
        return $this->bulkUpdateOrInsertAction($data);
    }

    protected function bulkUpdateOrInsertAction($data)
    {
        $result = $this->service->bulkUpdateOrInsert($data);
        return $this->response($result);
    }

    /**
     * Remove a list of resources from storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        $data = $this->getJson($request);
        return $this->bulkDeleteAction($data);
    }

    protected function bulkDeleteAction($data)
    {
        $result = $this->service->bulkDelete($data);
        return $this->response($result);
    }

    /**
     * Update the specified resource media file in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function createMedia(Request $request, $id)
    {
        $files = $request->allFiles();
        return $this->createMediaAction($files, $id);
    }

    /**
     * @param UploadedFile[] $files
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createMediaAction(array $files, $id)
    {
        if(intval($id) < 1)
        {
            $result = $this->result->status(402)->message('selected id is not in range');
        }
        else
        {
            if(empty($files))
            {
                $result = $this->result->status(402)->message('no file found in request');
            }
            else
            {
                $result = $this->service->createMedia(intval($id), $files);
            }
        }
        return $this->response($result);
    }

    /**
     * Update the specified resource media file in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMedia(Request $request, $id)
    {
        $files = $request->allFiles();
        return $this->updateMediaAction($files, $id);
    }

    /**
     * @param UploadedFile[] $files
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    protected function updateMediaAction(array $files, $id)
    {
        if(intval($id) < 1)
        {
            $result = $this->result->status(402)->message('selected id is not in range');
        }
        else
        {
            if(empty($files))
            {
                $result = $this->result->status(402)->message('no file found in request');
            }
            else
            {
                $result = $this->service->updateMedia(intval($id), $files);
            }
        }
        return $this->response($result);
    }

    /**
     * Destroy the specified resource media file in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyMedia(Request $request, $rId, $id)
    {
        return $this->destroyMediaAction($rId, $id, MediaTypes::ANY);
    }

    /**
     * @param $id
     * @param int $mediaType
     * @return \Illuminate\Http\JsonResponse
     */
    protected function destroyMediaAction($rId, $id, $mediaType = MediaTypes::ANY)
    {
        if(intval($rId) < 1)
        {
            $result = $this->result->status(402)->message('selected id is not in range');
        }
        else
        {
            $result = $this->service->deleteMedia(intval($rId), intval($id), $mediaType);
        }
        return $this->response($result);
    }
}
