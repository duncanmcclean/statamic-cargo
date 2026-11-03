<?php

namespace DuncanMcClean\Cargo\Http\Controllers\CP\Discounts;

use DuncanMcClean\Cargo\Contracts\Discounts\Discount as DiscountContract;
use DuncanMcClean\Cargo\Facades\Discount;
use DuncanMcClean\Cargo\Http\Resources\CP\Discounts\Discounts;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\CP\Column;
use Statamic\CP\PublishForm;
use Statamic\Facades\Scope;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Query\OrderBy;
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

            $sortField = OrderBy::column(request('sort'));
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
            return Inertia::render('cargo::Discounts/Empty', [
                'createUrl' => cp_route('cargo.discounts.create'),
            ]);
        }

        return Inertia::render('cargo::Discounts/Index', [
            'blueprint' => $blueprint,
            'columns' => $columns,
            'filters' => Scope::filters('discounts'),
            'createUrl' => cp_route('cargo.discounts.create'),
            'actionUrl' => cp_route('cargo.discounts.actions.run'),
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

        $blueprint = Discount::blueprint();
        $fields = $blueprint->fields()->preProcess();

        return Inertia::render('cargo::Discounts/Create', [
            'blueprint' => $blueprint->toPublishArray(),
            'title' => __('Create Discount'),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'submitUrl' => cp_route('cargo.discounts.store'),
            'createAnotherUrl' => cp_route('cargo.discounts.create'),
            'listingUrl' => cp_route('cargo.discounts.index'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('store', DiscountContract::class);

        $values = PublishForm::make(Discount::blueprint())->submit($request->all());

        $discount = Discount::make()
            ->title(Arr::pull($values, 'title'))
            ->type(Arr::pull($values, 'type'))
            ->data($values);

        $discount->save();

        return ['redirect' => $discount->editUrl()];
    }

    public function edit(Request $request, $discount)
    {
        $this->authorize('view', $discount);

        $blueprint = Discount::blueprint();

        $fields = $blueprint
            ->fields()
            ->addValues($discount->data()->merge([
                'title' => $discount->title(),
                'type' => $discount->type(),
            ])->all())
            ->preProcess();

        return Inertia::render('cargo::Discounts/Edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'title' => $discount->title(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'submitUrl' => $discount->updateUrl(),
            'createAnotherUrl' => cp_route('cargo.discounts.create'),
            'listingUrl' => cp_route('cargo.discounts.index'),
        ]);
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
            ->title(Arr::pull($values, 'title'))
            ->type(Arr::pull($values, 'type'))
            ->data($values);

        $discount->save();
    }
}
