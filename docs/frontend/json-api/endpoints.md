---
title: "JSON API: Endpoints"
---
## Current Cart
`GET` `/!/cargo/cart`

Returns the customer's cart.

When the customer doesn't have a cart, this endpoint will report a 404 status code.

## Update the cart
`POST` `/!/cargo/cart`

Updates the customer's cart. You can post customer information, redeem a discount code, or pass any other order fields here.

**Parameters:**
* `customer` (array)
	* `name`
	* `first_name`
	* `last_name`
	* `email`
	* Any additional data you want to persist on the customer.
* `discount_code`
* `shipping_method` (requires `shipping_option`)
* `shipping_option` (required `shipping_method`)
* Addresses
	* Shipping: `shipping_line_1`, `shipping_line_2`, `shipping_city`, `shipping_postcode`, `shipping_country`, `shipping_state`
	* Billing: `billing_line_1`, `billing_line_2`, `billing_city`, `billing_postcode`, `billing_country`, `billing_state`
* Any additional fields from your order blueprint

When the customer doesn't have a cart, this endpoint will report a 404 status code.

## Delete the cart
`DELETE` `/!/cargo/cart`

Deletes the customer's cart.

When the customer doesn't have a cart, this endpoint will report a 404 status code.

## Add a line item
`POST` `/!/cargo/cart/line-items`

Adds a line item to the customer's cart.

**Parameters:**
* `product` (required)
* `variant` (required, for variant products)
* `quantity` (defaults to `1`)
* Any additional data you want to persist on the line item.
* `customer` (array)
	* `name`
	* `first_name`
	* `last_name`
	* `email`
	* Any additional data you want to persist on the customer.

## Update a line item
`PATCH` `/!/cargo/cart/line-items/{id}`

Updates a line item on the customer's cart. The `{id}` should be the ID of the line item you wish to update.

**Parameters:**
* `variant` (when it's a variant product)
* `quantity`
* Any additional data you want to persist on the line item.
* `customer` (array)
	* `name`
	* `first_name`
	* `last_name`
	* `email`
	* Any additional data you want to persist on the customer.

When a line item with the provided ID doesn't exist, this endpoint will return a 404 status code.

## Remove a line item
`DELETE` `/!/cargo/cart/line-items/{id}`

Removes a line item from the customer's cart. The `{id}` should be the ID of the line item you wish to remove.

When a line item with the provided ID doesn't exist, this endpoint will return a 404 status code.

## Available Shipping Options
`GET` `/!/cargo/cart/shipping`

Returns the available shipping options for the customer's cart.

When there's no address on the cart, this endpoint will return a 422 status code.
When the customer doesn't have a cart, this endpoint will report a 404 status code. 


## Available Payment Gateways
`GET` `/!/cargo/cart/payment-gateways`

Returns the available payment gateways for the customer's cart, including the array returned by the payment gateway's `setup` method.

:::tip note
Payment Gateways will be returned *even* when the cart total is £0. In this case, no `setup` data will be returned.
:::

When the customer doesn't have a cart, this endpoint will report a 404 status code. 

## Checkout: £0 order
`GET / POST` `/!/cargo/cart/checkout`

When the cart total is equals to £0, you may use this endpoint to create an order without payment.

**Parameters:**
* `discount_code`

When successful, this endpoint will return a redirect response to the checkout confirmation page.

When the order requires payment, this endpoint will report a 404 status code.

## Checkout: Paid Order
`GET / POST` `/!/cargo/payments/{gateway}/checkout`

When the order requires payment, you may use this endpoint to create the order. The `{gateway}` should be the handle of the payment gateway you wish to checkout using.

**Parameters:**
* `discount_code`

When successful, this endpoint will return a redirect response to the checkout confirmation page.

When the order does not require payment, this endpoint will return a 404 status code.

## States
`GET` `/!/cargo/states`

Returns an array of states for a given country.

**Parameters:**
* `country` (required - in [ISO3](https://www.iso.org/obp/ui#iso:pub:PUB500001:en))