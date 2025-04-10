---
title: Upgrade Guide
---

:::tip Note
This upgrade guide is very much still a work in progress. There's bound to be things missing. If spot something that hasn't been covered here, please [open an issue](https://github.com/duncanmcclean/cargo/issues/new).
:::

## Overview
Cargo is the natural evolution of [Simple Commerce](https://github.com/duncanmcclean/cargo). 

What started out as a hobby project for me to learn the internals of Statamic turned into *the* way to build e-commerce sites with Statamic.

TODO

## Updating
To upgrade, uninstall Simple Commerce and install Cargo:

```
composer remove duncanmcclean/simple-commerce
composer require duncanmcclean/cargo
```

Once Composer has finished doing its thing, run Cargo's install command. It'll publish various stubs, setup your products collection, and more.

```
php please cargo:install
```

Please read through this guide thoroughly. You **will** need to make code changes as part of this upgrade.

## Mental shifts
* Carts and orders are no longer entries, they now live in their own [Stache stores](https://statamic.dev/stache#stores).
	* Carts and orders are separate. Gone are the days of order numbers being messed up by abandoned carts. 
	* Customers are now always Statamic users.
	* *Most* (but not all) of the features in Simple Commerce have been ported over to Cargo. If you spot something missing, please [open an issue](https://github.com/duncanmcclean/cargo/issues/new). 


* Carts and Orders are now separate concepts
* Carts and Orders are no longer stored as entries, but rather live in their own stache stores to give Cargo more control over how they work.
* Customers are now statamic users


## Breaking Changes





TODO



* Everything has been renamed to Cargo. Namespaces, routes, everything!
* Orders are now stored in the Stache
* Customers are now Statamic users - guest customers are just stored in the order data as arrays, fields are limited.
* Variable changes:
  * `coupon_total` is now `discount_total`
  * `gateway` is now `payment_gateway`
* The order status log has been removed.
* The `currency` modifier has been removed.
* `{{ items }}` are now `{{ line_items }}`
* The PHP API for orders & line items have changed.
* The `{{ sc:customer:update }}` tag has been removed. You should use Statamic's built-in tags for the same functionality.
* Previously, tags like `{{ sc:cart:rawTaxTotalSplit }}` existed to get the "raw" values of totals (eg. without currency formatting). These have now been removed, in favour of the raw modifier: `{{ sc:cart:taxTotalSplit | raw }}`
* Digital Products: License Keys functionality has been removed - SC primarilly supports digital download products like ebooks, etc. If you need license key functionality, you should build this yourself.
* The built-in payment gateways are now at `SC\Payments\Gateways`
* The contracts for payment gateways have moved to `SC\Contracts\Payments`
* The contracts for shipping stuff has moved to `SC\Contracts\Shipping`
* Line Items no longer have "metadata". Additional data can sit right beside the other keys.
* The PHP API for interacting with Line Items has changed. You should review the new documentation for how this works.
* Events have changed. Please see the table below for mappings for old events to equievlent events in the new version.
* The `{{ sc:customer }}` tag has been removed in favour of Statamic's user tags.
* Webhook URLs have changed. Instead of `/!/simple-commerce/gateways/{gateway}/webhook`, it's now `/!/simple-commerce/payments/{gateway}/webhook`.
* Stripe Webhooks now require verification using the webhook secret.
* Some of the action endpoint URLs have changed, in addition to different responses. You should check the documentation.
* The `quantity` input on the add to cart form is no lonnger required. It'll default to 1
* Form Requests have been removed. They may end up being added back if they're needed gain. You can add validation rules to your order blueprint and they'll be used on the frontend.
* The `{{ sc:cart:count }}` tag has been removed. You should now use `{{ sc:cart:lineItems | count }}` instead or `{{ sc:cart:is_empty }}`
* Intead of `{{ sc:cart:items }}`, you should do `{{ sc:cart:line_items }}`.
* Inside `{{ sc:cart }}`, instead of looping through `{{ items }}`, you should loop through `{{ line_items }}`
* `{{ sc:cart:total }}` has been removed in favour of `{{ sc:cart:grand_total }}`
* `{{ sc:cart:taxTotalSplit }}` and `{{ sc:cart:rawTaxTotalSplit }}` are now `{{ sc:cart:tax_totals }}` and `{{ sc:cart:tax_totals | raw }}`
* `{{ sc:cart:addItem }}` is now `{{ sc:cart:add }}`
* `{{ sc:cart:updateItem }}` is now `{{ sc:cart:update_line_item }}`
* `{{ sc:cart:removeItem }}` is now `{{ sc:cart:remove }}`
* The `{{ sc:coupon }}` tag has been removed.
  * You can now redeem coupons via the `{{ sc:cart }}` and `{{ sc:checkout }}` tags.
  * You can get the current coupon via `{{ sc:cart:coupon }}`.
* Simple Commerce no longer has its own error tags. You can use Statamic's error tags instead.
* `{{ sc:cart:has }}` is now `{{ sc:cart:exists }}`
* The PayPal gateway has been removed, in favour of using PayPal via Stripe or Mollie.
* The `{{ sc }}` tag has been removed, in favour of *actual* tags, like `{{ cart }}` or `{{ checkout }}`
* Shipping Methods are now registered differently. 
* The contract for shipping methods has changed (mostly just changes to method names)
* Discounts are now calculated on a per-line item/per product basis. The "Products" field thingy will now only apply the coupon to that product, rather than the whole cart like it did previously. 
* Tax Totals/`tax_totals` is now `tax_breakdown`... see docs
* Currency configuration now happens in individual site configs
* The `PreCheckout` event has been removed.
* The `PostCheckout` event has been removed, in favour of `OrderCreated`
* (document any other removed events)
* The `{{ sc:gateways }}` tag has been removed in favour of the `{{ payment_gateways }}` tag.
* Stripe: the autorization and capture steps have been separarted. authorization happens when the customer submits the form, capture (actually taking the payment) happens when the webhook has been received on the backend.
* {{ checkout }} tag has been removed. Each gateway has its own checkout endpoint now, and should submit to it.
  * For $0 orders, they can submit to a "central" checkout endpoint (as per the docs), which'll handle them.
* `{{ free }}` is now `{{ is_free }}`
* Mollie: `key` setting is now called `api_key`
* states are now referred to as regions
* The `gateway_config` variable has been removed inside the `{{ payment_gateways }}` loop. The gateway's `setup` method is now responsible for passing down anything that should be in the payment gateway data.
    * You shouldn't pass any sensitive API keys down, as these may be visible in network requests.
* The `{{ payment_gateways }}` tag will *always* return payment gateways, even when the cart total is $0. This allows you to loop over gateways when rendering pages, which you can show conditionally later on.


notifications:
We should make sure to document the fact that notifications have changed. If you have custom notifications, or notifications which extend SC's built-in notifications, they'll work differently.