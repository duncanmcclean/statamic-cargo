<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use DuncanMcClean\Cargo\Cargo;
use DuncanMcClean\Cargo\Facades\Order;
use Illuminate\Support\Str;
use Statamic\CP\Column;
use Statamic\CP\Columns;
use Statamic\Facades\User;
use Statamic\Fieldtypes\Relationship;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Statamic\Statamic;

class Orders extends Relationship
{
    use QueriesFilters;

    protected $canEdit = true;
    protected $canCreate = false;
    protected $canSearch = false;
    protected $formComponent = 'order-publish-form';
    protected $formComponentProps = [
        'blueprint' => 'blueprint',
        'reference' => 'reference',
        'initialTitle' => 'title',
        'initialValues' => 'values',
        'initialExtraValues' => 'extraValues',
        'initialMeta' => 'meta',
        'initialReadOnly' => 'readOnly',
        'actions' => 'actions',
        'itemActions' => 'itemActions',
        'itemActionUrl' => 'itemActionUrl',
        'canEditBlueprint' => 'canEditBlueprint',
    ];
    protected $activeFilterBadges;

    public function icon()
    {
        return Statamic::svg('icons/shopping-cart');
    }

    protected function toItemArray($id)
    {
        $order = Order::find($id);

        return [
            'id' => $order->id(),
            'reference' => $order->reference(),
            'title' => "#{$order->orderNumber()}",
            'hint' => $order->date()->format('Y-m-d'),
            'edit_url' => cp_route('cargo.orders.edit', $order->id()),
        ];
    }

    public function getIndexItems($request)
    {
        $query = $this->getIndexQuery($request);

        $filters = $request->filters;

        $this->activeFilterBadges = $this->queryFilters($query, $filters);

        if ($sort = $this->getSortColumn($request)) {
            $query->orderBy($sort, $this->getSortDirection($request));
        }

        return ($paginate = $request->boolean('paginate', true)) ? $query->paginate() : $query->get();
    }

    public function getResourceCollection($request, $items)
    {
        return (new \DuncanMcClean\Cargo\Http\Resources\CP\Orders\Orders($items))
            ->blueprint(Order::blueprint())
            ->columnPreferenceKey('cargo.orders.columns')
            ->additional(['meta' => [
                'activeFilterBadges' => $this->activeFilterBadges,
            ]]);
    }

    protected function getIndexQuery($request)
    {
        $query = Order::query();

        if ($search = $request->search) {
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

        if ($site = $request->site) {
            $query->where('site', $site);
        }

        if ($request->exclusions) {
            $query->whereNotIn('id', $request->exclusions);
        }

        $this->applyIndexQueryScopes($query, $request->all());

        return $query;
    }

    public function getColumns()
    {
        $columns = Order::blueprint()->columns();

        $this->addColumn($columns, 'status');

        $columns->setPreferred('cargo.orders.columns');

        return $columns
            ->rejectUnlisted()
            ->reject(fn ($column) => $column->field() === 'status') // The "status" column doesn't play well with the generic listing
            ->values();
    }

    private function addColumn(Columns $columns, string $columnKey): void
    {
        $column = Column::make($columnKey)
            ->listable(true)
            ->visible(true)
            ->defaultVisibility(true)
            ->sortable(false);

        $columns->put($columnKey, $column);
    }

    public function augment($values)
    {
        return collect($values)->map(fn ($id) => Order::find($id)?->toShallowAugmentedArray())->filter()->all();
    }
}
