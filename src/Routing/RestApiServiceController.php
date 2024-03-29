<?php


namespace Ruinton\Routing;


use Illuminate\Http\Request;
use Ruinton\Service\ServiceInterface;
use Ruinton\Service\ServiceResult;

class RestApiServiceController extends RuintonController
{
    protected ServiceInterface $service;

    public function __construct(ServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param bool $pagination
     */
    public function index(Request $request, bool $pagination = true)
    {
        if($request->query('export', null) !== null) {
            $queryParam = $this->getQueryParams($request);
            $queryParam->setPageSize(10000);
            $queryParam->setPageNumber(1);
            $result = $this->service->all($queryParam, $pagination);
            return $this->exportCsv($request, $result);
        }
        return $this->indexAction($request, $pagination)->toJsonResponse();
    }

    protected function indexAction(Request $request, bool $pagination = true) : ServiceResult
    {
        $params = $this->getQueryParams($request);
        return $this->service->all($params, $pagination);
    }

    protected function exportCsv(Request $request, ServiceResult $result)
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request|array  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $this->getJsonRequest($request);
        if(empty($data))
        {
            return $this->generateResponse(504, 'Input error', 'no data found in request')->toJsonResponse();
        }
        return $this->storeAction($request, $data)->toJsonResponse();
    }

    protected function storeAction(Request $request, $data) : ServiceResult
    {
        return $this->service->create($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        return $this->showAction($request, $id)->toJsonResponse();
    }

    protected function showAction(Request $request, $id) : ServiceResult
    {
        return $this->service->find($id, $this->getQueryParams($request));
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
        $data = $this->getJsonRequest($request);
        if(intval($id) < 1)
        {
            return $this->generateResponse(402, 'Input error', 'selected id is not in range')->toJsonResponse();
        }
        if(empty($data))
        {
            return $this->generateResponse(504, 'Input error', 'no data found in request')->toJsonResponse();
        }
        return $this->updateAction($request, $id, $data)->toJsonResponse();
    }

    protected function updateAction(Request $request, $id, $data) : ServiceResult
    {
        return $this->service->update(intval($id), $data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        if(intval($id) < 1)
        {
            return $this->generateResponse(402, 'Input error', 'selected id is not in range')->toJsonResponse();
        }
        return $this->destroyAction($request, $id)->toJsonResponse();
    }

    protected function destroyAction(Request $request, $id) : ServiceResult
    {
        return $this->service->delete(intval($id));
    }

    /**
     * Swap priorities
     */
    public function swapPriority(Request $request, $fromId, $toId)
    {
        if(intval($fromId) < 1)
        {
            return $this->generateResponse(404, 'Input error', 'selected from model id is not in range')->toJsonResponse();
        }
        if(intval($toId) < 1)
        {
            return $this->generateResponse(404, 'Input error', 'selected to model id is not in range')->toJsonResponse();
        }
        return $this->service->swapPriority($fromId, $toId)->toJsonResponse();
    }

    /**
     * Update a list of resources in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdate(Request $request)
    {
        $data = $this->getJsonRequest($request);
        if(empty($data))
        {
            return $this->generateResponse(504, 'Input error', 'no data found in request')->toJsonResponse();
        }
        return $this->bulkUpdateAction($request, $data)->toJsonResponse();
    }

    protected function bulkUpdateAction(Request $request, $data) : ServiceResult
    {
        return $this->service->bulkUpdate($data);
    }

    /**
     * Update a list of resources in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdateOrInsert(Request $request)
    {
        $data = $this->getJsonRequest($request);
        if(empty($data))
        {
            return $this->generateResponse(504, 'Input error', 'no data found in request')->toJsonResponse();
        }
        return $this->bulkUpdateOrInsertAction($request, $data)->toJsonResponse();
    }

    protected function bulkUpdateOrInsertAction(Request $request, $data) : ServiceResult
    {
        return $this->service->bulkUpdateOrInsert($data);
    }

    /**
     * Remove a list of resources from storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        $data = $this->getJsonRequest($request);
        if(empty($data))
        {
            return $this->generateResponse(504, 'Input error', 'no data found in request')->toJsonResponse();
        }
        return $this->bulkDeleteAction($request, $data)->toJsonResponse();
    }

    protected function bulkDeleteAction(Request $request, $data) : ServiceResult
    {
        return $this->service->bulkDelete($data);
    }

    public function uploadMedia(Request $request)
    {
        $files = $request->allFiles();
        return $this->uploadMediaAction($request, $files);
    }

    protected function uploadMediaAction(Request $request, array $files)
    {
        if(empty($files))
        {
            return $this->generateResponse(504, 'Input error', 'no file found in request')->toJsonResponse();
        }
        else
        {
            return $this->service->createMedia($files)->toJsonResponse();
        }
    }

    public function deleteMedia(Request $request, $id)
    {
        return $this->deleteMediaAction($request, $id);
    }

    protected function deleteMediaAction($request, $id)
    {
        if(intval($id) < 1)
        {
            return $this->generateResponse(504, 'Input error', 'selected id is not in range')->toJsonResponse();
        }
        else
        {
            return $this->service->deleteMedia($id)->toJsonResponse();
        }
    }
}
