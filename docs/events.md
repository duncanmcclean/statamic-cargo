---
title: Events
---

## Overview
Like Statamic, Cargo dispatches numerous events which you can listen for in your app code in order to trigger API calls or run custom logic.

To listen for events, simply create an event listener, type the event name, then handle the event.

```php
use DuncanMcClean\Cargo\Events\CartCreated;

class SomeListener
{
	public function handle(CartCreated $event)
	{
		//
	}
}
```

For a more in-depth explanation on events, please consult the [Laravel documentation](https://laravel.com/docs/events).

## Available Events
### CartCreated
`DuncanMcClean\Cargo\Events\CartCreated`

Dispatched after a cart has been created.

```php
public function handle(CartCreated $event)
{
	$event->cart;
}
```

### CartDeleted
`DuncanMcClean\Cargo\Events\CartDeleted`

Dispatched after a cart has been deleted.

```php
public function handle(CartDeleted $event)
{
	$event->cart;
}
```

### CartRecalculated
`DuncanMcClean\Cargo\Events\CartRecalculated`

Dispatched after the totals on a cart have been recalculated.

```php
public function handle(CartRecalculated $event)
{
	$event->cart;
}
```

### CartSaved
`DuncanMcClean\Cargo\Events\CartSaved`

Dispatched after a cart has been saved.

```php
public function handle(CartSaved $event)
{
	$event->cart;
}
```

### CouponCreated
`DuncanMcClean\Cargo\Events\CouponCreated`

Dispatched after a coupon has been created.

```php
public function handle(CouponCreated $event)
{
	$event->coupon;
}
```

### CouponDeleted
`DuncanMcClean\Cargo\Events\CouponDeleted`

Dispatched after a coupon has been deleted.

```php
public function handle(CouponDeleted $event)
{
	$event->coupon;
}
```

### CouponRedeemed
`DuncanMcClean\Cargo\Events\CouponRedeemed`

Dispatched when a coupon is redeemed during the checkout process.

```php
public function handle(CouponRedeemed $event)
{
	$event->coupon;
}
```

### CouponSaved
`DuncanMcClean\Cargo\Events\CouponSaved`

Dispatched after a coupon has been saved.

```php
public function handle(CouponSaved $event)
{
	$event->coupon;
}
```

### OrderCancelled
`DuncanMcClean\Cargo\Events\OrderCancelled`

Dispatched when an order's status is changed to ["Cancelled"](/the-basics/carts-and-orders#statuses).

```php
public function handle(OrderCancelled $event)
{
	$event->order;
}
```

### OrderCreated
`DuncanMcClean\Cargo\Events\OrderCreated`

Dispatched after an order has been created, usually during the checkout process.

```php
public function handle(OrderCreated $event)
{
	$event->order;
}
```

### OrderDeleted
`DuncanMcClean\Cargo\Events\OrderDeleted`

Dispatched after an order has been deleted.

```php
public function handle(OrderDeleted $event)
{
	$event->order;
}
```

### OrderPaymentPending
`DuncanMcClean\Cargo\Events\OrderPaymentPending`

Dispatched when an order's status is changed to ["Payment Pending"](/the-basics/carts-and-orders#statuses).

```php
public function handle(OrderPaymentPending $event)
{
	$event->order;
}
```

### OrderPaymentReceived
`DuncanMcClean\Cargo\Events\OrderPaymentReceived`

Dispatched when an order's status is changed to ["Payment Received"](/the-basics/carts-and-orders#statuses).

```php
public function handle(OrderPaymentReceived $event)
{
	$event->order;
}
```

### OrderRefunded
`DuncanMcClean\Cargo\Events\OrderRefunded`

Dispatched after an order has been refunded.

```php
public function handle(OrderRefunded $event)
{
	$event->order;
	$event->amount;
}
```

### OrderReturned
`DuncanMcClean\Cargo\Events\OrderReturned`

Dispatched when an order's status is changed to ["Returned"](/the-basics/carts-and-orders#statuses).

```php
public function handle(OrderReturned $event)
{
	$event->order;
}
```

### OrderSaved
`DuncanMcClean\Cargo\Events\OrderSaved`

Dispatched after an order has been saved.

```php
public function handle(OrderSaved $event)
{
	$event->order;
}
```

### OrderShipped
`DuncanMcClean\Cargo\Events\OrderShipped`

Dispatched when an order's status is changed to ["Shipped"](/the-basics/carts-and-orders#statuses).

```php
public function handle(OrderShipped $event)
{
	$event->order;
}
```

### ProductNoStockRemaining
`DuncanMcClean\Cargo\Events\ProductNoStockRemaining`

Dispatched when a product or variant has no stock remaining. `$event->product` could be a `Product` instance or a `ProductVariant` instance.

```php
public function handle(ProductNoStockRemaining $event)
{
	$event->product;
}
```

### ProductStockLow
`DuncanMcClean\Cargo\Events\ProductStockLow`

Dispatched when a product or variant is running low on stock. The "low stock" threshold is configurable in Cargo's config file:

```php
// config/cargo.php

'products' => [
	'low_stock_threshold' => 5,
],
```

`$event->product` could be a `Product` instance or a `ProductVariant` instance.

```php
public function handle(ProductStockLow $event)
{
	$event->product;
}
```

### TaxClassDeleted
`DuncanMcClean\Cargo\Events\TaxClassDeleted`

Dispatched after a tax class has been deleted.

```php
public function handle(TaxClassDeleted $event)
{
	$event->taxClass;
}
```

### TaxClassSaved
`DuncanMcClean\Cargo\Events\TaxClassSaved`

Dispatched after a tax class has been saved.

```php
public function handle(TaxClassSaved $event)
{
	$event->taxClass;
}
```

### TaxZoneDeleted
`DuncanMcClean\Cargo\Events\TaxZoneDeleted`

Dispatched after a tax zone has been deleted.

```php
public function handle(TaxZoneDeleted $event)
{
	$event->taxZone;
}
```

### TaxZoneSaved
`DuncanMcClean\Cargo\Events\TaxZoneSaved`

Dispatched after a tax zone has been saved.

```php
public function handle(TaxZoneSaved $event)
{
	$event->taxZone;
}
```