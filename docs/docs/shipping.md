---
title: Shipping
description: "Cargo allows you to define shipping methods and options, which can be selected by customers during checkout. This page explains how to create and configure shipping methods."
---

When you're selling physical products, you need a way to ship those products out to your customers.

Cargo allows you to define shipping methods, which can each provide multiple shipping options. These options can then be selected by the customer during checkout.

Most of the time, you will want to create your own shipping method. To do this, run the following command:

```
php please make:shipping-method RoyalMail
```

This will create a file in `app/ShippingMethods` which looks like this:

```php
<?php  
  
namespace App\ShippingMethods;  
  
use DuncanMcClean\Cargo\Contracts\Cart\Cart;  
use DuncanMcClean\Cargo\Shipping\ShippingMethod;  
use DuncanMcClean\Cargo\Shipping\ShippingOption;  
use Illuminate\Support\Collection;  
  
class RoyalMail extends ShippingMethod  
{  
    public function options(Cart $cart): Collection  
    {  
        return collect([  
            ShippingOption::make($this)  
                ->name(__('Free Shipping'))  
                ->price(0),  
        ]);  
    }  
}
```

The `options` method should return a collection of `ShippingOption` objects. The name and the price are displayed to the customer during checkout.

If you want to accept [payment on delivery](/docs/payment-gateways#pay-on-delivery), you'll also need to chain `->acceptsPaymentOnDelivery(true)` when creating shipping options.

You can optionally provide a `fieldtypeDetails` method to your shipping method, allowing you to display information about the shipment in the Control Panel, under the "Shipping" tab:

```php
public function fieldtypeDetails(Order $order): array  
{  
	return [
		__('Delivery Due') => 'Tomorrow',
	];
}
```

Cargo will automatically register any shipping methods in the `app/ShippingMethods` directory. 

If your shipping methods live elsewhere or you're inside of an addon, you will need to register it manually in a service provider:

```php
// app/Providers/AppServiceProvider.php

use App\ShippingMethods\RoyalMail;

public function boot(): void
{
	RoyalMail::register();
}
```

## Configuration
Before shipping methods will show up during the checkout process, you need to add them to the `cargo.php` config file, using their handles:

```php
// config/statamic/cargo.php

'shipping' => [
	'methods' => [
		'free_shipping' => [],

		'royal_mail' => []
	],
],
```

## Third-party shipping methods
Right now, there aren't any shipping methods built by the community. But, when there are, we'll list them here. Check back soon!

If you've created a custom shipping method others can use, please [let us know](mailto:support@builtwithcargo.dev) and we'll update the list.

## Taxes
Please see the [Taxes](/docs/taxes#shipping) page for information on how shipping costs are taxed.