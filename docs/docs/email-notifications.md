---
title: Email Notifications
---

When you install Cargo, it will automatically create a [mailable](https://laravel.com/docs/master/mail) for you, resulting in a class in `app/Mail` and a view in `resources/views/emails`.

Cargo will also configure an [event listener](https://laravel.com/docs/master/events#closure-listeners) in your `AppServiceProvider`, trigger the email to send whenever Cargo's [`OrderPaymentReceived`](/docs/events#orderpaymentreceived) event is dispatched.

```php
// app/Providers/AppServiceProvider.php

use App\Mail\OrderConfirmation;
use DuncanMcClean\Cargo\Events\OrderPaymentReceived;
use Illuminate\Support\Facades\Event;  
use Illuminate\Support\Facades\Mail;

Event::listen(OrderPaymentReceived::class, function ($event) {  
    Mail::to($event->order->customer())  
        ->locale($event->order->site()->shortLocale())  
        ->send(new OrderConfirmation($event->order));  
});
```

To change the content of the email, all you need to do is edit the view or mailable class in your app.

## Sending your own emails 
If you want to send any other emails, you can create your own mailable, then configure an event listener to trigger it based on one of [Cargo's events](/docs/events):

```sh
php artisan make:mail OrderOnTheWay
```

```php
// app/Providers/AppServiceProvider.php

use App\Mail\OrderOnTheWay;
use DuncanMcClean\Cargo\Events\OrderShipped;
use Illuminate\Support\Facades\Event;  
use Illuminate\Support\Facades\Mail;

Event::listen(OrderShipped::class, function ($event) {  
    Mail::to($event->order->customer())  
        ->locale($event->order->site()->shortLocale())  
        ->send(new OrderOnTheWay($event->order));  
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
```

You may want to wrap the route in a `if (! app()->isProduction())` conditional to ensure the email isn't accessible in production.