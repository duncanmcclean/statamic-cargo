---
title: Multi-site
description: "Cargo supports Statamic's multi-site feature, enabling you to sell goods internationally in different currencies or use the same Statamic instance to power multiple brands."
---

Cargo supports Statamic's [multi-site](https://statamic.dev/multi-site) feature, enabling you to sell goods internationally in different currencies or use the same Statamic instance to power multiple brands.

Carts and Orders are separate for each site - and can be viewed separately in the Control Panel.

## Enabling multi-site
When you enable multi-site with the `php please multisite` command, Cargo will automatically move your carts and orders directories into a subdirectory.

``` files
content/
	cargo/
		carts/
			en/ # [tl! ++]
		orders/
			en/ # [tl! ++]
``` 

## Adding a site
When adding a new site, make sure you add a `currency` attribute. Cargo uses this setting to determine how prices are displayed on your frontend.

![](/images/site-currency-attribute.png)

You will also need to create separate checkout routes for each site. They can use the same views/controllers, but the route names need to be different.

```php
// routes/web.php

// UK
Route::statamic('cart', 'cart', ['title' => 'Cart']);  
  
Route::statamic('checkout', 'checkout.index', ['title' => 'Checkout', 'layout' => 'checkout.layout'])  
    ->name('uk.checkout');  
  
Route::statamic('checkout/confirmation', 'checkout.confirmation', ['title' => 'Order Confirmation', 'layout' => 'checkout.layout'])  
    ->name('uk.checkout.confirmation')  
    ->middleware('signed');

// German
Route::statamic('de/cart', 'cart', ['title' => 'Warenkorb']);  

Route::statamic('de/checkout', 'checkout.index', ['title' => 'Kasse', 'layout' => 'checkout.layout'])  
    ->name('de.checkout');  
  
Route::statamic('de/checkout/confirmation', 'checkout.confirmation', ['title' => 'Checkout-Bestätigung', 'layout' => 'checkout.layout'])  
    ->name('de.checkout.confirmation')  
    ->middleware('signed');

``` 

After adding the routes, you will need to update the `cargo.php` config file to point at the new site-specific routes.

For example:

```php
// config/statamic/cargo.php

'routes' => [  
	'uk' => [
		'checkout' => 'uk.checkout',  
	    'checkout_confirmation' => 'uk.checkout.confirmation',  
	],

	'de' => [
		'checkout' => 'de.checkout',  
	    'checkout_confirmation' => 'de.checkout.confirmation',  
	]
],
``` 