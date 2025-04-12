<?php

namespace DuncanMcClean\Cargo\Query\Scopes\Filters;

use DuncanMcClean\Cargo\Facades;
use Statamic\Query\Scopes\Filter;

class DiscountType extends Filter
{
    public $pinned = true;

    public static function title()
    {
        return __('Type');
    }

    public function fieldItems()
    {
        return [
            'type' => [
                'type' => 'radio',
                'options' => Facades\DiscountType::all()
                    ->mapWithKeys(fn ($discountType) => [$discountType->handle() => $discountType->title()])
                    ->all(),
            ],
        ];
    }

    public function apply($query, $values)
    {
        $query->where('type', $values['type']);
    }

    public function badge($values)
    {
        return Facades\DiscountType::find($values['type'])->title();
    }

    public function visibleTo($key)
    {
        return in_array($key, ['discounts']);
    }
}
