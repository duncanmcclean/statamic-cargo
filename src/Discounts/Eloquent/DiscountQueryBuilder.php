<?php

namespace DuncanMcClean\Cargo\Discounts\Eloquent;

use DuncanMcClean\Cargo\Contracts\Discounts\Discount;
use DuncanMcClean\Cargo\Contracts\Discounts\QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Query\EloquentQueryBuilder;

class DiscountQueryBuilder extends EloquentQueryBuilder implements QueryBuilder
{
    protected $columns = [
        'id', 'handle', 'title', 'type', 'data',
    ];

    public function orderBy($column, $direction = 'asc')
    {
        $column = $this->column($column);

        return parent::orderBy($column, $direction);
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $column = $this->column($column);

        return parent::where($column, $operator, $value, $boolean);
    }

    public function pluck($column, $key = null)
    {
        $column = $this->column($column);

        return $this->builder->pluck($column, $key);
    }

    protected function transform($items, $columns = ['*'])
    {
        return Collection::make($items)->map(function ($model) {
            return app(Discount::class)::fromModel($model);
        });
    }

    protected function column($column)
    {
        if (! is_string($column)) {
            return $column;
        }

        if (! in_array($column, $this->columns)) {
            if (! Str::startsWith($column, 'data->')) {
                $column = 'data->'.$column;
            }
        }

        return $column;
    }
}
