---
title: "JSON API: Endpoints"
---

## Cart

[//]: # (todo: order keys alphabetically&#41;)

@blade
@include('markdown.api-endpoint', [
	'method' => 'GET',
	'path' => '/!/cargo/cart',
	'description' => "Returns the customer's cart.",
])

@include('markdown.api-endpoint', [
	'method' => 'POST',
	'path' => '/!/cargo/cart',
	'description' => "Updates the customer's cart. You can post customer information, redeem a discount code, or pass any other order fields here.",
	'parameters' => [
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
		['key' => 'shipping_line_1', 'type' => 'string'],
		['key' => 'shipping_line_2', 'type' => 'string'],
		['key' => 'shipping_city', 'type' => 'string'],
		['key' => 'shipping_postcode', 'type' => 'string'],
		['key' => 'shipping_country', 'type' => 'string', 'description' => 'Must be in [ISO3](https://www.iso.org/obp/ui#iso:pub:PUB500001:en) format.'],
		['key' => 'shipping_state', 'type' => 'string', 'description' => 'Must match one of the states in [Cargo\'s `states.json` file](https://github.com/duncanmcclean/statamic-cargo/blob/main/resources/json/states.json).'],
		['key' => 'billing_line_1', 'type' => 'string'],
		['key' => 'billing_line_2', 'type' => 'string'],
		['key' => 'billing_city', 'type' => 'string'],
		['key' => 'billing_postcode', 'type' => 'string'],
		['key' => 'billing_country', 'type' => 'string', 'description' => 'Must be in [ISO3](https://www.iso.org/obp/ui#iso:pub:PUB500001:en) format.'],
		['key' => 'billing_state', 'type' => 'string', 'description' => 'Must match one of the states in [Cargo\'s `states.json` file](https://github.com/duncanmcclean/statamic-cargo/blob/main/resources/json/states.json).'],
		['key' => '*', 'description' => 'Any other fields defined in your [order blueprint](/docs/orders#blueprint).'],
	],
])

@include('markdown.api-endpoint', [
	'method' => 'DELETE',
	'path' => '/!/cargo/cart',
	'description' => "Deletes the customer's cart.",
])

@include('markdown.api-endpoint', [
	'method' => 'POST',
	'path' => '/!/cargo/cart/line-items',
	'description' => "Adds a line item to the customer's cart.",
	'parameters' => [
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
	],
])

@include('markdown.api-endpoint', [
	'method' => 'PATCH',
	'path' => '/!/cargo/cart/line-items/{lineItem}',
	'description' => "Updates a line item on the customer's cart. The `{lineItem}` should be the ID of the line item you wish to update.",
	'parameters' => [
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
	],
])

@include('markdown.api-endpoint', [
	'method' => 'DELETE',
	'path' => '/!/cargo/cart/line-items/{lineItem}',
	'description' => "Removes a line item from the customer's cart. The `{lineItem}` should be the ID of the line item you wish to remove.",
])

@include('markdown.api-endpoint', [
	'method' => 'GET',
	'path' => '/!/cargo/cart/shipping',
	'description' => "Returns the available shipping options for the customer's cart. <br><br> When there's no address on the cart, this endpoint will return a 422 status code. When the customer doesn't have a cart, this endpoint will return a 404 status code.",
])

@include('markdown.api-endpoint', [
	'method' => 'GET',
	'path' => '/!/cargo/cart/payment-gateways',
	'description' => "Returns the available payment gateways for the customer's cart, including the array returned by the payment gateway's `setup` method. <br><br> Payment Gateways will be returned *even* when the cart total is £0. In this case, no `setup` data will be returned. <br><br> When the customer doesn't have a cart, this endpoint will return a 404 status code.",
])
@endblade

## Checkout

@blade
@include('markdown.api-endpoint', [
	'method' => 'GET & POST',
	'path' => '/!/cargo/cart/checkout',
	'description' => "When the cart total is equals to £0, you may use this endpoint to create an order without payment. <br><br> When successful, this endpoint will return a redirect response to the checkout confirmation page. <br><br> When the order requires payment, this endpoint will return a 404 status code.",
	'parameters' => [
		['key' => 'discount_code', 'type' => 'string'],
	],
])

@include('markdown.api-endpoint', [
	'method' => 'GET & POST',
	'path' => '/!/cargo/payments/{gateway}/checkout',
	'description' => "When the order requires payment, you may use this endpoint to create the order. The `{gateway}` should be the handle of the payment gateway you wish to check out using. <br><br> When successful, this endpoint will return a redirect response to the checkout confirmation page. <br><br> When the order does not require payment, this endpoint will return a 404 status code.",
	'parameters' => [
		['key' => 'discount_code', 'type' => 'string'],
	],
])
@endblade

## States

@blade
@include('markdown.api-endpoint', [
	'method' => 'GET',
	'path' => '/!/cargo/states',
	'description' => "Returns an array of states for a given country.",
	'parameters' => [
		['key' => 'country', 'type' => 'string', 'required' => true, 'description' => 'Must be in [ISO3](https://www.iso.org/obp/ui#iso:pub:PUB500001:en) format.'],
	],
])
@endblade