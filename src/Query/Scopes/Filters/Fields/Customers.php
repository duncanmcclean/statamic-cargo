<?php

namespace DuncanMcClean\Cargo\Query\Scopes\Filters\Fields;

use Statamic\Facades\User;
use Statamic\Query\Scopes\Filters\Fields\FieldtypeFilter;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Customers extends FieldtypeFilter
{
    public function fieldItems()
    {
        return [
            'operator' => [
                'type' => 'select',
                'placeholder' => __('Select Operator'),
                'options' => [
                    '=' => __('Is'),
                    'null' => __('Empty'),
                    'not-null' => __('Not empty'),
                ],
                'default' => '=',
            ],
            'value' => [
                'type' => 'users',
                'max_items' => 1,
                'mode' => 'select',
                'if' => [
                    'operator' => 'contains_any =',
                ],
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $operator = $values['operator'];

        if (in_array($operator, ['null', 'not-null'])) {
            match ($operator) {
                'null' => $query->whereNull($handle),
                'not-null' => $query->whereNotNull($handle),
            };

            return;
        }

        if ($user = $values['value']) {
            $query->where($handle, $user);
        }
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = $values['operator'];
        $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");

        if (in_array($operator, ['null', 'not-null'])) {
            return $field.' '.strtolower($translatedOperator);
        }

        if (! $user = User::find($values['value'])) {
            return null;
        }

        return $field.' '.strtolower($translatedOperator).' '.($user->name() ?? $user->email());
    }
}
