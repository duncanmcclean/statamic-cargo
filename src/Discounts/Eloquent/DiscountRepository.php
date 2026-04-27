<?php

namespace DuncanMcClean\Cargo\Discounts\Eloquent;

use DuncanMcClean\Cargo\Contracts\Discounts\Discount;
use DuncanMcClean\Cargo\Contracts\Discounts\DiscountRepository as RepositoryContract;
use DuncanMcClean\Cargo\Contracts\Discounts\QueryBuilder;
use DuncanMcClean\Cargo\Stache;
use Illuminate\Support\Str;

class DiscountRepository extends Stache\Repositories\DiscountRepository implements RepositoryContract
{
    public function query(): QueryBuilder
    {
        return app(QueryBuilder::class, ['builder' => app('cargo.discounts.eloquent.model')::query()]);
    }

    public function save(Discount $discount): void
    {
        if (! $discount->handle()) {
            $discount->handle(Str::slug($discount->title()));
        }

        $model = $discount->toModel();

        $model->save();

        $model = $model->fresh();

        $discount->model($model);
    }

    public function delete(Discount $discount): void
    {
        $discount->model()->delete();
    }

    public static function bindings(): array
    {
        return [
            Discount::class => \DuncanMcClean\Cargo\Discounts\Eloquent\Discount::class,
            QueryBuilder::class => DiscountQueryBuilder::class,
        ];
    }
}
