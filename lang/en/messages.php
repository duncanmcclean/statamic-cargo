<?php

return [
    'discount_configure_intro' => 'Discounts are a great way to offer discounts to your customers. You can create either a percentage or fixed amount discount.',
    'discounts' => [
        'customers' => 'Select which customers this discount should be limited to.',
        'discount_code' => 'Enter a discount code, or leave blank to apply discount automatically.',
        'maximum_uses' => 'Specify the maximum number of uses. Leave empty to allow unlimited uses.',
        'minimum_order_value' => 'Specify the minimum order value. Leave empty to allow any order value.',
        'name' => 'Give your discount a name. May be shown to customers.',
        'products' => 'Select which products this discount should be limited to.',
        'type' => 'Select the type of discount you want to create.',
    ],
    'products' => [
        'downloads' => 'Select the files available for download with this product.',
        'download_limit' => 'Specify the maximum number of times a customer can download this product. Leave empty to allow unlimited downloads.',
        'price.exclusive_of_tax' => 'Enter the price of the product, exclusive of tax.',
        'price.inclusive_of_tax' => 'Enter the price of the product, inclusive of tax.',
        'tax_class' => 'Determines how this product is taxed.',
        'type' => 'Used to determine how the product is delivered.',
    ],
    'tax_class_intro' => 'Tax Classes allow you to group products by tax classification. Useful if you have products which should be taxed at different rates.',
    'tax_classes_name_instructions' => 'This may be visible to customers in the order\'s tax breakdown.',
    'tax_zones_intro' => 'Tax Rates allow you to define the tax rates for each tax class, with different rates per country, state or postal code.',
    'tax_zones_rates_instructions' => 'Define the tax rates available for this zone, per tax class.',
    'tax_zones_type_instructions' => 'Where should this tax zone apply?',
];