<?php

return [
    'discount_configure_intro' => 'Discounts are a great way to offer discounts to your customers. You can create either a percentage or fixed amount discount.',
    'discount_discount_text' => ':amount off',
    'discounts' => [
        'active_dates' => 'Configure when this discount is active. Leave both dates blank to make the discount active indefinitely.',
        'name' => 'Give your discount a name. This is for your reference only.',
        'code' => 'When filled, this discount will only be redeemable with this code. Leave blank to make it automatically redeemable.',
        'customers_by_domain' => 'Provide a list of domains that are eligible for this discount. One per line.',
        'description' => 'Give yourself a reminder of what this discount is for.',
        'maximum_uses' => 'By default, discounts can be redeemed an unlimited amount of times. You can set a maximum here if you wish.',
        'minimum_cart_value' => "The minimum value the customer's cart should have before this discount can be redeemed.",
        'products' => 'This discount will only be redeemable when *any* of these products are present in the order.',
    ],
    'products' => [
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
