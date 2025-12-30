<?php

namespace DuncanMcClean\Cargo\Query\Scopes\Filters;

use Illuminate\Support\Carbon;
use Statamic\Query\Scopes\Filter;
use Statamic\Support\Arr;

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
            'operator' => [
                'type' => 'select',
                'placeholder' => __('Select Operator'),
                'options' => [
                    '<' => __('Before'),
                    '>' => __('After'),
                    'between' => __('Between'),
                    'null' => __('Empty'),
                    'not-null' => __('Not empty'),
                ],
            ],
            'value' => [
                'type' => 'date',
                'full_width' => true,
                'if' => [
                    'operator' => 'contains_any >, <',
                ],
            ],
            'range_value' => [
                'type' => 'date',
                'mode' => 'range',
                'full_width' => true,
                'if' => [
                    'operator' => 'between',
                ],
            ],
        ];
    }

    public function apply($query, $values)
    {
        $operator = $values['operator'];

        if ($operator == 'between') {
            $query->whereDate('date', '>=', Carbon::parse($values['range_value']['start']));
            $query->whereDate('date', '<=', Carbon::parse($values['range_value']['end']));

            return;
        }

        $value = Carbon::parse($values['value']);

        match ($operator) {
            'null' => $query->whereNull('date'),
            'not-null' => $query->whereNotNull('date'),
            default => $query->where('date', $operator, $value),
        };
    }

    public function badge($values)
    {
        $operator = $values['operator'];
        $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");

        if ($operator == 'between') {
            return 'Date'.' '.strtolower($translatedOperator).' '.$values['range_value']['start'].' '.__('and').' '.$values['range_value']['end'];
        }

        return 'Date'.' '.strtolower($translatedOperator).' '.$values['value'];
    }

    public function visibleTo($key)
    {
        return $key === 'orders';
    }
}
