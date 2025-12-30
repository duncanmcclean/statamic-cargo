---
title: Available events
---

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

### DiscountCreated
`DuncanMcClean\Cargo\Events\DiscountCreated`

Dispatched after a discount has been created.

```php
public function handle(DiscountCreated $event)
{
	$event->discount;
}
```

### DiscountDeleted
`DuncanMcClean\Cargo\Events\DiscountDeleted`

Dispatched after a discount has been deleted.

```php
public function handle(DiscountDeleted $event)
{
	$event->discount;
}
```

### DiscountRedeemed
`DuncanMcClean\Cargo\Events\DiscountRedeemed`

Dispatched when a discount is redeemed during the checkout process.

```php
public function handle(DiscountRedeemed $event)
{
	$event->discount;
}
```

### DiscountSaved
`DuncanMcClean\Cargo\Events\DiscountSaved`

Dispatched after a discount has been saved.

```php
public function handle(DiscountSaved $event)
{
	$event->discount;
}
```

### OrderCancelled
`DuncanMcClean\Cargo\Events\OrderCancelled`

Dispatched when an order's status is changed to ["Cancelled"](/docs/orders#statuses).

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

Dispatched when an order's status is changed to ["Payment Pending"](/docs/orders#statuses).

```php
public function handle(OrderPaymentPending $event)
{
	$event->order;
}
```

### OrderPaymentReceived
`DuncanMcClean\Cargo\Events\OrderPaymentReceived`

Dispatched when an order's status is changed to ["Payment Received"](/docs/orders#statuses).

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

Dispatched when an order's status is changed to ["Returned"](/docs/orders#statuses).

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

### OrderStatusUpdated
`DuncanMcClean\Cargo\Events\OrderStatusUpdated`

Dispatched when an order's status is changed.

```php
public function handle(OrderStatusUpdated $event)
{
	$event->order;
	$event->originalStatus;
	$event->updatedStatus;
}
```

### OrderShipped
`DuncanMcClean\Cargo\Events\OrderShipped`

Dispatched when an order's status is changed to ["Shipped"](/docs/orders#statuses).

```php
public function handle(OrderShipped $event)
{
	$event->order;
}
```

### ProductDownloaded
`DuncanMcClean\Cargo\Events\ProductDownloaded`

Dispatched when a product or variant is downloaded.

```php
public function handle(ProductDownloaded $event)
{
	$event->order;
	$event->lineItem; 
	
	$event->lineItem->product();
	$event->lineItem->variant();
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