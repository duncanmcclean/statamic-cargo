---
title: Carts
description: "Cargo revolves around two main concepts: carts and orders. This page explains the differences between them, how to add products to a cart, and how to store carts."
---
Cargo revolves around two main concepts: carts and orders.

**Carts** are created when customers add products to their, well, cart. They are essentially a collection of products the customer wishes to purchase. 

**Orders** are then created when the customer completes the checkout process. They are visible in the Control Panel.

They share many of the same attributes, like both containing line items, both being associated with customers, but they represent different parts of the customer's journey.

## Adding to the cart
Before customers can actually buy anything, they need to be able to add products to their cart. Thankfully, showing an "add to cart" form on your product page is trivial: 

::tabs
::tab antlers
```antlers
{{ cart:add }}
	<button>Add to cart</button>
{{ /cart:add }}
```
::tab blade
```blade
<s:cart:add>  
    <button>Add to cart</button>
</s:cart:add>
```
::

Cargo will automatically inject the product `id` from [the context](https://statamic.dev/extending/tags#context). However, If you need to, you can provide it manually using a hidden input:

```antlers
<input type="hidden" name="product" value="{{ page:id }}">
``` 

This example only scratches the surface of what you can do using the cart tag. To find out more, see the [Cart Tag](/docs/tags/cart) page.

## Storage
Out of the box, carts are stored as YAML files in the `content/cargo/carts` directory. If you wish, you can change the directory in the `cargo.php` config file:

```php
// config/statamic/cargo.php

'carts' => [
    'repository' => 'file',  
  
    'directory' => base_path('carts'),
],
```

### Database
You can also opt to store carts in a traditional database, which might be useful for high-traffic stores.

To move carts to the database, run this command:

```
php please statamic:cargo:database-carts
```

It'll automatically publish database migrations, update your `cargo.php` config file and import existing carts into the database.

If needed, you can customise the eloquent models Cargo uses by updating the `model` and/or `line_items_model` keys in the `cargo.php` config file. 

:::tip warning
Make sure you have a backup strategy in place before moving carts to the database, in case the worst happens. Both [Laravel Forge](https://forge.laravel.com/docs/servers/backups) and [Ploi](https://ploi.io/features/database-backups) have built-in solutions for database backups.
:::

## Abandoned carts
As with any e-commerce store, customers are going to add products to their cart without continuing through to checkout.

To keep your backend nice and organised, Cargo provides a `purge-abandoned-carts` command which deletes any carts which have been inactive for a certain period of time. By default, this is 30 days, however it's customisable in the `cargo.php` config file.

This command will have been automatically added to your `routes/console.php` file during the install process:

```php
Schedule::command('statamic:cargo:purge-abandoned-carts')->daily();
```

You should make sure to [configure Laravel's task scheduler](https://statamic.dev/scheduling#running-the-scheduler) when you deploy your site to production.

## Calculations
Tip: If you want to override how taxes are calculated, you should investigate building your own driver.

If you need to, you can hook into how Cargo calculates line items, discounts, taxes and shipping.

### Dynamic Pricing
By default, Cargo uses the `price` field on a product/variant to determine the unit price of a line item.

However, if you need to change prices based on some logic, you can do that using the `CalculateLineItems::priceHook()` method.

Your hook should return the desired unit price in pence.

```php
// app/Providers/AppServiceProvider.php

CalculateLineItems::priceHook(function ($cart, $lineItem) {  
    if ($cart->customer()->vip) {  
        return $lineItem->product()->purchasePrice() / 2;  
    }  
  
    return $lineItem->product()->purchasePrice();  
});
```

### Taxes
To override how taxes are calculated, you should investigate [building your own](/docs/taxes) tax driver.

### Custom Calculations
If you need to implement your own custom calculations, you can build your own calculator class and place it inside Cargo's calculator [pipeline](https://laravel.com/docs/master/processes#process-pipelines).

```php
// app/Cargo/CustomCalculator.php

<?php  
  
namespace App\Cargo;  
  
use Closure;  
use DuncanMcClean\Cargo\Cart\Cart;  
  
class CustomCalculator  
{  
    public function handle(Cart $cart, Closure $next)  
    {  
		// Your calculations here...

		$cart->set('custom_calculation_amount', 1234);

        return $next($cart);  
    }  
}
```

```php
// app/Cargo/Calculator.php

<?php  
  
namespace App\Cargo;  
  
use DuncanMcClean\Cargo\Contracts\Cart\Cart;  
use Illuminate\Support\Facades\Pipeline;
  
class Calculator  
{  
    public static function calculate(Cart $cart): Cart  
    {  
        return Pipeline::send($cart)  
            ->through([  
                ResetTotals::class,  
                CalculateLineItems::class,  
                ApplyCouponDiscounts::class,  
                ApplyShipping::class,  
                CalculateTaxes::class,  

				// Your custom calculator...
				CustomCalculator::class,

                CalculateTotals::class,  
            ])  
            ->thenReturn();  
    }
}
```

```php
// app/Providers/AppServiceProvider.php

$this->app->bind(  
    \DuncanMcClean\Cargo\Cart\Calculator\Calculator::class,  
    \App\Cargo\Calculator::class  
);
```
