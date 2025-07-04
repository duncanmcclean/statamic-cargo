---
title: Listening for Events
---

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

For a more in-depth explanation on events, please consult the  [Laravel documentation](https://laravel.com/docs/events).