<?php

namespace DuncanMcClean\Cargo\Http\Controllers\CP\Discounts;

use DuncanMcClean\Cargo\Cargo;
use DuncanMcClean\Cargo\Contracts\Discounts\Discount as DiscountContract;
use DuncanMcClean\Cargo\Facades\Discount;
use DuncanMcClean\Cargo\Http\Resources\CP\Discounts\Discounts;
use Illuminate\Http\Request;
use Statamic\CP\Column;
use Statamic\CP\PublishForm;
use Statamic\Facades\Scope;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Statamic\Support\Arr;

class DiscountController extends CpController
{
    use QueriesFilters;

    public function index(FilteredRequest $request)
    {
        $this->authorize('index', DiscountContract::class, __('You are not authorized to view discounts.'));

        if ($request->wantsJson()) {
            $query = $this->indexQuery();

            $activeFilterBadges = $this->queryFilters($query, $request->filters);

            $sortField = request('sort');
            $sortDirection = request('order', 'asc');

            if (! $sortField && ! request('search')) {
                $sortField = 'name';
                $sortDirection = 'desc';
            }

            if ($sortField) {
                $query->orderBy($sortField, $sortDirection);
            }

            $discounts = $query->paginate(request('perPage'));

            return (new Discounts($discounts))
                ->blueprint(Discount::blueprint())
                ->columnPreferenceKey('cargo.discounts.columns')
                ->additional(['meta' => [
                    'activeFilterBadges' => $activeFilterBadges,
                ]]);
        }

        $blueprint = Discount::blueprint();

        $columns = $blueprint
            ->columns()
            ->put('status', Column::make('status')
                ->listable(true)
                ->visible(true)
                ->defaultVisibility(true)
                ->sortable(false))
            ->setPreferred('cargo.discounts.columns')
            ->rejectUnlisted()
            ->values();

        if (Discount::query()->count() === 0) {
            return view('cargo::cp.discounts.empty');
        }

        return view('cargo::cp.discounts.index', [
            'blueprint' => $blueprint,
            'columns' => $columns,
            'filters' => Scope::filters('discounts'),
        ]);
    }

    protected function indexQuery()
    {
        $query = Discount::query();

        if ($search = request('search')) {
            $query
                ->where('name', 'LIKE', '%'.$search.'%')
                ->orWhere('discount_code', 'LIKE', '%'.$search.'%');
        }

        return $query;
    }

    public function create(Request $request)
    {
        $this->authorize('create', DiscountContract::class);

        return PublishForm::make(Discount::blueprint())
            ->icon(Cargo::svg('discounts'))
            ->title(__('Create Discount'))
            ->submittingTo(cp_route('cargo.discounts.store'), 'POST');
    }

    public function store(Request $request)
    {
        $this->authorize('store', DiscountContract::class);

        $values = PublishForm::make(Discount::blueprint())->submit($request->all());

        $discount = Discount::make()
            ->name(Arr::pull($values, 'name'))
            ->type(Arr::pull($values, 'type'))
            ->data($values);

        $discount->save();

        return ['redirect' => $discount->editUrl()];
    }

    public function edit(Request $request, $discount)
    {
        $this->authorize('view', $discount);

        return PublishForm::make(Discount::blueprint())
            ->icon(Cargo::svg('discounts'))
            ->title($discount->name())
            ->values($discount->data()->merge([
                'name' => $discount->name(),
                'type' => $discount->type(),
            ])->all())
            ->submittingTo($discount->updateUrl());
    }

    public function update(Request $request, $discount)
    {
        $this->authorize('update', $discount);

        $fields = Discount::blueprint()
            ->fields()
            ->setParent($this->parent ?? null)
            ->addValues($request->all());

        $fields->validator()->withReplacements(['handle' => $discount->handle()])->validate();

        $values = $fields->process()->values()->all();

        $discount
            ->name(Arr::pull($values, 'name'))
            ->type(Arr::pull($values, 'type'))
            ->data($values);

        $discount->save();
    }
}
