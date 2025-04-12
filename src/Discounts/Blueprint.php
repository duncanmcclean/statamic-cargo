<?php

namespace DuncanMcClean\Cargo\Discounts;

use Statamic\Fields\Blueprint as FieldsBlueprint;

class Blueprint
{
    public function __invoke(): FieldsBlueprint
    {
        return \Statamic\Facades\Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'name',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('Name'),
                                        'instructions' => __('cargo::messages.discounts.name'),
                                        'listable' => true,
                                        'validate' => ['required', 'string', 'max:255'],
                                    ],
                                ],
                                // todo: method button group (applied automatically / discount code)
                                [
                                    'handle' => 'code',
                                    'field' => [
                                        'type' => 'coupon_code',
                                        'display' => __('Discount Code'),
                                        'instructions' => __('cargo::messages.discounts.code'),
                                        'listable' => true,
                                        'validate' => ['uppercase', function ($attribute, $value, $fail) {
                                            if (! preg_match('/^[a-zA-Z][a-zA-Z0-9]*(?:_{0,1}[a-zA-Z0-9])*$/', $value)) {
                                                $fail('statamic::validation.handle')->translate();
                                            }
                                        }],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Value'),
                            'fields' => [
                                [
                                    'handle' => 'type',
                                    'field' => [
                                        'type' => 'select',
                                        'options' => collect(DiscountType::cases())
                                            ->mapWithKeys(fn ($enum) => [$enum->value => DiscountType::label($enum)])
                                            ->all(),
                                        'clearable' => false,
                                        'multiple' => false,
                                        'searchable' => false,
                                        'taggable' => false,
                                        'push_tags' => false,
                                        'cast_booleans' => false,
                                        'display' => 'Type',
                                        'width' => 50,
                                        'validate' => ['required'],
                                        'listable' => false,
                                        'max_items' => 1,
                                    ],
                                ],
                                [
                                    'handle' => 'amount',
                                    'field' => [
                                        'type' => 'coupon_amount',
                                        'display' => __('Amount'),
                                        'width' => 50,
                                        'validate' => ['required'],
                                        'listable' => false,
                                        'if' => [
                                            // We only want the Amount field to show when a Type has been selected.
                                            'type' => 'contains e',
                                        ],
                                    ],
                                ],
                                [
                                    'handle' => 'discount_text',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('Discount'),
                                        'listable' => true,
                                        'visibility' => 'hidden',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Minimum purchase requirements'),
                            'fields' => [
                                [
                                    'handle' => 'minimum_cart_value',
                                    'field' => [
                                        'type' => 'money',
                                        'display' => __('Minimum Order Value'),
                                        'instructions' => __('cargo::messages.discounts.minimum_cart_value'),
                                        'width' => 50,
                                        'listable' => 'hidden',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Customer eligibility'),
                            'fields' => [
                                [
                                    'handle' => 'customer_eligibility',
                                    'field' => [
                                        'type' => 'radio',
                                        'display' => __('Which customers are eligible for this coupon?'),
                                        'options' => [
                                            'all' => __('All'),
                                            'specific_customers' => __('Specific customers'),
                                            'customers_by_domain' => __('Specific customers (by domain)'),
                                        ],
                                        'inline' => false,
                                        'validate' => ['required'],
                                        'default' => 'all',
                                        'listable' => false,
                                    ],
                                ],
                                [
                                    'handle' => 'customers',
                                    'field' => [
                                        'mode' => 'default',
                                        'display' => __('Specific Customers'),
                                        'type' => 'users',
                                        'icon' => 'users',
                                        'if' => [
                                            'customer_eligibility' => 'specific_customers',
                                        ],
                                    ],
                                ],
                                [
                                    'handle' => 'customers_by_domain',
                                    'field' => [
                                        'type' => 'list',
                                        'display' => __('Domains'),
                                        'instructions' => __('cargo::messages.discounts.customers_by_domain'),
                                        'add_button' => __('Add Domain'),
                                        'listable' => false,
                                        'if' => [
                                            'customer_eligibility' => 'customers_by_domain',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Usage limits'),
                            'fields' => [
                                [
                                    'handle' => 'maximum_uses',
                                    'field' => [
                                        'type' => 'integer',
                                        'display' => __('Maximum times coupon can be redeemed'),
                                        'instructions' => __('cargo::messages.discounts.maximum_uses'),
                                        'width' => 50,
                                        'listable' => 'hidden',
                                    ],
                                ],
                                [
                                    'handle' => 'products',
                                    'field' => [
                                        'mode' => 'default',
                                        'collections' => config('statamic.cargo.products.collections'),
                                        'display' => __('Limit to certain products'),
                                        'instructions' => __('cargo::messages.discounts.products'),
                                        'type' => 'entries',
                                        'icon' => 'entries',
                                        'width' => 50,
                                        'listable' => 'hidden',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Active dates'),
                            'instructions' => __('cargo::messages.discounts.active_dates'),
                            'fields' => [
                                [
                                    'handle' => 'valid_from',
                                    'field' => [
                                        'type' => 'date',
                                        'display' => __('Start Date'),
                                        'width' => 50,
                                        'listable' => 'hidden',
                                    ],
                                ],
                                [
                                    'handle' => 'expires_at',
                                    'field' => [
                                        'type' => 'date',
                                        'display' => __('End Date'),
                                        'width' => 50,
                                        'listable' => 'hidden',
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
