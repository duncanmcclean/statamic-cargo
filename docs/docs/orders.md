---
title: Orders
description: "Orders are created at the end of the Checkout process, just before the payment is confirmed. This page explains how to manage orders, including blueprints, statuses, and storage options."
---
Orders are created at the end of the [Checkout](/frontend/checkout/introduction) process, just before the payment is confirmed.

## Blueprint
If you need to add additional fields to orders, you can do so by updating the Order blueprint, found on the "Blueprints" page in the Control Panel:

![Order Blueprint](/images/order-blueprint.png)

The blueprint only contains your custom fields. They'll be merged with Cargo's built-in order blueprint.

## Statuses
Orders can move between various different statuses:
* Payment Pending
* Payment Received
* Shipped
* Returned
* Cancelled

You can change an order's status manually on the order details page in the Control Panel:

![Order Status dropdown](/images/order-status-dropdown.png)

Sometimes, an order's status may be updated automatically. For example: an order will move to "Payment Received" when a payment has been confirmed.

### Shipping
When you change an order's status to "Shipped", Cargo will let you input a "tracking number" for the order and will give you the ability to print/download a packing slip.

![Packing Slip](/images/packing-slip.png)

If you want to customise the packing slip, you may publish it with the following command:

```
php artisan vendor:publish --tag=cargo-packing-slip
```

## Timeline
The Timeline feature provides a complete audit trail of order changes, visible when viewing orders in the Control Panel. It automatically tracks key events including:

- Order creation and updates
- Status changes
- Refunds

Each event records the authenticated user who made the change and any relevant metadata.

![Order Timeline](/images/order-timeline.png)

### Custom Timeline Events
You can extend the Timeline by registering custom event types to track additional order activities beyond the built-in events.

Create a PHP class that extends `TimelineEventType` to represent your custom event:

```php
// app/TimelineEventTypes/OrderDelivered.php

use DuncanMcClean\Cargo\Orders\TimelineEventType;

class OrderDelivered extends TimelineEventType
{
    public function message() : string
    {
        return "Order Delivered by Royal Mail";
    }
}
```

Register your custom event type in your `AppServiceProvider`:

```php
// app/Providers/AppServiceProvider.php

public function boot(): void
{
	OrderDelivered::register();
}
```

Then listen for the relevant event in your application and append your custom timeline event to the order:

```php
// app/Listeners/RoyalMailPackageDeliveredListener.php

use DuncanMcClean\Cargo\Facades\Order;
use RoyalMail\Events\PackageDelivered;

class RoyalMailPackageDeliveredListener
{
    public function handle(PackageDelivered $event)
    {
        $order = Order::find('the-order-id');
        
        $order->appendTimelineEvent(
            eventType: OrderDelivered::class, 
            metadata: [
                'Foo' => 'bar',
                'Baz' => 'qux', 
            ],
        );
    }
}
```

## Storage
Out of the box, orders are stored as YAML files in the `content/cargo/orders` directory. If you wish, you can change the directory in the `cargo.php` config file:

```php
// config/statamic/cargo.php

'orders' => [  
    'repository' => 'file',  
  
    'directory' => base_path('orders'),
],
```

### Database
You can also opt to store orders in a traditional database, which might be useful for high-traffic stores.

To move orders to the database, run this command:

```
php please statamic:cargo:database-orders
```

It'll automatically publish database migrations, update your `cargo.php` config file and import existing orders into the database.

If needed, you can customise the eloquent models Cargo uses by updating the `model` and/or `line_items_model` keys in the `cargo.php` config file. 

:::tip warning
Make sure you have a backup strategy in place before moving carts to the database, in case the worst happens. Both [Laravel Forge](https://forge.laravel.com/docs/servers/backups) and [Ploi](https://ploi.io/features/database-backups) have built-in solutions for database backups.
:::