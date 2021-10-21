<?php

namespace Ruinton\Middleware;

use Closure;
use Ruinton\Parser\QueryParser;

class QueryStringParserMiddleware
{

    public function handle($request, Closure $next)
    {
        $queryParams = QueryParser::Parse($request);
        $request->merge([
            'queryParams' => $queryParams
        ]);
        return $next($request);
    }
}
