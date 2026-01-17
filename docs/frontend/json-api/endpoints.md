---
title: "JSON API: Endpoints"
---

## Current Cart
Returns the customer's cart.

@blade
<x-api-endpoint method="GET" path="/!/cargo/cart" />
@endblade

## Update the cart
Updates the customer's cart. You can post customer information, redeem a discount code, or pass any other order fields here.

[//]: # (Note: Parameters are duplicated in frontend/tags/cart.md)
@blade
<x-api-endpoint
	method="POST"
	path="/!/cargo/cart"
	:parameters="[
		[
			'key' => 'customer', 
			'type' => 'array', 
			'required' => false,
			'description' => 'Customer information to persist on the cart.',
			'parameters' => [
				['key' => 'name', 'type' => 'string'],
				['key' => 'first_name', 'type' => 'string'],
				['key' => 'last_name', 'type' => 'string'],
				['key' => 'email', 'type' => 'string'],
				['key' => '*', 'description' => 'Any other fields defined in your [user blueprint](https://statamic.dev/users#user-fields).'],
			],
		],
		['key' => 'discount_code', 'type' => 'string'],
		['key' => 'shipping_method', 'type' => 'string', 'description' => 'Required when `shipping_option` is provided.'],
		['key' => 'shipping_option', 'type' => 'string', 'description' => 'Required when `shipping_method` is provided.'],
		[
			'key' => 'shipping_address',
			'type' => 'array',
			'description' => 'Shipping address for the order.',
			'parameters' => [
				['key' => 'name', 'type' => 'string'],
				['key' => 'line_1', 'type' => 'string'],
				['key' => 'line_2', 'type' => 'string'],
				['key' => 'city', 'type' => 'string'],
				['key' => 'postcode', 'type' => 'string'],
				['key' => 'country', 'type' => 'string', 'description' => 'Must be in [ISO3](https://www.iso.org/obp/ui#iso:pub:PUB500001:en) format.'],
				['key' => 'state', 'type' => 'string', 'description' => 'Must match one of the states in [Cargo\'s `states.json` file](https://github.com/duncanmcclean/statamic-cargo/blob/main/resources/json/states.json).'],
			],
		],
		[
			'key' => 'billing_address',
			'type' => 'array',
			'description' => 'Billing address for the order.',
			'parameters' => [
				['key' => 'name', 'type' => 'string'],
				['key' => 'line_1', 'type' => 'string'],
				['key' => 'line_2', 'type' => 'string'],
				['key' => 'city', 'type' => 'string'],
				['key' => 'postcode', 'type' => 'string'],
				['key' => 'country', 'type' => 'string', 'description' => 'Must be in [ISO3](https://www.iso.org/obp/ui#iso:pub:PUB500001:en) format.'],
				['key' => 'state', 'type' => 'string', 'description' => 'Must match one of the states in [Cargo\'s `states.json` file](https://github.com/duncanmcclean/statamic-cargo/blob/main/resources/json/states.json).'],
			],
		],
		['key' => '*', 'description' => 'Any other fields defined in your [order blueprint](/docs/orders#blueprint).'],
	]"
/>
@endblade

## Delete the cart
Deletes the customer's cart.

@blade
<x-api-endpoint method="DELETE" path="/!/cargo/cart" />
@endblade

## Add a line item
Adds a line item to the customer's cart.

[//]: # (Note: Parameters are duplicated in frontend/tags/cart.md)
@blade
<x-api-endpoint
	method="POST"
	path="/!/cargo/cart/line-items"
	:parameters="[
		['key' => 'product', 'type' => 'string', 'required' => true],
		['key' => 'variant', 'type' => 'string', 'description' => 'Required when adding a variant product.'],
		['key' => 'quantity', 'type' => 'integer', 'description' => 'Defaults to `1`'],
		[
			'key' => 'customer', 
			'type' => 'array', 
			'required' => false,
			'description' => 'Customer information to persist on the cart.',
			'parameters' => [
				['key' => 'name', 'type' => 'string'],
				['key' => 'first_name', 'type' => 'string'],
				['key' => 'last_name', 'type' => 'string'],
				['key' => 'email', 'type' => 'string'],
				['key' => '*', 'description' => 'Any other fields defined in your [user blueprint](https://statamic.dev/users#user-fields).'],
			],
		],
		['key' => '*', 'description' => 'Any other data you\'d like to persist on the line item.'],
	]"
