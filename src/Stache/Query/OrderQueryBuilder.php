<?php

namespace DuncanMcClean\Cargo\Stache\Query;

use DuncanMcClean\Cargo\Contracts\Orders\QueryBuilder;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use DuncanMcClean\Cargo\Query\QueriesLineItems;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Statamic\Data\DataCollection;
use Statamic\Stache\Query\Builder;

class OrderQueryBuilder extends Builder implements QueryBuilder
{
    use QueriesLineItems;

    public function whereStatus(string|OrderStatus $status): self
    {
        if ($status instanceof OrderStatus) {
            $status = $status->value;
        }

        $this->where('status', $status);

        return $this;
    }

    public function whereNotStatus(string|OrderStatus $status): self
    {
        if ($status instanceof OrderStatus) {
            $status = $status->value;
        }

        $this->where('status', '!=', $status);

        return $this;
    }

    public function getByCustomer(): LazyCollection
    {
        $ordersByCustomer = [];

        $ids = $this->pluck('id')->all();
        $index = $this->store->index('customer');

        foreach ($index->getItems() as $key => $value) {
            if (! in_array($key, $ids)) {
                continue;
            }

            isset($ordersByCustomer[$value])
                ? $ordersByCustomer[$value][] = $key
                : $ordersByCustomer[$value] = [$key];
        }

        return LazyCollection::make($ordersByCustomer)
            ->map(fn ($items) => collect($items)->map(fn ($id) => $this->store->getItem($id)));
    }

    public function sum(string $column)
    {
        return $this->pluck($column)->sum();
    }

    protected function collect($items = [])
    {
        return DataCollection::make($items);
    }

    protected function getFilteredKeys()
    {
        if (! empty($this->wheres)) {
            return $this->getKeysWithWheres($this->wheres);
        }

        return collect($this->store->paths()->keys());
    }

    protected function getKeysWithWheres($wheres)
    {
        return collect($wheres)->reduce(function ($ids, $where) {
            $keys = $where['type'] == 'Nested'
                ? $this->getKeysWithWheres($where['query']->wheres)
                : $this->getKeysWithWhere($where);

            return $this->intersectKeysFromWhereClause($ids, $keys, $where);
        });
    }

    protected function getKeysWithWhere($where)
    {
        $items = app('stache')
            ->store('orders')
            ->index($where['column'])->items();

        $method = 'filterWhere'.$where['type'];

        return $this->{$method}($items, $where)->keys();
    }

    protected function getOrderKeyValuesByIndex()
    {
        return collect($this->orderBys)->mapWithKeys(function ($orderBy) {
            $items = $this->store->index($orderBy->sort)->items()->all();

            return [$orderBy->sort => $items];
        });
    }
}
