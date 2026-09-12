---
title: Email Notifications
description: "Cargo can send email notifications to customers when they place an order and when it has been shipped. This page explains how to configure Cargo to send emails, and how to preview them in the browser."
---

When you install Cargo, it will automatically create two [mailables](https://laravel.com/docs/master/mail) for you, resulting in classes in `app/Mail` and views in `resources/views/emails`:

| Mailable | Sent when... |
| --- | --- |
| `App\Mail\OrderConfirmation` | An order's payment has been received. |
| `App\Mail\OrderShipped` | An order has been marked as shipped. If a tracking number has been provided, it'll be included in the email. |

Cargo will also configure [event listeners](https://laravel.com/docs/master/events#closure-listeners) in your `AppServiceProvider`, triggering the emails to send whenever Cargo's [`OrderPaymentReceived`](/docs/events#orderpaymentreceived) and [`OrderShipped`](/docs/events#ordershipped) events are dispatched.

```php
// app/Providers/AppServiceProvider.php

use App\Mail\OrderConfirmation;
use App\Mail\OrderShipped;
use DuncanMcClean\Cargo\Events\OrderPaymentReceived;
use DuncanMcClean\Cargo\Events\OrderShipped as OrderShippedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

Event::listen(OrderPaymentReceived::class, function ($event) {
    Mail::to($event->order->customer())
        ->locale($event->order->site()->shortLocale())
        ->send(new OrderConfirmation($event->order));
});

Event::listen(OrderShippedEvent::class, function ($event) {
    Mail::to($event->order->customer())
        ->locale($event->order->site()->shortLocale())
        ->send(new OrderShipped($event->order));
});
```

To change the content of the emails, all you need to do is edit the views or mailable classes in your app.

:::tip note
If you installed Cargo before the `OrderShipped` mailable was introduced, you can run `php please cargo:install` again to publish it. Any mailables you've already published will be left untouched.
:::

## Sending your own emails 
If you want to send any other emails, you can create your own mailable, then configure an event listener to trigger it based on one of [Cargo's events](/extending/events/list):

```sh
php artisan make:mail OrderCancelled
```

```php
// app/Providers/AppServiceProvider.php

use App\Mail\OrderCancelled;
use DuncanMcClean\Cargo\Events\OrderCancelled as OrderCancelledEvent;
use Illuminate\Support\Facades\Event;  
use Illuminate\Support\Facades\Mail;

Event::listen(OrderCancelledEvent::class, function ($event) {  
    Mail::to($event->order->customer())  
        ->locale($event->order->site()->shortLocale())  
        ->send(new OrderCancelled($event->order));  
});
```

For more information on sending emails, please consult the [Laravel documentation](https://laravel.com/docs/master/mail).

## Previewing emails in the browser
You can preview Mailables by returning them from a route, like this:

```php
// routes/web.php

Route::get('/order-confirmation', function () {
	$order = Order::query()->orderByDesc('date')->first();

	return new OrderConfirmation($order);
});

Route::get('/order-shipped', function () {
	$order = Order::query()->orderByDesc('date')->first();

	return new OrderShipped($order);
});
```

You may want to wrap the routes in a `if (! app()->isProduction())` conditional to ensure the emails aren't accessible in production.
