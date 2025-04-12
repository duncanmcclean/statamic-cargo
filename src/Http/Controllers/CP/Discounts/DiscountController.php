<?php

namespace DuncanMcClean\Cargo\Http\Controllers\CP\Discounts;

use DuncanMcClean\Cargo\Contracts\Discounts\Discount as DiscountContract;
use DuncanMcClean\Cargo\Facades\Discount;
use DuncanMcClean\Cargo\Http\Resources\CP\Discounts\Discount as DiscountResource;
use DuncanMcClean\Cargo\Http\Resources\CP\Discounts\Discounts;
use DuncanMcClean\Cargo\Rules\UniqueDiscountValue;
use Illuminate\Http\Request;
use Statamic\CP\Breadcrumbs;
use Statamic\Facades\Action;
use Statamic\Facades\Scope;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;

class DiscountController extends CpController
{
    use ExtractsFromDiscountFields, QueriesFilters;

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

        $columns = $blueprint->columns()
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

        $blueprint = Discount::blueprint();

        $values = Discount::make()->data()->all();

        $fields = $blueprint
            ->fields()
            ->addValues($values)
            ->preProcess();

        $values = $fields->values();

        $viewData = [
            'title' => __('Create Discount'),
            'actions' => [
                'save' => cp_route('cargo.discounts.store'),
            ],
            'values' => $values->all(),
            'meta' => $fields->meta(),
            'blueprint' => $blueprint->toPublishArray(),
            'breadcrumbs' => new Breadcrumbs([
                ['text' => __('Discounts'), 'url' => cp_route('cargo.discounts.index')],
            ]),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('cargo::cp.discounts.create', $viewData);
    }

    public function store(Request $request)
    {
        $this->authorize('store', DiscountContract::class);

        $blueprint = Discount::blueprint();

        $data = $request->all();

        $fields = $blueprint->fields()->addValues($data);

        $fields->validator()->withRules([
            'discount_code' => [new UniqueDiscountValue],
        ])->validate();

        $values = $fields->process()->values();

        $discount = Discount::make()
            ->name($values->get('name'))
            ->type($values->get('type'))
            ->data($values->except(['name', 'type']));

        $saved = $discount->save();

        return [
            'data' => (new DiscountResource($discount))->resolve()['data'],
            'saved' => $saved,
        ];
    }

    public function edit(Request $request, $discount)
    {
        $this->authorize('view', $discount);

        $blueprint = Discount::blueprint();
        $blueprint->setParent($discount);

        [$values, $meta] = $this->extractFromFields($discount, $blueprint);

        $viewData = [
            'title' => $discount->name(),
            'reference' => $discount->reference(),
            'actions' => [
                'save' => $discount->updateUrl(),
            ],
            'values' => array_merge($values, [
                'id' => $discount->handle(),
                'redeemed_count' => $discount->redeemedCount(),
            ]),
            'meta' => $meta,
            'blueprint' => $blueprint->toPublishArray(),
            'readOnly' => User::current()->cant('update', $discount),
            'breadcrumbs' => new Breadcrumbs([
                ['text' => __('Discounts'), 'url' => cp_route('cargo.discounts.index')],
            ]),
            'itemActions' => Action::for($discount, ['view' => 'form']),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        if ($request->has('created')) {
            session()->now('success', __('Discount created'));
        }

        return view('cargo::cp.discounts.edit', array_merge($viewData, [
            'discount' => $discount,
        ]));
    }

    public function update(Request $request, $discount)
    {
        $this->authorize('update', $discount);

        $blueprint = Discount::blueprint();

        $data = $request->except('id');

        $fields = $discount
            ->blueprint()
            ->fields()
            ->addValues($data);

        $fields
            ->validator()
            ->withRules([
                'discount_code' => [new UniqueDiscountValue(except: $discount->handle())],
            ])
            ->withReplacements([
                'id' => $discount->handle(),
            ])
            ->validate();

        $values = $fields->process()->values();

        $discount
            ->name($values->get('name'))
            ->type($values->get('type'))
            ->merge($values->except(['name', 'type']));

        $saved = $discount->save();

        [$values] = $this->extractFromFields($discount, $blueprint);

        return [
            'data' => array_merge((new DiscountResource($discount->fresh()))->resolve()['data'], [
                'values' => $values,
            ]),
            'saved' => $saved,
        ];
    }
}
