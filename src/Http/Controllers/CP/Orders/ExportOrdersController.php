<?php

namespace DuncanMcClean\Cargo\Http\Controllers\CP\Orders;

use DuncanMcClean\Cargo\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Orders\CsvExporter;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Query\OrderBy;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;

class ExportOrdersController extends CpController
{
    use QueriesFilters, QueriesOrderSearch;

    public function __invoke(FilteredRequest $request)
    {
        $this->authorize('index', OrderContract::class, __('You are not authorized to export orders.'));

        $query = Order::query();

        $this->applyOrderSearch($query, $request->search);
        $this->queryFilters($query, $request->filters);

        $query->orderBy(OrderBy::column($request->sort) ?? 'order_number', $request->input('order', 'desc'));

        $columns = $request->filled('columns') ? explode(',', $request->columns) : null;

        return (new CsvExporter($query, $columns))->download();
    }
}
