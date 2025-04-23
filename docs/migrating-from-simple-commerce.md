---
title: Migration from Simple Commerce
---
:::tip Note
This migration guide is still a work in progress - there's bound to be things missing. If you come across something which hasn't been covered here, please [open an issue](https://github.com/duncanmcclean/cargo/issues/new) or a [pull request](https://github.com/duncanmcclean/statamic-cargo/pulls).
:::

## Overview
Cargo is the natural evolution of [Simple Commerce](https://github.com/duncanmcclean/cargo). 

What started out as a hobby project for me to learn the internals of Statamic turned into *the way* to build e-commerce sites with Statamic.

Cargo takes everything you love about Simple Commerce, and makes it better in every possible way. Some of the big changes include....

* **A complete re-working of carts & orders** to ultimately make things faster and more reliable.
* **A redesigned Control Panel UI**, ensuring your admins can get what they need at a glance.
* **A massive overhaul to discounting**, allowing for automatic site-wide discounts, as well as traditional coupon-style discounts.
* **Pre-built Checkout Page**, allowing you to level up your customer's purchasing experience.

## Licensing
Before deploying Cargo to production, you will need to obtain a new license via the [Statamic Marketplace](https://statamic.com/addons/duncanmcclean/cargo). 

Cargo now costs $199 for new licenses, then $85 per year thereafter for support & updates, similar to Statamic Pro.

If you've purchased Simple Commerce **within the last 12 months**, you can migrate for free.

Otherwise, if you purchased Simple Commerce **more than a year ago**, you can migrate to Cargo for $85.

Either way, just email support@builtwithcargo.dev with your order number and I'll send you a coupon code to use at checkout.

## Updating
To upgrade, uninstall Simple Commerce and install Cargo using composer:

```
composer remove duncanmcclean/simple-commerce
composer require duncanmcclean/cargo
```

Once composer has done its thing, run Cargo's install command to publish various stubs, select your products collection, and more:

```
php please cargo:install
```

Next, run the migrator script to copy over configuration options and migrate your data:

```
php please cargo:migrate
```

If you need to, you can safely re-run the `cargo:migrate` command multiple times. You can also run each of the migrators independently:

* `php please cargo:migrate:configs` 
* `php please cargo:migrate:customers` 
* `php please cargo:migrate:discounts` 
* `php please cargo:migrate:taxes` 
* `php please cargo:migrate:orders` 
* `php please cargo:migrate:carts` 

Finally, please review this migration guide thoroughly. You **will** need to make code changes as part of this upgrade.

:::tip Tip
Consider making small, regular commits during the migration process. This makes it easier for you to revert any accidental changes or reference things in the future.
:::

## Products
Products haven't changed much - they're still entries in Statamic, like you're used to.

### Product collections
It's now possible to configure multiple product collections. This may be handy if you want to split our digital products, like courses, from physical products, like merchandise.

You can specify additional collections in the `cargo.php` config file:

```php
// config/statamic/cargo.php

'products' => [
	'collections' => ['courses', 'merch'],
],
```

### Digital Products
To avoid cluttering the product publish form for stores selling *only* physical products, the Digital Product functionality is now opt-in:

```php
// config/statamic/cargo.php

'digital_products' => true,
```

:::tip Note
Cargo doesn't *currently* include support for digital downloads, like Simple Commerce did. However, this should be supported before launch. 
:::

Cargo doesn't include the "license keys" functionality which was part of Simple Commerce. If you need this, we recommend building it yourself.

## Carts
In Simple Commerce, carts were basically just unpaid orders. 

In Cargo, carts and orders are separate. A cart is created when a customer adds a product to their cart. An order is created when a customer checks out.

### Migrating carts
Cargo will have attempted to automatically migrate existing carts when you ran the `cargo:migrate` command. 

If you need to, you can migrate *just* carts using `php please cargo:migrate:carts`.

If you were previously storing orders in the database, Cargo will publish and run the relevant database migrations prior to migrating carts.

### Abandoned carts
During the install process, Cargo will have added its `purge-abandoned-carts` command to your app's `routes/console.php` file.

If you were previously scheduling Simple Commerce's `sc:purge-cart-orders` command, either in `routes/console.php` or `app/Console/Kernel.php`, you can remove it.

## Orders
Orders are now stored within their own "stache store", making them independent from collections/entries.

This change was made for a variety reasons, with the main one being that it gives Cargo more control around how everything works, than piggybacking on collections.

### Migrating orders
Cargo will have attempted to automatically migrate existing orders when you ran the `cargo:migrate` command. 

If you need to, you can migrate *just* orders using `php please cargo:migrate:orders`.

If you were previously storing orders in the database, Cargo will publish and run the relevant database migrations prior to migrating orders.

### Blueprint
Since orders are no longer stored in collections, Cargo provides its own "Order" blueprint, which can be edited via the Blueprints page in the Control Panel:

![Order Blueprint](/images/order-blueprint.png)

During the migration process, Cargo will have attempted to copy across any custom fields you added to the order collection's blueprint.

### Status Log
The "Status Log" feature present in Simple Commerce doesn't exist in Cargo. Cargo simply displays the current status of orders.

If you need to occasionally reference the history of order statuses, you may wish to take advantage of [Statamic's Git Automation](https://statamic.dev/git-automation) if you're storing orders in flat files, or use a package like [`spatie/laravel-activitylog`](https://github.com/spatie/laravel-activitylog) if you're using a database.

## Customers
By default, Simple Commerce stored information about your customers in a customers collection or `customers` database table.

Upon reflecting, this setup didn't really make sense and kinda became a pain if you wanted customers to login to your site in the future.

In Cargo, there are two types of customers:
* Users
	* These are plain old [Statamic users](https://statamic.dev/users).
	* When a customer is logged in, the logged in user will be automatically associated with the cart. 
	* This makes it easy to manage your customers in the Control Panel and built user account functionality, if needed.
* Guest customers
	* Guest customers are saved on individual orders, useful for stores without user accounts.
	* You need to provide `customer[name]` and `customer[email]` inputs during checkout for a guest customer to be created

### Migrating customers
Cargo will have attempted to automatically migrate existing customers when you ran the `cargo:migrate` command. 

If you need to, you can migrate *just* customers using `php please cargo:migrate:customers`.

:::tip Note
You should still run the migration command if you were previously storing customers as users, as it'll remove the `orders` key from user data (it is now a computed field).
:::

## Discounting
Cargo builds on top of the coupons feature in Simple Commerce, allowing you to create site-wide discounts which are automatically applied to eligible carts.

If you only want a discount to be redeemable using a coupon code (like how it worked in Simple Commerce), you can specify a "Discount code" on the discount.
### Behaviour changes
There are a few notable behaviour changes when compared with Simple Commerce's coupons feature...
* Discounts can no longer be limited by email domain, only be linking existing customers.
* Discounts are now calculated on *individual* line items, rather than the cart as a whole. 
* It's now possible for multiple discounts to be applied at once.
	* Although, there can only be one discount code applied at once.
* Simple Commerce prevented customers from applying coupon codes which weren't eligible for items in the cart. 
	* This isn't the same in Cargo. Customers can apply discount codes, even when none of the eligible products are in the cart. 
		* Obviously, in this case, no discount will actually be applied.

### Migrating discounts
Cargo will have attempted to automatically migrate existing discounts when you ran the `cargo:migrate` command. 

If you need to, you can migrate *just* discounts using `php please cargo:migrate:discounts`.

## Taxes
The whole tax situation in Simple Commerce was pretty confusing... there were two tax engines, you had to go through multiple screens to wire stuff up. Not an ideal UX.

Taxation has been drastically improved in Cargo. There's now only "one" tax engine - it has two concepts:
* *Tax Classes* are assigned to products, representing the "rate" of tax which should be applied
* *Tax Zones* represent the physical area where certain tax rules apply. 
	* This is where you set rates for each of the configured tax classes.

![Screenshot of create tax zone page](/images/create-tax-zone.png)

### Configuration
By default, prices are **inclusive** of tax. If you would rather them be exclusive of taxes instead, you can disable the behaviour in the `cargo.php` config file:

```php
// config/statamic/cargo.php

'taxes' => [  
    // Enable this when product prices are entered inclusive of tax.  
    // When calculating taxes, the tax will be deducted from the product price, then added back on at the end.    
    'price_includes_tax' => true,  
],
```

### Migrating taxes
Cargo will have attempted to automatically migrate your tax configuration when you ran the `cargo:migrate` command. 

If you need to, you can migrate *just* taxes using `php please cargo:migrate:taxes`.

## Frontend
### Antlers
Cargo has renamed and removed a lot of tags from Simple Commerce. For ease of reference, a table has been provided below with the old tag, and the new/alternative tag in Cargo.

You should use the "find & replace" feature in your code editor to well... find & replace references to the old tags in your templates.

| Old                                                            | New                                                                                                                                                                                                                                            |
| -------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `{{ sc:cart }}`                                                | `{{ cart }}`                                                                                                                                                                                                                                   |
| `{{ sc:cart:* }}`                                              | `{{ cart:* }}`                                                                                                                                                                                                                                 |
| `{{ sc:cart:has }}`                                            | `{{ cart:exists }}`                                                                                                                                                                                                                            |
| `{{ sc:cart:items }}`                                          | `{{ cart:line_items }}`                                                                                                                                                                                                                        |
| `{{ sc:cart:count }}`                                          | `{{ {cart:line_items} \| count }}`                                                                                                                                                                                                             |
| `{{ sc:cart:quantity_total }}`                                 | `{{ {cart:line_items} \| sum('quantity') }}`                                                                                                                                                                                                   |
| `{{ sc:cart:total }}`                                          | `{{ cart:grand_total }}`                                                                                                                                                                                                                       |
| `{{ sc:cart:free }}`                                           | `{{ cart:is_free }}`                                                                                                                                                                                                                           |
| `{{ sc:cart:grand_total }}`                                    | `{{ cart:grand_total }}`                                                                                                                                                                                                                       |
| `{{ sc:cart:raw_grand_total }}`                                | `{{ cart:grand_total \| raw }}`                                                                                                                                                                                                                |
| `{{ sc:cart:items_total }}`                                    | `{{ cart:sub_total }}`                                                                                                                                                                                                                         |
| `{{ sc:cart:raw_items_total }}`                                | `{{ cart:sub_total \| raw }}`                                                                                                                                                                                                                  |
| `{{ sc:cart:items_total_with_tax }}`                           | `{{ {cart:sub_total} + {cart:line_items \| sum('tax_total')} }}`                                                                                                                                                                               |
| `{{ sc:cart:shipping_total }}`                                 | `{{ cart:shipping_total }}`                                                                                                                                                                                                                    |
| `{{ sc:cart:raw_shipping_total }}`                             | `{{ cart:shipping_total \| raw }}`                                                                                                                                                                                                             |
| `{{ sc:cart:shipping_total_with_tax }}`                        | `{{ {cart:shipping_total} + {cart:shipping_tax_total} }}`                                                                                                                                                                                      |
| `{{ sc:cart:tax_total }}`                                      | `{{ cart:tax_total }}`                                                                                                                                                                                                                         |
| `{{ sc:cart:raw_tax_total }}`                                  | `{{ cart:tax_total \| raw }}`                                                                                                                                                                                                                  |
| `{{ sc:cart:tax_total_split }}`                                | `{{ cart:tax_breakdown }}`<br><br>You can output `rate`, `description`, `zone` and `amount`.                                                                                                                                                   |
| `{{ sc:cart:raw_tax_total_split }}`                            | `{{ cart:tax_breakdown \| raw }}`                                                                                                                                                                                                              |
| `{{ sc:cart:coupon_total }}`                                   | `{{ cart:discount_total }}`                                                                                                                                                                                                                    |
| `{{ sc:cart:raw_coupon_total }}`                               | `{{ cart:discount_total \| raw }}`                                                                                                                                                                                                             |
| `{{ sc:cart:addItem }}` / `{{ sc:cart:add_item }}`             | `{{ cart:add }}`                                                                                                                                                                                                                               |
| `{{ sc:cart:updateItem }}` / `{{ sc:cart:update_item }}`       | `{{ cart:update_line_item }}`                                                                                                                                                                                                                  |
| `{{ sc:cart:removeItem }}` / `{{ sc:cart:remove_item }}`       | `{{ cart:remove }}`                                                                                                                                                                                                                            |
| `{{ sc:cart:update }}`                                         | `{{ cart:update }}`                                                                                                                                                                                                                            |
| `{{ sc:cart:empty }}`                                          | `{{ cart:delete }}`                                                                                                                                                                                                                            |
| `{{ sc:cart:alreadyExists }}` / `{{ sc:cart:already_exists }}` | `{{ cart:added }}`                                                                                                                                                                                                                             |
| `{{ sc:checkout }}`                                            | Removed. See the [Checkout](#checkout) section below.                                                                                                                                                                                          |
| `{{ sc:checkout:* }}`                                          | Removed. See the [Checkout](#checkout) section below.                                                                                                                                                                                          |
| `{{ sc:coupon }}`                                              | Removed                                                                                                                                                                                                                                        |
| `{{ sc:coupon:has }}`                                          | `{{ if {cart:discount_code} }}`                                                                                                                                                                                                                |
| `{{ sc:coupon:redeem }}`                                       | ```antlers<br>{{ cart:update }}<br>    <input name="discount_code"><br>{{ /cart:update }}<br>```                                                                                                                                               |
| `{{ sc:coupon:remove }}`                                       | ```antlers<br>{{ cart:update }}<br>    <input name="discount_code"><br>{{ /cart:update }}<br>```<br><br>To remove a discount code, an empty `discount_code` input should be probided.                                                          |
| `{{ sc:coupon:* }}`                                            | Removed                                                                                                                                                                                                                                        |
| `{{ sc:customer:* }}`                                          | `{{ cart:customer }}`                                                                                                                                                                                                                          |
| `{{ sc:customer:update }}`                                     | ```antlers<br>{{ cart:update }}<br>    <input name="customer[name]"><br>	<input name="customer[email]"><br>{{ /cart:update }}<br>```<br><br>You can alternatively use Statamic's [`profile_form` tag](https://statamic.dev/tags/user-profile). |
| `{{ sc:customer:orders }}`                                     | `{{ orders :customer:is="cart:customer:id" }}`                                                                                                                                                                                                 |
| `{{ sc:customer:order }}`                                      | `{{ orders :customer:is="cart:customer:id" :id:is="get:order_id" }}`                                                                                                                                                                           |
| `{{ sc:gateway }}`                                             | `{{ payment_gateways }}`                                                                                                                                                                                                                       |
| `{{ sc:gateway:count }}`                                       | `{{ payment_gateways \| count }}`                                                                                                                                                                                                              |
| `{{ sc:gateway:* }}`                                           | Removed.                                                                                                                                                                                                                                       |
| `{{ sc:shipping:methods }}`                                    | `{{ shipping_methods }}`                                                                                                                                                                                                                       |
| `{{ sc:countries }}`                                           | `{{ dictionary:countries }}`<br><br>Uses Statamic's [built-in country dictionary](https://statamic.dev/fieldtypes/dictionary#countries).                                                                                                       |
| `{{ sc:currencies }}`                                          | Removed.                                                                                                                                                                                                                                       |
| `{{ sc:regions }}`                                             | `{{ states }}`                                                                                                                                                                                                                                 |
| `{{ sc:errors }}`                                              | `{{ get_errors:all }}`<br><br>The `get_errors` tag is [built into Statamic](https://statamic.dev/tags/get_errors).                                                                                                                             |
| `{{ sc:has_errors }}` / `{{ sc:hasErrors }}`                   | `{{ {get_errors:all \| count} > 0 }}`<br><br>The `get_errors` tag is [built into Statamic](https://statamic.dev/tags/get_errors).                                                                                                              |
In addition to changes of tag names, some variables have been renamed as well. A non-exclusive list is provided below:

| Old                   | New               |
| --------------------- | ----------------- |
| `items`               | `line_items`      |
| `items_total`         | `sub_total`       |
| `coupon_total`        | `discount_total`  |
| `gateway`             | `payment_gateway` |
| `free`                | `is_free`         |
| `total_including_tax` | Removed.          |

:::tip Tip
The [`{{ dump }}`](https://statamic.dev/tags/dump) tag is an easy way of debugging data inside the current view context (eg. seeing which variables are defined at the current point in time).
:::

The `currency` modifier has been removed in Cargo.

### Blade
If you prefer to use Laravel Blade, a lot of the changes mentioned in the [Antlers](#antlers) section will still apply. Please review it and make any necessary changes.

### JSON API
The JSON API has changed quite a bit between Simple Commerce and Cargo, including the URLs of endpoints and the structure of responses.

You can find a mapping of API endpoints below:

| Old                                               | New                                                                                                                                                                                                                               |
| ------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| GET `/!/simple-commerce/cart`                     | GET `/!/cargo/cart`                                                                                                                                                                                                               |
| POST `/!/simple-commerce/cart`                    | POST `/!/cargo/cart`                                                                                                                                                                                                              |
| DELETE `/!/simple-commerce/cart`                  | DELETE `/!/cargo/cart`                                                                                                                                                                                                            |
| POST `/!/simple-commerce/cart-items`              | POST `/!/cargo/cart/line-items`                                                                                                                                                                                                   |
| POST `/!/simple-commerce/cart-items/{cartItem}`   | POST `/!/cargo/cart/line-items/{lineItem}`                                                                                                                                                                                        |
| DELETE `/!/simple-commerce/cart-items/{cartItem}` | DELETE `/!/cargo/cart/line-items/{lineItem}`                                                                                                                                                                                      |
| GET `/!/simple-commerce/customer/{customer}`      | Removed.                                                                                                                                                                                                                          |
| POST `/!/simple-commerce/customer/{customer}`     | Removed, in favour of updating the customer via the POST `/!/cargo/cart` endpoint.<br><br>Alternatively, you could make an AJAX request to the [`{{ user:profile_form }}`](https://statamic.dev/tags/user-profile_form) endpoint. |
| POST `/!/simple-commerce/checkout`                | Removed. See the [Checkout](#checkout) section below.                                                                                                                                                                             |
| POST `/!/simple-commerce/coupon`                  | Removed, in favour of updating the discount code via the POST `/!/cargo/cart` endpoint.                                                                                                                                           |
| DELETE `/!/simple-commerce/coupon`                | Removed, in favour of removing the discount code via the POST `/!/cargo/cart` endpoint.                                                                                                                                           |

For more information about the JSON API, please consult the [JSON API](/docs/json-api) page.

### Checkout
In order to improve reliability and make things easier to understand, Cargo has re-worked the checkout process. As a result, you will need to make some changes to your checkout templates.

:::tip Tip
If you haven't made too many changes to your checkout page, we recommend publishing Cargo's pre-built checkout page and working from there.

It handles everything on one page, without the need for full-page refreshes, in addition to a sleek modern design.

To publish the pre-built checkout flow into your project, run this command:

```
php artisan vendor:publish --tag=cargo-checkout-stubs
```

Then, add the following to your `routes/web.php` file:

```php
Route::statamic('checkout', 'checkout.index', ['title' => 'Checkout', 'layout' => 'checkout.layout'])  
	->name('checkout');  
		  
Route::statamic('checkout/confirmation', 'checkout.confirmation', ['title' => 'Order Confirmation', 'layout' => 'checkout.layout'])  
	->name('checkout.confirmation')  
	->middleware('signed');
```

![Screenshot of Cargo's pre-built checkout flow](/images/prebuilt-checkout.png)
:::

Cargo removes the `{{ checkout }}` tag, which was used for collecting the customer's payment details.

In its place, each payment gateway now has a dedicated checkout URL, which can either be passed as a return/callback URL to the gateway, or submitted using a `<form>`. 

The checkout URL will handle validation and the creation of the order.

You can use the `{{ payment_gateways }}` tag to loop through the cart's available payment gateways:

```antlers
{{ payment_gateways }}
	<details name="payment_gateway" {{ if first }} open {{ /if }}>
		<summary>{{ name }}</summary>
		<div>
			{{ partial src="checkout/payment-forms/{handle}" }}
		</div>
	</details>
{{ /payment_gateways }}
```

Inside the loop, you have access to the following variables:
* `name` 
* `handle` 
* `checkout_url` 
* Anything returned by the payment gateway's `setup` method.

Every [payment gateway](/docs/payment-gateways) should provide a "payment form" template in its documentation. 

In the above example, each payment form lives in its own partial, so you can create one and copy the provided code into there.

When the cart total is $0, you should instead display a generic checkout URL, which'll handle validation and creating the order:

```antlers
{{ if {cart:is_free} }}
<form action="{{ route:statamic.cargo.cart.checkout }}">
	<p>{{ 'No payment required. Continue to checkout.' | trans }}</p>

	<button>Checkout</button>
</form>
{{ /if }}
```

For more information, and examples on building custom checkout flows, please see the [Checkout](/docs/checkout) page.

## Payments
After migrating to Cargo, you'll need to re-configure your site's payment gateways in the `cargo.php` config file.

### Dummy
#### Configuration
You will need to add the following lines to the `payments.gateways` config option in order to use the dummy payment gateway.

```php
// config/statamic/cargo.php

'payments' => [  
    'gateways' => [  
        'dummy' => [], // [tl! add]
	],
],
```

#### Payment form
:::tip Note
If you're adopting Cargo's [pre-built checkout flow](/docs/checkout), you don't need to do this step.
:::

You will need to update the payment form for the Dummy payment gateway in your templates. You can find the latest version on the [Payment Gateways](/docs/payment-gateways#dummy) page.

### Stripe
#### Configuration
You will need to add the following lines to the `payments.gateways` config option in order to use the stripe payment gateway.

```php
// config/statamic/cargo.php

'payments' => [  
    'gateways' => [  
        'stripe' => [ // [tl! add]
            'key' => env('STRIPE_KEY'), // [tl! add]
            'secret' => env('STRIPE_SECRET'), // [tl! add]
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'), // [tl! add]
        ], // [tl! add]
	],
],
```

#### Payment form
:::tip Note
If you're adopting Cargo's [pre-built checkout flow](/docs/checkout), you don't need to do this step.
:::

You will need to update the payment form for the Stripe payment gateway in your templates. You can find the latest version on the [Payment Gateways](/docs/payment-gateways#dummy) page.

#### Webhook
When deploying your changes live, you will need to update your app's webhook URL in the Stripe Dashboard.

Previously, the URL was `https://example.com/!/simple-commerce/gateways/{gateway}/webhook`.

It is now `https://example.com/!/cargo/payments/{gateway}/webhook`.

:::tip Note
If you were using Simple Commerce's Card Elements implementation, you will need to *create* a webhook in Stripe. It should listen for all charge and payment intent events.
:::

Whilst not strictly required, we highly recommend configuring a "webhook secret" to prevent malicious requests to the webhook.

You can find the webhook secret on the "Webhooks" page in the Stripe dashboard.

```
STRIPE_WEBHOOK_SECRET=whsec_...
```

#### Behaviour changes
The *authorization* and *capture* steps have been separated to account for incomplete/cancelled orders. 
* Authorization happens when the customer submits the checkout form.
* Capturing (actually taking the payment) happens when the webhook is received.

### Mollie
#### Configuration
You will need to add the following lines to the `payments.gateways` config option in order to use the mollie payment gateway.

```php
// config/statamic/cargo.php

'payments' => [  
    'gateways' => [  
        'mollie' => [ // [tl! add]
            'api_key' => env('MOLLIE_KEY'), // [tl! add]
            'profile_id' => env('MOLLIE_PROFILE_ID'), // [tl! add]
        ], // [tl! add]
	],
],
```

#### Payment form
:::tip Note
If you're adopting Cargo's [pre-built checkout flow](/docs/checkout), you don't need to do this step.
:::

You will need to update the payment form for the Mollie payment gateway in your templates. You can find the latest version on the [Payment Gateways](/docs/payment-gateways#dummy) page.

### PayPal
Cargo removed the PayPal payment gateway, in favour of using PayPal via another payment gateway like Stripe or Mollie.

The PayPal SDK used in Simple Commerce had been deprecated, and from what I can gather, there wasn't *too many* people using it.

From personal experience, it was also a pain to do any kind of testing with. 🫠

If anyone wants to port the PayPal gateway for Cargo, and release it as an addon, I'm happy to link to it here.

### Custom payment gateways
You should be able to re-use most of your existing code. It'll just need to be adjusted to work with Cargo's APIs.

#### API Changes
Payment Gateways should extend Cargo's abstract `PaymentGateway` class, and implement the [required methods](/docs/payment-gateways#methods).

Payment Gateways in `app/PaymentGateways` will be automatically registered by Cargo. They can then be referenced in the `cargo.php` config file using their handle:

```php
// config/statamic/cargo.php

'payments' => [  
    'gateways' => [  
        'custom_payment_gateway' => [
            // Any config options...
        ],
	],
],
```
#### Payment Form
As explained in the [Checkout](#checkout) section above, each payment gateway has its own checkout URL, which can either be used as a return/callback URL, or can be submitted to using a `<form>`, if needed.

```antlers
<div id="payment-details">

<script>
customPaymentGateway.init({
    return_url: '{{ checkout_url }}',
});
</script>
```

The gateway's `setup` method should return anything you need in the payment form. 

You shouldn't return any sensitive API keys though, as they will be available using Cargo's JSON API endpoint.

#### Webhooks
Each payment gateway is assigned its own webhook URL: `/!/cargo/payments/custom_payment_gateway/webhook`

## Shipping
After migrating to Cargo, you'll need to re-configure your site's shipping methods in the `cargo.php` config file:

```php
// config/statamic/cargo.php

'shipping' => [
	'methods' => [
		'free_shipping' => [],
	],
],
```

### Custom shipping methods
You should be able to re-use most of your existing code. It'll just need to be adjusted to work with Cargo's APIs.

#### API Changes
Shipping Methods should extend Cargo's abstract `ShippingMethod` class, and implement the [required methods](/docs/shipping#methods).

Unlike Simple Commerce, shipping methods can return multiple shipping options, each with their own price.


## Notifications
When you ran the `cargo:install` command at the start of the migration process, you may have noticed it created a mailable in `app/Mail`, a view in `resources/views/emails` and added an event listener to your `AppServiceProvider.

Instead of notifications being configured in the `cargo.php` config file, you're now responsible for the code which sends notifications to customers.

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

This gives you full control over how & when emails (or other kinds of notifications) are sent to customers, and does away with the whole palavar of publishing files and extending files in Simple Commerce.

To learn more about [event listeners](https://laravel.com/docs/master/events#defining-listeners) and [mailables](https://laravel.com/docs/master/mail#introduction), please consult the Laravel documentation.
## PHP APIs
While most things are in roughly the same place as they used to be in Simple Commerce, there are some pretty big things you should be aware of.

### Namespace
The most obvious change is that you'll need to reference `Cargo` in any imports instead of `SimpleCommerce`. 

It's a pretty easy find & replace:
* ***Before:** `DuncanMcClean\SimpleCommerce`
* ***After:** `DuncanMcClean\Cargo`

### Events
If you're listening to any of Simple Commerce's events, you will need to listen for Cargo's equivalent events.

| Old                                                          | New                                                                                                                                                                                                                                                                                                                                                                     |
| ------------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `DuncanMcClean\SimpleCommerce\Events\CouponRedeemed`         | `DuncanMcClean\Cargo\Events\DiscountRedeemed`                                                                                                                                                                                                                                                                                                                           |
| `DuncanMcClean\SimpleCommerce\Events\DigitalDownloadReady`   | Removed.                                                                                                                                                                                                                                                                                                                                                                |
| `DuncanMcClean\SimpleCommerce\Events\GatewayWebhookReceived` | Removed.                                                                                                                                                                                                                                                                                                                                                                |
| `DuncanMcClean\SimpleCommerce\Events\OrderPaymentFailed`     | Removed.                                                                                                                                                                                                                                                                                                                                                                |
| `DuncanMcClean\SimpleCommerce\Events\OrderSaved`             | `DuncanMcClean\Cargo\Events\OrderSaved`                                                                                                                                                                                                                                                                                                                                 |
| `DuncanMcClean\SimpleCommerce\Events\OrderStatusUpdated`     | Removed. Can be replaced with one of the following events:<br>* `DuncanMcClean\Cargo\Events\OrderCancelled`<br>* `DuncanMcClean\Cargo\Events\OrderPaymentPending`<br>* `DuncanMcClean\Cargo\Events\OrderPaymentReceived`<br>* `DuncanMcClean\Cargo\Events\OrderRefunded`<br>* `DuncanMcClean\Cargo\Events\OrderReturned`<br>* `DuncanMcClean\Cargo\Events\OrderShipped` |
| `DuncanMcClean\SimpleCommerce\Events\PaymentStatusUpdated`   | Removed. Can be replaced with one of the following events:<br>* `DuncanMcClean\Cargo\Events\OrderCancelled`<br>* `DuncanMcClean\Cargo\Events\OrderPaymentPending`<br>* `DuncanMcClean\Cargo\Events\OrderPaymentReceived`<br>* `DuncanMcClean\Cargo\Events\OrderRefunded`<br>* `DuncanMcClean\Cargo\Events\OrderReturned`<br>* `DuncanMcClean\Cargo\Events\OrderShipped` |
| `DuncanMcClean\SimpleCommerce\Events\PostCheckout`           | Removed.                                                                                                                                                                                                                                                                                                                                                                |
| `DuncanMcClean\SimpleCommerce\Events\PreCheckout`            | Removed.                                                                                                                                                                                                                                                                                                                                                                |
| `DuncanMcClean\SimpleCommerce\Events\StockRunOut`            | `DuncanMcClean\Cargo\Events\ProductNoStockRemaining`                                                                                                                                                                                                                                                                                                                    |
| `DuncanMcClean\SimpleCommerce\Events\StockRunningLow`        | `DuncanMcClean\Cargo\Events\ProductStockLow`                                                                                                                                                                                                                                                                                                                            |

For a full list of events, please consult the [Events](/docs/events) page.

### Products
Like Simple Commerce, Cargo has a `Product` facade, allowing you to fetch products. 

The `Product` instances returned by Cargo's facade are thin wrappers around Statamic's `Entry` class. They add some handy helper methods around things like product variants, stock, and taxation.

While you can use Cargo's `Product` facade in your own code, we would recommend using [Statamic's `Entry` repository](https://statamic.dev/repositories/entry-repository) directly instead.

### Carts
As mentioned earlier in this guide, carts and orders are now stored separately. You can use the `Cart` facade to find, query and create new carts:

```php
use DuncanMcClean\Cargo\Facades\Cart;

// Find a cart
Cart::find(123);

// Query carts by customer
Cart::query()
	->where('customer', 'user-id')
	->get();

// Make and save a Cart instance
Cart::make()
	->orderNumber(1234)
	->save();
```

To find out more about the `Cart` facade and its available methods, please visit the [PHP APIs](/docs/php-apis) page.


### Orders
Like Simple Commerce, Cargo provides an `Order` facade, allowing you to find, query and create new orders.

We've documented some of the major changes to the `Order` facade below...

#### Order Statuses
Order Statuses have been simplified in Cargo. The `->orderStatus()` and `->paymentStatus()` methods have been removed.

Instead, you can get/set an orders's status using the `->status()` method:

```php
$order->status(OrderStatus::PaymentReceived);
```

Cargo also removes the `->statusLog()` method which existed in Simple Commerce.

#### Discounting
As part of Cargo's overhaul to discounting, the `->coupon()` and `->redeemCoupon()` methods have been removed. 

You should instead set the `discount_code` key on the **cart** (not the order, as discounts can't be applied after checkout).

```php
$cart->set('discount_code', 'BLACKFRIDAY')->save();
```

#### Line Items
Cargo removed the `addLineItem`, `updateLineItem`, `removeLineItem` and `clearLineItem`  methods, in favour of equivalent methods on the `LineItems` collection:

```php
// Add a line item
$order->addLineItem([ // [tl! --]
$order->lineItems()->create([ // [tl! ++]
	'product' => 'product-id',
	'quantity' => 1,
	'total' => 1500,
]);


// Update a line item
$order->updateLineItem($lineItemId, [ // [tl! --]
$order->lineItems()->update($lineItemId, [ // [tl! ++]
	'quantity' => 2,
]);

// Remove a line item
$order->removeLineItem($lineItemId); // [tl! --]
$order->lineItems()->remove($lineItemId); // [tl! ++]

// Clear all line items from the order
$order->clearLineItems(); // [tl! --]
$order->lineItems()->flush(); // [tl! ++]
```

#### Customers
Cargo has significantly simplified the concept of customers. They are either users in Statamic, or guest customers whose information is stored on individual orders.

To associate an order with a user, pass either the `User` object or their ID to the `->customer()` method:

```php
$order->customer($user);

$order->customer($user->id());
```

To associate an order with a guest customer, pass an email with `name` and `email` keys to the `->customer()` method:

```php
$order->customer([
	'name' => 'David Hasselhoff',
	'email' => 'david@hasselhoff.com',
]);
```

#### Totals
There's a couple of notable changes around order totals, and how order totals are calculated.

* The `->itemsTotal()` method has been renamed `->subTotal()`.
* The `->couponTotal()` method has been renamed `->discountTotal()`.
* Totals are now only calculated on carts. Once a cart has been converted to an order, the totals cannot be recalculated.

#### Payments
The `gatewayData()` method has been removed. Payment Gateways are now responsible for adding payment details to the order data themselves.

## Finally...
Once you're happy that everything has been migrated across successfully, there's a couple of post-migration cleanup steps you may want to take:

* Delete the `config/simple-commerce.php` file
* Delete the `content/simple-commerce` directory
* If you were storing customers and orders as entries, you can delete the "Customers" and "Orders" collections.
* If you were storing customers and orders in the database, you can drop the `customers` and `orders` tables.
	* You can also remove the models from the `runway.php` config. If you're not using Runway elsewhere, you can also uninstall Runway:
	```
	composer remove statamic-rad-pack/runway
	```

## The End
I know the "Simple Commerce -> Cargo" migration has been a bit of a slog, so thank you for sticking with it! 🙌

Cargo has been months of hard work, during evenings and weekends. It would make my ~~day~~week if you would [star the repository on GitHub](https://github.com/duncanmcclean/statamic-cargo) 😎.