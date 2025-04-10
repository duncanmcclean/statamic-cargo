<?php

namespace DuncanMcClean\Cargo\Query\Scopes\Filters;

use DuncanMcClean\Cargo\Discounts\DiscountType as DiscountTypeEnum;
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
                'options' => collect(DiscountTypeEnum::cases())
                    ->mapWithKeys(fn ($enum) => [$enum->value => DiscountTypeEnum::label($enum)])
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
        return DiscountTypeEnum::label(DiscountTypeEnum::from($values['type']));
    }

    public function visibleTo($key)
    {
        return in_array($key, ['discounts']);
    }
}
