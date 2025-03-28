<?php

return [

    'products' => [
        'collections' => ['products'],

        'low_stock_threshold' => 5,

        'digital_products' => true,
    ],

    'coupons' => [
        'directory' => base_path('content/cargo/coupons'),
    ],

    'routes' => [
        'checkout' => 'checkout',
        'checkout_confirmation' => 'checkout.confirmation',
    ],

    'carts' => [
        'repository' => 'file',

        // Flat file repository
        'directory' => base_path('content/cargo/carts'),

        // Database repository
        'model' => \DuncanMcClean\Cargo\Cart\Eloquent\CartModel::class,
        'table' => 'carts',

        'line_items_model' => \DuncanMcClean\Cargo\Cart\Eloquent\LineItemModel::class,
        'line_items_table' => 'cart_line_items',

        'cookie_name' => 'cargo-cart',

        'unique_metadata' => false,

        'purge_abandoned_carts_after' => 30,

        // When a user logs in, and they've already started a cart elsewhere, should the two carts be merged?
        'merge_on_login' => true,
    ],

    'orders' => [
        'repository' => 'file',

        // Flat file repository
        'directory' => base_path('content/cargo/orders'),

        // Database repository
        'model' => \DuncanMcClean\Cargo\Orders\Eloquent\OrderModel::class,
        'table' => 'orders',

        'line_items_model' => \DuncanMcClean\Cargo\Orders\Eloquent\LineItemModel::class,
        'line_items_table' => 'order_line_items',
    ],

    'taxes' => [
        // Enable this when product prices are entered inclusive of tax.
        // When calculating taxes, the tax will be deducted from the product price, then added back on at the end.
        'price_includes_tax' => true,

        // Determines how tax is calculated on shipping costs. Options:
        // - highest_tax_rate: Charge the highest tax rate from the products in the cart.
        // - tax_class: When enabled, a new tax class will be created for shipping, allowing you to set a specific tax rate for shipping.
        'shipping_tax_behaviour' => 'tax_class',
    ],

    'shipping' => [
        'methods' => [
            'free_shipping' => [],
        ],
    ],

    'payments' => [
        'gateways' => [
            'dummy' => [
                //
            ],

            //                        'stripe' => [
            //                            'key' => env('STRIPE_KEY'),
            //                            'secret' => env('STRIPE_SECRET'),
            //                            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            //                        ],
            //
            //            'mollie' => [
            //                'api_key' => env('MOLLIE_KEY'),
            //                'profile_id' => env('MOLLIE_PROFILE_ID'),
            //            ],
        ],
    ],

];
