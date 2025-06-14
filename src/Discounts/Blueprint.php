<?php

namespace DuncanMcClean\Cargo\Discounts;

use DuncanMcClean\Cargo\Discounts\Types\DiscountType;
use DuncanMcClean\Cargo\Facades;
use DuncanMcClean\Cargo\Rules\UniqueDiscountValue;
use Statamic\Fields\Blueprint as FieldsBlueprint;

class Blueprint
{
    public function __invoke(): FieldsBlueprint
    {
        return \Statamic\Facades\Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'display' => __('General'),
                    'sections' => [
                        [
                            'display' => __('Details'),
                            'fields' => [
                                [
                                    'handle' => 'name',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('Name'),
                                        'instructions' => __('cargo::messages.discounts.name'),
                                        'listable' => true,
                                        'width' => 50,
                                        'validate' => ['required', 'string', 'max:255'],
                                    ],
                                ],
                                [
                                    'handle' => 'type',
                                    'field' => [
                                        'type' => 'select',
                                        'display' => __('Type'),
                                        'instructions' => __('cargo::messages.discounts.type'),
                                        'options' => Facades\DiscountType::all()
                                            ->mapWithKeys(fn ($discountType) => [$discountType->handle() => $discountType->title()])
                                            ->all(),
                                        'clearable' => false,
                                        'multiple' => false,
                                        'push_tags' => false,
                                        'width' => 50,
                                        'validate' => ['required'],
                                        'listable' => true,
                                        'max_items' => 1,
                                    ],
                                ],
                                [
                                    'handle' => 'start_date',
                                    'field' => ['type' => 'date', 'display' => __('Start Date'), 'width' => 50, 'listable' => 'hidden'],
                                ],
                                [
                                    'handle' => 'end_date',
                                    'field' => ['type' => 'date', 'display' => __('End Date'), 'width' => 50, 'listable' => 'hidden'],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Conditions'),
                            'fields' => [
                                [
                                    'handle' => 'discount_code',
                                    'field' => [
                                        'type' => 'discount_code',
                                        'display' => __('Discount code'),
                                        'instructions' => __('cargo::messages.discounts.discount_code'),
                                        'listable' => true,
                                        'width' => 33,
                                        'validate' => [
                                            'uppercase',
                                            function ($attribute, $value, $fail) {
                                                if (! preg_match('/^[a-zA-Z][a-zA-Z0-9]*(?:_{0,1}[a-zA-Z0-9])*$/', $value)) {
                                                    $fail('statamic::validation.handle')->translate();
                                                }
                                            },
                                            'new \DuncanMcClean\Cargo\Rules\UniqueDiscountValue({handle})',
                                        ],
                                    ],
                                ],
                                [
                                    'handle' => 'maximum_uses',
                                    'field' => [
                                        'type' => 'integer',
                                        'display' => __('Maximum uses'),
                                        'instructions' => __('cargo::messages.discounts.maximum_uses'),
                                        'width' => 33,
                                        'listable' => 'hidden',
                                    ],
                                ],
                                [
                                    'handle' => 'minimum_order_value',
                                    'field' => [
                                        'type' => 'money',
                                        'display' => __('Minimum order value'),
                                        'instructions' => __('cargo::messages.discounts.minimum_order_value'),
                                        'width' => 33,
                                        'listable' => 'hidden',
                                    ],
                                ],
                            ],
                        ],
                        ...Facades\DiscountType::all()
                            ->filter(fn (DiscountType $discountType) => $discountType->fields()->items()->isNotEmpty())
                            ->map(function (DiscountType $discountType) {
                                return [
                                    'display' => __($discountType->title()),
                                    'fields' => collect($discountType->fieldItems())
                                        ->map(fn ($field, $handle) => [
                                            'handle' => $handle,
                                            'field' => [
                                                ...$field,
                                                'listable' => false,
                                                'if' => [
                                                    ...$field['if'] ?? [],
                                                    'type' => "equals {$discountType->handle()}",
                                                ],
                                                'validate' => collect($field['validate'] ?? [])
                                                    ->flatMap(function ($rule) use ($discountType) {
                                                        if ($rule === 'required') {
                                                            return ['nullable', "required_if:type,{$discountType->handle()}"];
                                                        }

                                                        return [$rule];
                                                    })
                                                    ->values()
                                                    ->ray()
                                                    ->all(),
                                            ],
                                        ])->all(),
                                ];
                            })
                            ->values()->all(),
                    ],
                ],
                'limitations' => [
                    'display' => __('Limitations'),
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'customers',
                                    'field' => [
                                        'display' => __('Customers'),
                                        'instructions' => __('cargo::messages.discounts.customers'),
                                        'type' => 'users',
                                        'listable' => 'hidden',
                                        'mode' => 'stack',
                                    ],
                                ],
                                [
                                    'handle' => 'products',
                                    'field' => [
                                        'display' => __('Products'),
                                        'instructions' => __('cargo::messages.discounts.products'),
                                        'collections' => config('statamic.cargo.products.collections'),
                                        'type' => 'entries',
                                        'listable' => 'hidden',
                                        'create' => false,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
