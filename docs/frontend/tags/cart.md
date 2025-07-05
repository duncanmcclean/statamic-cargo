---
title: Cart Tag
---

You can use the `{{ cart }}` tag to do just about anything related the customer's cart, including getting cart totals and looping through line items.

In this example, you can use the `{{ cart }}` tag as a tag pair, meaning you can access any of the cart's data inside:

::tabs
::tab antlers
```antlers
{{ cart }}  
    <h2>Your cart comes to {{ sub_total }}!</h2>  
  
    <ul>  
        {{ line_items }}  
            <li><a href="{{ product:url }}">{{ product:title }}</a> ({{ total }})</li>  
        {{ /line_items }}  
    </ul>  
  
    <a href="/checkout">Checkout</a>  
{{ /cart }}
```
::tab blade
```blade
<s:cart>  
    <h2>Your cart comes to {{ $sub_total }}!</h2>  
  
    <ul>  
        @foreach($line_items as $line_item)  
            <li><a href="{{ $line_item['product']['url'] }}">{{ $line_item['product']['title'] }}</a> ({{ $line_item['total'] }})</li>  
        @endforeach  
    </ul>  
  
    <a href="/checkout">Checkout</a>  
</s:cart>
``` 
::

You can also use the `{{ cart }}` tag to access a single field at a time, like this:

::tabs
::tab antlers
```antlers
{{ cart:sub_total }}
``` 
::tab blade
```blade
<s:cart:sub_total>
``` 
::

These snippets might come in handy... they're all pretty self-explanatory:

* `{{ cart:grand_total }}`
* `{{ cart:sub_total }}`
* `{{ cart:discount_total }}`
* `{{ cart:tax_total }}`
* `{{ cart:shipping_total }}`
* `{{ cart:is_free }}`
* `{{ cart:has_physical_products }}`
* `{{ cart:has_digital_products }}`
* `{{ cart:customer:name }}`
* `{{ cart:customer:email }}`
* `{{ cart:coupon:code }}`
* `{{ cart:shipping_option:name }}`
* `{{ {cart:line_items} | count }}`

