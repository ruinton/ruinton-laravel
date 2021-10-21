<?php


namespace App\Classes;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProxyController extends ControllerBase
{
    protected $baseUrl;
    protected $accessToken;

    public function __construct($baseUrl, $accessToken = null)
    {
        parent::__construct();
        $this->baseUrl = $baseUrl;
        $this->accessToken = $accessToken;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        $route = $request->route()->uri;
        $params = $request->query();
        return $this->indexAction($route, $params);
    }

    protected function indexAction($route, $params)
    {
        $proxyRequest = Http::asJson()->withoutVerifying();
        if($this->accessToken !== null) {
            $proxyRequest->withToken($this->accessToken);
        }
        $response = $proxyRequest->acceptJson()->get(
            $this->baseUrl . '/' . $route,
            $params
        );
        return $response->json();
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
        $route = $request->route()->uri;
        return $this->storeAction($route, $data);
    }

    protected function storeAction($route, $data)
    {
        if(empty($data))
        {
            $result = $this->result->status(504)->message('no data found in request');
            return $this->response($result);
        }
        else
        {
            $proxyRequest = Http::asJson()->withoutVerifying();
            if($this->accessToken !== null) {
                $proxyRequest->withToken($this->accessToken);
            }
            $response = $proxyRequest->acceptJson()->post(
                $this->baseUrl . '/' . $route,
                $data
            );
            return $response->json();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show(Request $request, $id)
    {
        $route = $request->route()->uri;
        return $this->showAction($route);
    }

    protected function showAction($route)
    {
        $proxyRequest = Http::asJson()->withoutVerifying();
        if($this->accessToken !== null) {
            $proxyRequest->withToken($this->accessToken);
        }
        $response = $proxyRequest->acceptJson()->get(
            $this->baseUrl . '/' . $route
        );
        return $response;
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
        if($request->has('institute_id'))
        {
            $data = array_merge($this->getJson($request), ['institute_id' => $request->institute_id]);
        } else {
            $data = $this->getJson($request);
        }
        $route = $request->route()->uri;
        return $this->updateAction($route, $id, $data);
    }

    protected function updateAction($route, $id, $data)
    {
        if(empty($data))
        {
            $result = $this->result->status(504)->message('no data found in request');
            return $this->response($result);
        }
        else
        {
            $proxyRequest = Http::asJson()->withoutVerifying();
            if($this->accessToken !== null) {
                $proxyRequest->withToken($this->accessToken);
            }
            $response = $proxyRequest->acceptJson()->put(
                $this->baseUrl . '/' . explode('{', $route)[0] . $id,
                $data
            );
            return $response;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy(Request $request, $id)
    {
        $route = $request->route()->uri;
        return $this->destroyAction($route, $id);
    }

    protected function destroyAction($route, $id)
    {
        $proxyRequest = Http::asJson()->withoutVerifying();
        if($this->accessToken !== null) {
            $proxyRequest->withToken($this->accessToken);
        }
        $response = $proxyRequest->acceptJson()->delete(
            $this->baseUrl . '/' . explode('{', $route)[0] . $id,
        );
        return $response;
    }

}