/>
@endblade

## Update a line item
Updates a line item on the customer's cart. The `{lineItem}` should be the ID of the line item you wish to update.

[//]: # (Note: Parameters are duplicated in frontend/tags/cart.md)
@blade
<x-api-endpoint
	method="PATCH"
	path="/!/cargo/cart/line-items/{lineItem}"
	:parameters="[
		['key' => 'variant', 'type' => 'string', 'description' => 'Required when the product is a variant product.'],
		['key' => 'quantity', 'type' => 'integer'],
		[
			'key' => 'customer', 
			'type' => 'array', 
			'required' => false,
			'description' => 'Customer information to persist on the cart.',
			'parameters' => [
				['key' => 'name', 'type' => 'string'],
				['key' => 'first_name', 'type' => 'string'],
				['key' => 'last_name', 'type' => 'string'],
				['key' => 'email', 'type' => 'string'],
				['key' => '*', 'description' => 'Any other fields defined in your [user blueprint](https://statamic.dev/users#user-fields).'],
			],
		],
		['key' => '*', 'description' => 'Any other data you\'d like to persist on the line item.'],
	]"
/>
@endblade

## Remove a line item
Removes a line item from the customer's cart. The `{lineItem}` should be the ID of the line item you wish to remove.

@blade
<x-api-endpoint method="DELETE" path="/!/cargo/cart/line-items/{lineItem}" />
@endblade

## Available Shipping Methods
Returns the available shipping options for the customer's cart.

This endpoint will return a `422` status code when no shipping address is set on the cart.

@blade
<x-api-endpoint method="GET" path="/!/cargo/cart/shipping" />
@endblade

## Available Payment Gateways
Returns the available payment gateways for the customer's cart, including the array returned by the payment gateway's `setup` method.

:::tip note
Payment Gateways will be returned *even* when the cart total is £0. In this case, no `setup` data will be returned.
:::

@blade
<x-api-endpoint method="GET" path="/!/cargo/cart/payment-gateways" />
@endblade

## Checkout: £0 order
When the cart total is equals to £0, you may use this endpoint to create an order without payment.

When successful, this endpoint will return a redirect response to the checkout confirmation page.

When the order requires payment, this endpoint will report a `404` status code.

@blade
<x-api-endpoint
	:methods="['GET', 'POST']"
	path="/!/cargo/cart/checkout"
	:parameters="[
		['key' => 'discount_code', 'type' => 'string'],
	]"
/>
@endblade

## Checkout: Paid Order
When the order requires payment, you may use this endpoint to create the order. The `{gateway}` should be the handle of the payment gateway you wish to check out using.

When successful, this endpoint will return a redirect response to the checkout confirmation page.

When the order does not require payment, this endpoint will return a `404` status code.

@blade
<x-api-endpoint
	:methods="['GET', 'POST']"
	path="/!/cargo/cart/payments/{gateway}/checkout"
	:parameters="[
		['key' => 'discount_code', 'type' => 'string'],
	]"
/>
@endblade

## States
Returns an array of states for a given country.

@blade
<x-api-endpoint
	method="GET"
	path="/!/cargo/states"
	:parameters="[
		['key' => 'country', 'type' => 'string', 'required' => true, 'description' => 'Must be in [ISO3](https://www.iso.org/obp/ui#iso:pub:PUB500001:en) format.'],
	]"
/>
@endblade