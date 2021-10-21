<?php


namespace Ruinton\Routing;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Ruinton\Parser\QueryParser;

class RuintonController extends Controller
{

//    protected function response(ResultBuilder $resultBuilder)
//    {
//        return response()->json($resultBuilder->build(), $resultBuilder->getStatus());
//    }

    protected function parseQueryParams(Request $request)
    {
        return QueryParser::Parse($request);
    }
}
