---
title: "JSON API: Introduction"
---
When the `<form>` tag just doesn't cut it anymore, Cargo provides a JSON API allowing you to interact with the cart in a more flexible way.

You can do everything you would normally do with the form tags, but just with JSON instead. Particularly useful if you don't want a page reload after submitting forms.

## Customizing Responses
By default, cart responses include every augmented value from the cart, plus every augmented value from each line item's product. If your product blueprints contain a lot of fields, you may wish to slim down the response.

To do this, create your own resource class, extending Cargo's `CartResource`, and return only the values you need:

```php
namespace App\Http\Resources;

use DuncanMcClean\Cargo\Http\Resources\API\CartResource;

class SlimCartResource extends CartResource
{
    public function toArray($request)
    {
        return $this->resource
            ->toAugmentedCollection(['id', 'grand_total', 'line_items'])
            ->withShallowNesting()
            ->toArray();
    }
}
```

Then, tell Cargo to use your resource class by calling `Resource::map` in your `AppServiceProvider`'s `boot` method:

```php
use App\Http\Resources\SlimCartResource;
use DuncanMcClean\Cargo\Http\Resources\API\CartResource;
use DuncanMcClean\Cargo\Http\Resources\API\Resource;

Resource::map([
    CartResource::class => SlimCartResource::class,
]);
```

Cargo will then use your resource class whenever it returns the cart, including from the [Current Cart](/frontend/json-api/endpoints#current-cart) and line item endpoints.

:::tip warning
Customizing the response may break parts of the [pre-built checkout](/frontend/checkout/introduction) process, as it relies on values from the JSON response. Before removing a value, do a "find in files" across your project to make sure nothing is using it.
:::