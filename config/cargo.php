<?php

return [

    'products' => [

        /*
        |--------------------------------------------------------------------------
        | Product Collections
        |--------------------------------------------------------------------------
        |
        | Determines the collections containing product entries. You can define as many
        | collections as you like.
        |
        */

        'collections' => ['products'],

        /*
        |--------------------------------------------------------------------------
        | Low Stock Threshold
        |--------------------------------------------------------------------------
        |
        | The threshold at which a product is considered "low stock", used to determine
        | when the ProductStockLow event should be dispatched.
        |
        */

        'low_stock_threshold' => 10,

        /*
        |--------------------------------------------------------------------------
        | Digital Products
        |--------------------------------------------------------------------------
        |
        | By default, Cargo assumes that all products are physical products. If you're
        | selling digital goods, like downloadable files, enable this option.
        |
        */

        'digital_products' => false,

    ],

    'discounts' => [

        /*
        |--------------------------------------------------------------------------
        | Storage
        |--------------------------------------------------------------------------
        |
        | Discounts are stored in flat files. If you need to change the location of
        | them, you can do that here.
        |
        */

        'directory' => base_path('content/cargo/discounts'),

    ],

    'carts' => [

        /*
        |--------------------------------------------------------------------------
        | Storage
        |--------------------------------------------------------------------------
        |
        | By default, carts are stored in flat files. However, it's also possible to
        | store them in a database by changing the "driver" to "eloquent".
        |
        | Learn more at https://builtwithcargo.dev/docs/carts
        |
        */

        'driver' => 'file',

        'directory' => base_path('content/cargo/carts'),

        // 'model' => \DuncanMcClean\Cargo\Cart\Eloquent\CartModel::class,
        // 'table' => 'cargo_carts',
        // 'line_items_model' => \DuncanMcClean\Cargo\Cart\Eloquent\LineItemModel::class,
        // 'line_items_table' => 'cargo_cart_line_items',

        /*
        |--------------------------------------------------------------------------
        | Abandoned Carts
        |--------------------------------------------------------------------------
        |
        | To keep things nice and tidy, Cargo will automatically delete any carts that
        | haven't been updated in a while. This is to prevent your storage from filling up.
        | You can change the number of days here.
        |
        */

        'purge_abandoned_carts_after' => 30,

        /*
        |--------------------------------------------------------------------------
        | Merging carts on login
        |--------------------------------------------------------------------------
        |
        | When a customer logs in, and they have an existing cart from another device,
        | should the two carts be merged?
        |
        */

        'merge_on_login' => true,

        /*
        |--------------------------------------------------------------------------
        | Cookie Name
        |--------------------------------------------------------------------------
        |
        | Cargo places a small cookie in the user's browser to identify their cart.
        | You can change the name of this cookie here.
        |
        */

        'cookie_name' => 'cargo-cart',

        /*
        |--------------------------------------------------------------------------
        | Unique Metadata
        |--------------------------------------------------------------------------
        |
        | By default, when you add a product to the cart multiple times, Cargo will
        | increase the quantity of the existing line item. However, if the metadata of
        | the new line item is different, this option will create a new line item
        | instead of increasing the quantity. Useful for personalised products.
        |
        */

        'unique_metadata' => false,

    ],

    'orders' => [

        /*
        |--------------------------------------------------------------------------
        | Storage
        |--------------------------------------------------------------------------
        |
        | By default, orders are stored in flat files. However, it's also possible to
        | store them in a database by changing the "driver" to "eloquent".
        |
        | Learn more at https://builtwithcargo.dev/docs/orders
        |
        */

        'driver' => 'file',

        'directory' => base_path('content/cargo/orders'),

        // 'model' => \DuncanMcClean\Cargo\Orders\Eloquent\OrderModel::class,
        // 'table' => 'cargo_orders',
        // 'line_items_model' => \DuncanMcClean\Cargo\Orders\Eloquent\LineItemModel::class,
        // 'line_items_table' => 'cargo_order_line_items',

    ],

    'taxes' => [

        /*
        |--------------------------------------------------------------------------
        | Prices Include Tax
        |--------------------------------------------------------------------------
        |
        | Enable this when product prices are entered inclusive of tax. When calculating
        | taxes, the tax will be added to the product price. The tax will be
        | calculated on the product price, then added back on at the end.
        |
        */

        'price_includes_tax' => true,

    ],

    'shipping' => [

        /*
        |--------------------------------------------------------------------------
        | Shipping Methods
        |--------------------------------------------------------------------------
        |
        | Determines which shipping methods should be available during checkout.
        | You can define as many shipping methods as you need.
        |
        */

        'methods' => [
            'free_shipping' => [],
        ],

    ],

    'payments' => [

        /*
        |--------------------------------------------------------------------------
        | Payment Gateways
        |--------------------------------------------------------------------------
        |
        | Determines which payment gateways should be available during checkout.
        | Each payment gateway may have its own configuration options.
        |
        */

        'gateways' => [
            'dummy' => [],

            // 'stripe' => [
            //     'key' => env('STRIPE_KEY'),
            //     'secret' => env('STRIPE_SECRET'),
            //     'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            // ],

            // 'mollie' => [
            //     'api_key' => env('MOLLIE_KEY'),
            //     'profile_id' => env('MOLLIE_PROFILE_ID'),
            // ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | Cargo uses these routes during the checkout process to handle redirects,
    | like redirecting the customer to the checkout confirmation page.
    |
    */

    'routes' => [
        'checkout' => 'checkout',
        'checkout_confirmation' => 'checkout.confirmation',
    ],

];
