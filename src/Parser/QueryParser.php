<?php

namespace Ruinton\Parser;

use Illuminate\Http\Request;
use Ruinton\Enums\FilterOperators;
use Ruinton\Enums\SortOrder;

class QueryParser
{
    public static function Parse(Request $request) : QueryParam {
        $queryParam = new QueryParam();
        if($request->has('page')) {
            $page = $request->query('page');
            if(isset($page['number']))
            {
                $queryParam->setPageNumber(intval($page['number']));
            }
            if(isset($page['size']))
            {
                $queryParam->setPageSize(intval($page['size']));
            }
        }
        if($request->has('trashed')) {
            if($request->query('trashed', true)) {
                $queryParam->showTrashed();
            }
        }
        if($request->has('filter')) {
            $filters = $request->query('filter');
            if(is_array($filters))
            {
                foreach ($filters as $key => $filter)
                {
                    if(is_array($filter)) {
                        $queryParam->addFilter($key.'closure', function($q) use ($filter, $key) {
                            foreach ($filter as $column => $f) {
                                if(str_contains($f, ':')) {
                                    $parts = explode(':', $f);
                                    $q->where($column, FilterOperators::getOperatorByFilterName($parts[0]), $parts[1], $key);
                                } else {
                                    $q->where($column, '=', $f, $key);
                                }
                            }
                        }, FilterOperators::CLOSURE);
                    }
                    else if(str_contains($filter, ':')) {
                        $parts = explode(':', $filter);
                        $queryParam->addFilter($key, $parts[1], FilterOperators::getOperatorByFilterName($parts[0]));
                    } else {
                        $queryParam->addFilter($key, $filter);
                    }
                }
            }
        }
        if($request->has('sort')) {
            $sorts = $request->query('sort');
            $sortsExpressions = [];
            if(str_contains(',', $sorts)) {
                $sortsExpressions = explode(',', $sorts);
            }
            foreach ($sortsExpressions as $sort) {
                $queryParam->addSort(ltrim($sort, '-'),
                    $sort[0] === '-' ? SortOrder::DESCENDING : SortOrder::ASCENDING);
            }
        }
        if($request->has('include')) {
            $include = $request->query('include');
            if(str_contains(',', $include)) {
                $include = explode(',', $include);
                $queryParam->setWith($include);
            }else {
                $queryParam->addWith($include);
            }
        }
        if($request->has('data')) {
            $queryParam->setData($request->query('data'));
        }
        return $queryParam;
    }
}
