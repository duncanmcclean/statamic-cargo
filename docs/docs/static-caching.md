---
title: Static Caching
---

Static Caching is a must-have for Statamic sites. It helps to make your website blazing fast, which is important for e-commerce sites, where every second counts.

However, there's a few things you should configure before using static caching with Cargo.

## Dynamic Content
You should use the `{{ nocache }}` tag anywhere you're displaying information about the customer's cart on your frontend.

This could be a counter in your nav, or a dynamic section on a product page that shows different content based on if a product has been added to the cart.

::tabs
::tab antlers
```antlers
{{ nocache }} {{# [tl! add] #}}
	{{ if ! {cart:is_empty} }}
	    <a href="/cart">
	        {{ {cart:line_items} | count }} items
	    </a>  
	{{ /if }}
{{ /nocache }} {{# [tl! add] #}}
``` 
::tab blade
```blade
<statamic:nocache> {{-- [tl! add] --}}
	@if(! Statamic::tag('cart:is_empty'))
		<a href="/cart">
			{{ count(Statamic::tag('cart:line_items')) }} items
		</a>  
	@endif
</statamic:nocache> {{-- [tl! add] --}}
``` 
::

## Cart & Checkout pages
Since cart and checkout pages are dynamic, we **strongly recommend** excluding them from the static cache.

```php
// config/statamic/static_caching.php 

return [
	'exclude' => [
		'class' => null,
		
		'urls' => [
			'/cart', // [tl! add]
			'/checkout*', // [tl! add]
        ],
    ],
];
``` 
