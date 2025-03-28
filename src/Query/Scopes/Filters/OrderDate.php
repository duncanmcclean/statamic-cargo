<?php

namespace DuncanMcClean\Cargo\Query\Scopes\Filters;

use Illuminate\Support\Arr;
use Statamic\Query\Scopes\Filter;

class OrderDate extends Filter
{
    public $pinned = true;

    public static function title()
    {
        return __('Date');
    }

    public function fieldItems()
    {
        return [
            'date' => [
                'type' => 'date',
                'inline' => true,
                'latest_date' => now(tz: 'UTC')->format('Y-m-d'),
            ],
        ];
    }

    public function apply($query, $values)
    {
        $query->whereDate('date', Arr::get($values, 'date.date'));
    }

    public function badge($values)
    {
        return Arr::get($values, 'date.date');
    }

    public function visibleTo($key)
    {
        return $key === 'orders';
    }
}
