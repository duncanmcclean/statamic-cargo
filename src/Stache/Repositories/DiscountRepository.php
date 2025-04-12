<?php

namespace DuncanMcClean\Cargo\Stache\Repositories;

use DuncanMcClean\Cargo\Contracts\Discounts\Discount;
use DuncanMcClean\Cargo\Contracts\Discounts\DiscountRepository as RepositoryContract;
use DuncanMcClean\Cargo\Contracts\Discounts\QueryBuilder;
use DuncanMcClean\Cargo\Discounts\Blueprint;
use DuncanMcClean\Cargo\Exceptions\DiscountNotFound;
use Statamic\Fields\Blueprint as StatamicBlueprint;
use Statamic\Stache\Stache;
use Statamic\Support\Str;

class DiscountRepository implements RepositoryContract
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('discounts');
    }

    public function all()
    {
        return $this->query()->get();
    }

    public function query()
    {
        return app(QueryBuilder::class);
    }

    public function find($handle): ?Discount
    {
        return $this->query()->where('handle', $handle)->first();
    }

    public function findOrFail($handle): Discount
    {
        $discount = $this->find($handle);

        if (! $discount) {
            throw new DiscountNotFound("Discount [{$handle}] could not be found.");
        }

        return $discount;
    }

    public function findByDiscountCode(string $code): ?Discount
    {
        return $this->query()->where('discount_code', strtoupper($code))->first();
    }

    public function make(): Discount
    {
        return app(Discount::class);
    }

    public function save(Discount $discount): void
    {
        if (! $discount->handle()) {
            $discount->handle(Str::slug($discount->name));
        }

        $this->store->save($discount);
    }

    public function delete(Discount $discount): void
    {
        $this->store->delete($discount);
    }

    public function blueprint(): StatamicBlueprint
    {
        return (new Blueprint)();
    }

    public static function bindings(): array
    {
        return [
            Discount::class => \DuncanMcClean\Cargo\Discounts\Discount::class,
            \DuncanMcClean\Cargo\Contracts\Discounts\QueryBuilder::class => \DuncanMcClean\Cargo\Stache\Query\DiscountQueryBuilder::class,
        ];
    }
}
