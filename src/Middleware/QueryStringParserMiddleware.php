<?php

namespace Ruinton\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ruinton\Parser\QueryParser;

class QueryStringParserMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        $queryParams = QueryParser::Parse($request);
        $request->merge([
            'queryParams' => $queryParams
        ]);
        return $next($request);
    }
}
