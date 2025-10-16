<?php

namespace DuncanMcClean\Cargo\Http\Controllers\CP\Orders;

use DuncanMcClean\Cargo\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Http\Resources\CP\Orders\Order as OrderResource;
use DuncanMcClean\Cargo\Http\Resources\CP\Orders\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Statamic\Facades\Action;
use Statamic\Facades\Scope;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;

class OrderController extends CpController
{
    use ExtractsFromOrderFields, QueriesFilters;

    public function index(FilteredRequest $request)
    {
        $this->authorize('index', OrderContract::class, __('You are not authorized to view orders.'));

        if ($request->wantsJson()) {
            $query = $this->indexQuery();

            $activeFilterBadges = $this->queryFilters($query, $request->filters);

            $sortField = request('sort');
            $sortDirection = request('order', 'asc');

            if (! $sortField && ! request('search')) {
                $sortField = 'order_number';
                $sortDirection = 'desc';
            }

            if ($sortField) {
                $query->orderBy($sortField, $sortDirection);
            }

            $orders = $query->paginate(request('perPage'));

            return (new Orders($orders))
                ->blueprint(Order::blueprint())
                ->columnPreferenceKey('cargo.orders.columns')
                ->additional(['meta' => [
                    'activeFilterBadges' => $activeFilterBadges,
                ]]);
        }

        $blueprint = Order::blueprint();

        $columns = $blueprint->columns()
            ->setPreferred('cargo.orders.columns')
            ->rejectUnlisted()
            ->values();

        return Inertia::render('cargo::Orders/Index', [
            'blueprint' => $blueprint,
            'columns' => $columns,
            'filters' => Scope::filters('orders'),
            'actionUrl' => cp_route('cargo.orders.actions.run'),
            'editBlueprintUrl' => cp_route('blueprints.additional.edit', ['cargo', 'order']),
            'canEditBlueprint' => User::current()->can('configure fields'),
        ]);
    }

    protected function indexQuery()
    {
        $query = Order::query();

        if ($search = request('search')) {
            $query
                ->where('id', $search)
                ->orWhere('date', 'LIKE', '%'.$search.'%')
                ->orWhere('order_number', 'LIKE', '%'.Str::remove('#', $search).'%')
                ->orWhere(function ($query) use ($search) {
                    $users = User::query()
                        ->where('name', 'LIKE', '%'.$search.'%')
                        ->orWhere('email', 'LIKE', '%'.$search.'%')
                        ->pluck('id')
                        ->all();

                    $query->whereIn('customer', $users);
                })
                ->orWhere('customer', "guest::$search%");
        }

        return $query;
    }

    public function edit(Request $request, $order)
    {
        $this->authorize('edit', $order);

        $blueprint = Order::blueprint();
        $blueprint->setParent($order);

        [$values, $meta, $extraValues] = $this->extractFromFields($order, $blueprint);

        $viewData = [
            'blueprint' => $blueprint->toPublishArray(),
            'reference' => $order->reference(),
            'title' => __('Order #:number', ['number' => $order->orderNumber()]),
            'actions' => [
                'save' => $order->updateUrl(),
                'editBlueprint' => cp_route('blueprints.additional.edit', ['cargo', 'order']),
                'packingSlip' => cp_route('cargo.orders.packing-slip', $order->id()),
            ],
            'values' => array_merge($values, [
                'id' => $order->id(),
                'status' => $order->status()->value,
            ]),
            'extraValues' => $extraValues,
            'meta' => $meta,
            'readOnly' => User::current()->cant('update', $order),
            'itemActions' => Action::for($order, ['view' => 'form']),
            'itemActionUrl' => cp_route('cargo.orders.actions.run'),
            'canEditBlueprint' => User::current()->can('configure fields'),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('cargo::cp.orders.edit', array_merge($viewData, [
            'order' => $order,
        ]));
    }

    public function update(Request $request, $order)
    {
        $this->authorize('update', $order);

        $blueprint = Order::blueprint();

        $data = collect($request->all())->except($except = [
            'id', 'customer', 'date', 'status', 'discount_total', 'grand_total', 'line_items', 'order_number',
            'payment_details', 'receipt', 'shipping_total', 'sub_total', 'tax_total', 'shipping_method',
        ])->all();

        $fields = $blueprint
            ->fields()
            ->addValues($data);

        $fields
            ->validator()
            ->withReplacements([
                'id' => $order->id(),
            ])
            ->validate();

        $values = $fields->process()->values()->except($except);

        if ($request->status) {
            $order->status($request->status);
        }

        $order->merge($values->all());

        $saved = $order->save();

        [$values, $meta, $extraValues] = $this->extractFromFields($order, $blueprint);

        return [
            'data' => array_merge_recursive((new OrderResource($order->fresh()))->resolve(), [
                'data' => [
                    'values' => $values,
                    'extraValues' => $extraValues,
                ],
            ]),
            'saved' => $saved,
        ];
    }
}
