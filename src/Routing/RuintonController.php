<?php


namespace Ruinton\Routing;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Ruinton\Parser\QueryParam;
use Ruinton\Parser\QueryParser;
use Ruinton\Service\ServiceResult;

class RuintonController extends Controller
{

//    protected function response(ResultBuilder $resultBuilder)
//    {
//        return response()->json($resultBuilder->build(), $resultBuilder->getStatus());
//    }

    protected function generateResponse(int $status, string $message) : ServiceResult {
        $response = new ServiceResult(self::class);
        $response->status($status)->message($message);
        return $response;
    }

    protected function getQueryParams(Request $request) : QueryParam {
        return $request->queryParams ?? $this->parseQueryParams($request);
    }

    private function parseQueryParams(Request $request) : QueryParam
    {
        return QueryParser::Parse($request);
    }
}
