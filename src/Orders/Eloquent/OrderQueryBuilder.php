<?php

namespace DuncanMcClean\Cargo\Orders\Eloquent;

use Closure;
use DuncanMcClean\Cargo\Contracts\Orders\QueryBuilder;
use DuncanMcClean\Cargo\Query\Eloquent\QueriesCustomers;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Query\EloquentQueryBuilder;

class OrderQueryBuilder extends EloquentQueryBuilder implements QueryBuilder
{
    use QueriesCustomers;

    protected $columns = [
        'id', 'order_number', 'date', 'site', 'cart', 'status', 'customer', 'coupon', 'grand_total',
        'sub_total', 'discount_total', 'tax_total', 'shipping_total', 'line_items', 'data',
    ];

    public function orderBy($column, $direction = 'asc')
    {
        $column = $this->column($column);

        return parent::orderBy($column, $direction);
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $column = $this->column($column);

        if ($column === 'customer') {
            return $this->queryByCustomer($operator, $value, $boolean);
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    public function whereHasLineItem(?Closure $callback = null): self
    {
        $this->builder->whereHas('lineItems', $callback);

        return $this;
    }

    public function pluck($column, $key = null)
    {
        $column = $this->column($column);

        return $this->builder->pluck($column, $key);
    }

    protected function transform($items, $columns = ['*'])
    {
        return Collection::make($items)->map(function ($model) {
            return Order::fromModel($model);
        });
    }

    protected function column($column): string
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
