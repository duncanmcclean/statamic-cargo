<?php

namespace DuncanMcClean\Cargo\Query\Scopes\Filters;

use DuncanMcClean\Cargo\Facades\Order;
use Statamic\Query\Scopes\Filters\Fields as BaseFieldsFilter;

class Fields extends BaseFieldsFilter
{
    protected static $handle = 'cargo_fields';

    public function visibleTo($key)
    {
        return $key === 'orders';
    }

    protected function getFields()
    {
        return Order::blueprint()
            ->fields()
            ->all()
            ->filter
            ->isFilterable();
    }
}