You can use the [`{{ dump }}`](https://statamic.dev/tags/dump) tag to get a comprehensive list of the variables available to you.

## Check if a cart exists
You can use the `{{ cart:exists }}` tag to determine if the customer has an active cart:

::tabs
::tab antlers
```antlers
{{ if {cart:exists} }}  
    The user has an active cart! 🎉  
{{ else }}  
    The user does not have an active cart. 😢  
{{ /if }}
``` 
::tab blade
```blade
@if(Statamic::tag('cart:exists'))  
    The user has an active cart! 🎉  
@else  
    The user does not have an active cart. 😢  
@endif
``` 
::

## Check if the cart is empty
You can use the `{{ cart:is_empty }}` tag to determine if the customer's cart is empty (eg. has no line items).

::tabs
::tab antlers
```antlers
{{ if {cart:is_empty} }}  
    Oh no! The cart is empty. 😢  
{{ else }}  
    Sweet! The cart has items in it. 🎉  
{{ /if }}
``` 
::tab blade
```blade
@if(Statamic::tag('cart:is_empty'))  
    Oh no! The cart is empty. 😢  
@else  
    The user does not have an active cart. 😢  
@endif
``` 
::

## Check if a product has been added to the cart
You can use the `{{ cart:added }}` tag to determine if a product is in the customer's cart:

::tabs
::tab antlers
```antlers
{{ if {cart:added product="product-id"} }}  
    Yay! They already have it in their cart. 🎉  
{{ else }}  
    Oh no! They don't have it in their cart. 😢  
{{ /if }}
``` 
::tab blade
```blade
@if(Statamic::tag('cart:added')->param('product', 'product-id'))  
    Yay! They already have it in their cart. 🎉  
@else  
    Oh no! They don't have it in their cart. 😢  
@endif
``` 
::

This tag also accepts a `variant` parameter for checking if a particular variant has been added to the customer's cart.

## Add to the cart
This tag allows you to add products to the customer's cart:

::tabs
::tab antlers
```antlers
{{ cart:add }}  
    <input type="number" name="quantity" min="1" required>  
    <button>Add to cart</button>  
{{ /cart:add }}
``` 
::tab blade
```blade
<s:cart:add>  
    <input type="number" name="quantity" min="1" required>  
    <button>Add to cart</button>  
</s:cart:add>
``` 
::

When possible, Cargo will automatically inject a hidden `product` input when it finds the product ID in the page's [context](https://statamic.dev/extending/tags#context). However, you can provide it manually if needed:

```antlers
<input type="hidden" name="product" value="{{ page:id }}">
``` 

### Fields
This form supports the following fields:
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

## Updating a line item
This tag allows you to update a line item in the customer's cart. You can either pass the `id` of the line item, or the ID of the product.

::tabs
::tab antlers
```antlers
{{ cart:update_line_item :product="page:id" }}  
    <input type="number" name="quantity" min="1" value="{{ quantity }}" required>  
    <button>Update</button>  
{{ /cart:update_line_item }}
```
::tab blade
```blade
<s:cart:update_line_item product="$page->id">  
    <input type="number" name="quantity" min="1" value="{{ $quantity }}" required>  
    <button>Update</button>  
</s:cart:update_line_item>
```
::

Inside the `{{ cart:update_line_item }}` tag, you have access to the line item's data, allowing you to pre-fill values.

### Fields
This form supports the following fields:
* `variant` (when it's a variant product)
* `quantity` 
* Any additional data you want to persist on the line item.
* `customer` (array)
	* `name`
	* `first_name`
	* `last_name`
	* `email`
	* Any additional data you want to persist on the customer.

## Removing a line item
This tag allows you to remove a line item from the customer's cart. You can either pass the `id` of the line item, or the ID of the product.

::tabs
::tab antlers
```antlers
{{ cart:remove :product="page:id" }}  
    <button>Remove</button>  
{{ /cart:remove }}
```
::tab blade
```blade
<s:cart:remove product="$page->id">  
    <button>Remove</button>  
</s:cart:remove>
```
::

Inside the `{{ cart:remove }}` tag, you have access to the line item's data, allowing you to pre-fill values.

## Updating the cart
This tag allows you to update the customer's cart. Allowing you to update everything from customer details, to shipping options, and apply discounts.

::tabs
::tab antlers
```antlers
{{ cart:update }}  
    <input type="text" name="customer[name]" value="{{ customer:name }}" required>  
    <input type="email" name="customer[email]" value="{{ customer:email }}" required>  

	<input type="text" name="shipping_line_1" value="{{ shipping_line_1 }}">
	<input type="text" name="shipping_line_2" value="{{ shipping_line_2 }}">
	<input type="text" name="shipping_city" value="{{ shipping_city }}">
	<input type="text" name="shipping_postcode" value="{{ shipping_postcode }}">
	<input type="text" name="shipping_country" value="{{ shipping_country }}">
	<input type="text" name="shipping_state" value="{{ shipping_state }}">
    
    <button>Update</button>  
{{ /cart:update }}
```
::tab blade
```blade
<s:cart:update>  
    <input type="text" name="customer[name]" value="{{ $customer['name'] }}" required>  
    <input type="email" name="customer[email]" value="{{ $customer['email'] }}" required>  

	<input type="text" name="shipping_line_1" value="{{ $shipping_line_1 }}">
	<input type="text" name="shipping_line_2" value="{{ $shipping_line_2 }}">
	<input type="text" name="shipping_city" value="{{ $shipping_city }}">
	<input type="text" name="shipping_postcode" value="{{ $shipping_postcode }}">
	<input type="text" name="shipping_country" value="{{ $shipping_country }}">
	<input type="text" name="shipping_state" value="{{ $shipping_state }}">
    
    <button>Update</button>  
</s:cart:update>
```
::

Inside the `{{ cart:update }}` tag, you have access to the cart's data, allowing you to pre-fill values.

### Fields
This form supports the following fields:
* `customer` (array)
	* `name`
	* `first_name`
	* `last_name`
	* `email`
	* Any additional data you want to persist on the customer.
* `coupon`
* `shipping_method` (requires `shipping_option`)
* `shipping_option` (required `shipping_method`)
* Addresses
	* Shipping: `shipping_line_1`, `shipping_line_2`, `shipping_city`, `shipping_postcode`, `shipping_country`, `shipping_state`
	* Billing: `billing_line_1`, `billing_line_2`, `billing_city`, `billing_postcode`, `billing_country`, `billing_state`
* Any additional fields from your order blueprint

## Deleting the cart
This tag allows you to delete the customer's cart. 

::tabs
::tab antlers
```antlers
{{ cart:delete }}  
    <button>Delete</button>  
{{ /cart:delete }}
```
::tab blade
```blade
<s:cart:delete>  
    <button>Delete</button>  
</s:cart:delete>
```
::

Inside the `{{ cart:delete }}` tag, you have access to the cart's data.