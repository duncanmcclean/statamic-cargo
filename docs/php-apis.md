---
title: PHP APIs
---

This guide will hopefully give you a rough idea of how things work, and details on what methods are available, in order to help you when writing custom code to interact with Cargo.

If you can't find what you're looking for here, you may want to consider source-diving the code (it's really not as bad as you think), or ask for help on [GitHub](https://github.com/duncanmcclean/statamic-cargo/discussions).

## Carts
The `Cart` facade allows you to query, create and update carts. 

### Querying
#### Get a single cart by its ID
You can use the `find` method to get a single cart:

```php
use DuncanMcClean\Cargo\Facades\Cart;

Cart::find(123);
``` 

The `find` method will return `null` when the cart can't be found. If you'd prefer an exception to be thrown, you can use the `findOrFail` method.

#### Get the current cart
If you're in the context of a web request, you can call the `current()` method to obtain the customer's current cart instance.

```php
use DuncanMcClean\Cargo\Facades\Cart;

Cart::current();
``` 

#### Querying all carts
You can use the facade's `query()` method to query your store's carts.

```php
use DuncanMcClean\Cargo\Facades\Cart;

Cart::query()
  ->where('site', 'english')
  ->where('customer', $userId)
  ->get();
``` 

You can learn more about query builders, and the available methods, on the [Statamic documentation](https://statamic.dev/content-queries).

### Creating
Start by making an instance of a cart with the `make` method. 

```php
use DuncanMcClean\Cargo\Facades\Cart;

Cart::make();
``` 

You may call additional methods on the cart to customise it further:

```php
$cart
  ->site('english')
  ->customer($userId)
  ->customer(['name' => 'John Doe', 'email' => 'john@example.com']) // For guest customers
  ->coupon($coupon)
  ->lineItems([
	  [
		  'product' => 'abc',
		  'quantity' => 1,
	  ],
	  [
		  'product' => 'efg',
		  'quantity' => 2,
	  ],
  ]);
```

Finally, save it. It'll return a boolean for whether it succeeded or not.

```php
$cart->save();
```

:::tip Note
Cargo will automatically recalculate the cart's totals when you `->save()`. If you wish to save the cart *without* recalculating, you should call `->saveWithoutRecalculating()` instead.
:::

#### Methods
There's a bunch of handy methods on the `Cart` object. For completeness, here's a big old table:

| Method                          | Description                                                                                                                                                                                                                                                                                      |
| ------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `id($id)`                       | Allows you to get/set the cart's ID. The `$id` parameter is optional.                                                                                                                                                                                                                            |
| `customer($customer)`           | Allows you to get/set the customer. <br><br>Returns a Statamic `User` object, or a `GuestCustomer` object.<br><br>Accepts an optional `$customer` parameter, which should either be a Statamic `User` object, a user ID, or an array of guest customer data.                                     |
| `coupon($coupon)`               | Allows you to get/set the cart's coupon. The `$coupon` parameter is optional.                                                                                                                                                                                                                    |
| `shippingMethod()`              | Allows you to get the selected shipping method. Returns a `ShippingMethod` object.                                                                                                                                                                                                               |
| `shippingOption()`              | Allows you to get the selected shipping option. Returns a `ShippingOption` object.                                                                                                                                                                                                               |
| `paymentGateway()`              | Allows you to get the selected payment gateway. Returns a `PaymentGateway` object.                                                                                                                                                                                                               |
| `lineItems($lineItems)`         | Allows you to get/set line items. <br><br>Returns a `LineItems` collection of `LineItem` objects (you can use Laravel collection methods on the `LineItems` class).<br><br>Accepts an optional `$lineItems` parameter, which should be an array of line items to replace the current line items. |
| `site($site)`                   | Allows you to get/set the cart's site. The `$site` parameter is optional.                                                                                                                                                                                                                        |
| `saveWithoutRecalculating()`    | Saves the cart without recalculating totals.                                                                                                                                                                                                                                                     |
| `saveQuietly()`                 | Saves the cart without dispatching events.                                                                                                                                                                                                                                                       |
| `save()`                        | Saves the cart.                                                                                                                                                                                                                                                                                  |
| `deleteQuietly()`               | Deletes the cart without dispatching events.                                                                                                                                                                                                                                                     |
| `delete()`                      | Deletes the cart.                                                                                                                                                                                                                                                                                |
| `fresh()`                       | Returns a fresh instance of the current cart.                                                                                                                                                                                                                                                    |
| `recalculate()`                 | Recalculates the cart's totals.                                                                                                                                                                                                                                                                  |
| `taxableAddress()`              | Returns the cart's "taxable address". <br><br>Normally returns the cart's shipping address, but falls back to the cart's billing address when not available.                                                                                                                                     |
| `shippingAddress()`             | Returns the shipping address in as an `Address` object.                                                                                                                                                                                                                                          |
| `billingAddress()`              | Returns the billing address in as an `Address` object.                                                                                                                                                                                                                                           |
| `hasShippingAddress()`          | Returns `true` when a shipping address is present.                                                                                                                                                                                                                                               |
| `hasBillingAddress()`           | Returns `true` when a billing address is present.                                                                                                                                                                                                                                                |
| `grandTotal($grandTotal)`       | Allows you to get/set the grand total, in pence. The `$grandTotal` parameter is optional.                                                                                                                                                                                                        |
| `isFree()`                      | Returns `true` when the grand total is £0.00.                                                                                                                                                                                                                                                    |
| `subTotal($subTotal)`           | Allows you to get/set the sub total, in pence. The `$subTotal` parameter is optional.                                                                                                                                                                                                            |
| `discountTotal($discountTotal)` | Allows you to get/set the discount total, in pence. The `$discountTotal` parameter is optional.                                                                                                                                                                                                  |
| `taxTotal($taxTotal)`           | Allows you to get/set the tax total, in pence. The `$taxTotal` parameter is optional.                                                                                                                                                                                                            |
| `taxBreakdown()`                | Returns a `Collection` breaking down the taxes applied to line items & shipping options.                                                                                                                                                                                                         |
| `shippingTotal($shippingTotal)` | Allows you to get/set the shipping total, in pence. The `$shippingTotal` parameter is optional.                                                                                                                                                                                                  |

## Orders
The `Order` facade allows you to query, create and update orders. 

### Querying
#### Get a single order by its ID
You can use the `find` method to get a single order:

```php
use DuncanMcClean\Cargo\Facades\Order;

Order::find(123);
``` 

The `find` method will return `null` when the order can't be found. If you'd prefer an exception to be thrown, you can use the `findOrFail` method.

#### Querying all orders
You can use the facade's `query()` method to query your store's orders.

```php
use DuncanMcClean\Cargo\Facades\Order;

Order::query()
  ->where('site', 'english')
  ->where('customer', $userId)
  ->get();
``` 

You can learn more about query builders, and the available methods, on the [Statamic documentation](https://statamic.dev/content-queries).

### Creating
Start by making an instance of an order with the `make` method. 

```php
use DuncanMcClean\Cargo\Facades\Order;

Order::make();
``` 

You may call additional methods on the cart to customise it further:

```php
$order
  ->orderNumber(12345)
  ->status('shipped')
  ->date('2025-04-07')
  ->site('english')
  ->customer($userId)
  ->customer(['name' => 'John Doe', 'email' => 'john@example.com']) // For guest customers
  ->coupon($coupon)
  ->lineItems([
	  [
		  'product' => 'abc',
		  'quantity' => 1,
	  ],
	  [
		  'product' => 'efg',
		  'quantity' => 2,
	  ],
  ]);
```

Finally, save it. It'll return a boolean for whether it succeeded or not.

```php
$order->save();
```

#### Methods
There's a bunch of handy methods on the `Order` object. For completeness, here's a big old table:

| Method                          | Description                                                                                                                                                                                                                                                                                      |
| ------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `id($id)`                       | Allows you to get/set the orders's ID. The `$id` parameter is optional.                                                                                                                                                                                                                          |
| `orderNumber($orderNumber)`     | Allows you to get/set the order number. The `$orderNumber` parameter is optional.                                                                                                                                                                                                                |
| `date($date)`                   | Allows you to get/set the order date. The `$date` parameter is optional.                                                                                                                                                                                                                         |
| `cart($cart)`                   | Allows you to get/set the associated cart. The `$cart` parameter is optional.<br><br>It's possible that an order may reference a cart that doesn't exist (by default, carts are deleted after 30 days).                                                                                          |
| `status($status)`               | Allows you to get/set the order status. <br><br>Returns an `OrderStatus` enum value.<br><br>Accepts a string, or an `OrderStatus` enum value.                                                                                                                                                    |
| `customer($customer)`           | Allows you to get/set the customer. <br><br>Returns a Statamic `User` object, or a `GuestCustomer` object.<br><br>Accepts an optional `$customer` parameter, which should either be a Statamic `User` object, a user ID, or an array of guest customer data.                                     |
| `coupon($coupon)`               | Allows you to get/set the order's coupon. The `$coupon` parameter is optional.                                                                                                                                                                                                                   |
| `shippingMethod()`              | Allows you to get the selected shipping method. Returns a `ShippingMethod` object.                                                                                                                                                                                                               |
| `shippingOption()`              | Allows you to get the selected shipping option. Returns a `ShippingOption` object.                                                                                                                                                                                                               |
| `paymentGateway()`              | Allows you to get the selected payment gateway. Returns a `PaymentGateway` object.                                                                                                                                                                                                               |
| `lineItems($lineItems)`         | Allows you to get/set line items. <br><br>Returns a `LineItems` collection of `LineItem` objects (you can use Laravel collection methods on the `LineItems` class).<br><br>Accepts an optional `$lineItems` parameter, which should be an array of line items to replace the current line items. |
| `site($site)`                   | Allows you to get/set the order's site. The `$site` parameter is optional.                                                                                                                                                                                                                       |
| `saveQuietly()`                 | Saves the order without dispatching events.                                                                                                                                                                                                                                                      |
| `save()`                        | Saves the order.                                                                                                                                                                                                                                                                                 |
| `deleteQuietly()`               | Deletes the order without dispatching events.                                                                                                                                                                                                                                                    |
| `delete()`                      | Deletes the order.                                                                                                                                                                                                                                                                               |
| `fresh()`                       | Returns a fresh instance of the current order.                                                                                                                                                                                                                                                   |
| `taxableAddress()`              | Returns the order's "taxable address". <br><br>Normally returns the order's shipping address, but falls back to the order's billing address when not available.                                                                                                                                  |
| `shippingAddress()`             | Returns the shipping address in as an `Address` object.                                                                                                                                                                                                                                          |
| `billingAddress()`              | Returns the billing address in as an `Address` object.                                                                                                                                                                                                                                           |
| `hasShippingAddress()`          | Returns `true` when a shipping address is present.                                                                                                                                                                                                                                               |
| `hasBillingAddress()`           | Returns `true` when a billing address is present.                                                                                                                                                                                                                                                |
| `grandTotal($grandTotal)`       | Allows you to get/set the grand total, in pence. The `$grandTotal` parameter is optional.                                                                                                                                                                                                        |
| `isFree()`                      | Returns `true` when the grand total is £0.00.                                                                                                                                                                                                                                                    |
| `subTotal($subTotal)`           | Allows you to get/set the sub total, in pence. The `$subTotal` parameter is optional.                                                                                                                                                                                                            |
| `discountTotal($discountTotal)` | Allows you to get/set the discount total, in pence. The `$discountTotal` parameter is optional.                                                                                                                                                                                                  |
| `taxTotal($taxTotal)`           | Allows you to get/set the tax total, in pence. The `$taxTotal` parameter is optional.                                                                                                                                                                                                            |
| `taxBreakdown()`                | Returns a `Collection` breaking down the taxes applied to line items & shipping options.                                                                                                                                                                                                         |
| `shippingTotal($shippingTotal)` | Allows you to get/set the shipping total, in pence. The `$shippingTotal` parameter is optional.                                                                                                                                                                                                  |

## Products
Since products are just entries, you can use the [Entry Repository](https://statamic.dev/repositories/entry-repository) and the associated `Entry` class to work with products.

### Product Variants
If you need to add product variants programatically (maybe for a custom importer), you should first create an example product, add a couple of options/variants to see how it gets saved.

Then, in your custom code, you should try and construct the array using the same format.