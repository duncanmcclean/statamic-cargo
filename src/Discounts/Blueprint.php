<?php

namespace DuncanMcClean\Cargo\Discounts;

use Statamic\Fields\Blueprint as FieldsBlueprint;
use Statamic\Rules\Handle;

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
                                    'handle' => 'code',
                                    'field' => [
                                        'type' => 'coupon_code',
                                        'display' => __('Discount Code'),
                                        'instructions' => __('cargo::messages.discounts.code'),
                                        'listable' => true,
                                        'validate' => ['required', 'uppercase', new Handle],
                                    ],
                                ],
                                [
                                    'handle' => 'description',
                                    'field' => [
                                        'type' => 'textarea',
                                        'display' => __('Description'),
                                        'instructions' => __('cargo::messages.discounts.description'),
                                        'listable' => true,
                                    ],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Options'),
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
                            'display' => __('Minimum Requirements'),
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
                            'display' => __('Customer Eligibility'),
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
                            'display' => __('Usage Limits'),
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
                            'display' => __('Active Dates'),
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
