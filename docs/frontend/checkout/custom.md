---
title: Custom checkout flow
description: "Learn how to build a custom checkout flow using Cargo's Antlers tags and JSON API endpoints."
---
We recommend splitting your checkout flow into multiple steps to allow for totals to be recalculated when the customer's cart is updated.

Most stores split their checkout flow into the following steps:
* Customer information
* Shipping and/or billing addresses
* Shipping method
* Payment
* Confirmation

In this guide, we're going to cover how to handle each of these steps. 

:::tip info
It's hard to build a good checkout flow. If you'd rather not build everything from scratch, we recommend publishing the [pre-built checkout flow](#pre-built-checkout-flow) and customising it to your needs.
:::

## Setup
To keep things simple, we're going to create separate routes and views for each step in the checkout process:

```php
// routes/web.php

Route::redirect('checkout', 'checkout/customer');
Route::statamic('checkout/customer');
Route::statamic('checkout/addresses');
Route::statamic('checkout/shipping');
Route::statamic('checkout/payment')->name('checkout.payment');
Route::statamic('checkout/confirmation')->name('checkout.confirmation');
```

::tabs
::tab antlers
```files
resources
  views
    checkout
      customer.antlers.html
      addresses.antlers.html
      shipping.antlers.html
      confirmation.antlers.html
```
::tab blade
```files
resources
  views
    checkout
      customer.blade.php
      addresses.blade.php
      shipping.blade.php
      confirmation.blade.php
```
::

We're also going to create a `summary` view, which we'll use to display the cart's line items and totals.

::tabs
::tab antlers
```antlers
{{# resources/views/checkout/summary.antlers.html #}}

<table>
	<tbody>
		{{ line_items }}
			<tr>
				<td>{{ quantity }}x {{ product:title }}</td>
				<td align="right">{{ sub_total }}</td>
			</tr>
		{{ /line_items }}
		<tr>
			<td>Subtotal:</td>
			<td align="right">{{ sub_total }}</td>
		</tr>
		{{ if discounts }}
			{{ discounts }}
				<tr>
					<td>Discount ({{ discount_code ?? name }}):</td>
					<td align="right">-{{ amount }}</td>
				</tr>
			{{ /discounts }}
		{{ /if }}
		{{ if coupon }}
			<tr>
				<td>Discount:</td>
				<td align="right">{{ discount_total }}</td>
			</tr>
		{{ /if }}
		{{ if has_physical_products }}
			<tr>
				<td>Shipping:</td>
				<td align="right">{{ shipping_option:price }}</td>
			</tr>
		{{ /if }}
		{{ if !config:statamic:cargo:taxes:price_includes_tax }}
			<tr>
				<td>Taxes:</td>
				<td align="right">{{ tax_total }}</td>
			</tr>
		{{ /if }}
		<tr style="font-weight: bold">
			<td>Grand Total:</td>
			<td align="right">{{ grand_total }}</td>
		</tr>
	</tbody>
</table>
```
::tab blade
```blade
{{# resources/views/checkout/summary.blade.php #}}

<table>  
    <tbody>  
        @foreach($line_items as $lineItem)  
            <tr>  
                <td>{{ $lineItem->quantity }}x {{ $product->title }}</td>  
                <td align="right">{{ $lineItem->sub_total }}</td>  
            </tr>  
        @endforeach  
        <tr>  
            <td>Subtotal:</td>  
            <td align="right">{{ $sub_total }}</td>  
        </tr>  
        @if($discounts)  
            @foreach($discounts as $discount)  
                <tr>  
                    <td>Discount ({{ $discount->discount_code ?? $discount->name }}):</td>  
                    <td align="right">-{{ $discount->amount }}</td>  
                </tr>  
            @endforeach  
        @endif        
        @if($has_physical_products)  
            <tr>  
                <td>Shipping:</td>  
                <td align="right">{{ $shipping_option->price }}</td>  
            </tr>  
        @endif  
        @if(config('statamic.cargo.taxes.price_includes_tax'))  
            <tr>  
                <td>Taxes:</td>  
                <td align="right">{{ $tax_total }}</td>  
            </tr>  
        @endif  
        <tr style="font-weight: bold">  
            <td>Grand Total:</td>  
            <td align="right">{{ $grand_total }}</td>  
        </tr>  
    </tbody>  
</table>
```
::

Finally, we need to update the route names in the `cargo.php` file to match the routes we just created. Cargo uses these to handle redirects.

```php
// config/cargo.php

'routes' => [  
    'checkout' => 'checkout',  
    'checkout_confirmation' => 'checkout.confirmation',  
],
```

## Customer
Usually, when a customer is logged in, it means you already have their details, therefore there isn't much point in collecting their details again, so they can be redirected onto the next step.

However, for logged out users, you probably want to give them the option to login/register for an account **or** continue as a guest customer.

You can use Statamic's [`{{ user:login_form }}`](https://statamic.dev/tags/user-login_form) and [`{{ user:register_form }}`](https://statamic.dev/tags/user-register_form) tags to handle authentication, then use Cargo's `{{ cart:update }}` tag to create [guest customers](/docs/customers).

You should specify the `redirect` parameter on all form tags, so whichever action the customer takes, they end up on the next step.

::tabs
::tab antlers
```antlers
{{ if logged_in }}
    {{ redirect to="/checkout/addresses" }}
{{ /if }}

<h1>Checkout</h1>

<h2>Customer</h2>

<h3>Login to your account</h3>

{{ user:login_form redirect="/checkout/addresses" }}
	<div>
		<input type="email" name="email" placeholder="Email" required>
		<input type="password" name="password" placeholder="Password" required>
	</div>

	<button>Login</button>
{{ /user:login_form }}
        
<h3>Checkout as a guest</h3>

{{ cart:update redirect="/checkout/addresses" }}
	<div>
		<input type="text" name="customer[name]" placeholder="Name" required>
		<input type="email" name="customer[email]" placeholder="Email" required>
	</div>

	<button>Continue</button>
{{ /cart:update }}

{{ cart }}
	{{ partial:checkout/summary }}
{{ /cart }}
```
::tab blade
```blade
@auth  
    @php(redirect('/checkout/addresses'))  
@endauth  
  
<h1>Checkout</h1>  
  
<h2>Customer</h2>  
  
<h3>Login to your account</h3>  
  
<s:user:login_form redirect="/checkout/addresses">  
    <div>  
        <input type="email" name="email" placeholder="Email" required>  
        <input type="password" name="password" placeholder="Password" required>  
    </div>  
  
    <button>Login</button>  
</s:user:login_form>  
  
<h3>Checkout as a guest</h3>  
  
<s:cart:update redirect="/checkout/addresses">  
    <div>  
        <input type="text" name="customer[name]" placeholder="Name" required>  
        <input type="email" name="customer[email]" placeholder="Email" required>  
    </div>  
  
    <button>Continue</button>  
</s:cart:update>  
  
<s:cart>  
    @include('checkout/summary')  
</s:cart>
```
::

## Addresses
Most of the time, you'll want to collect the customer's shipping and billing addresses during checkout. If you're only selling digital products, you can leave out the shipping address. 

The form fields are pretty self explanatory - just make sure the input names match the ones in this example.

You might notice that the state dropdowns in the example below are a *little* weird... this is because the options need to be pulled from the backend whenever the country dropdown is changed. This is achieved using a JavaScript event listener, seen at the bottom of the template.

::tabs
::tab antlers
```antlers
<h1 class="mb-2">Checkout</h1>

<h2>Addresses</h2>

{{ cart:update redirect="/checkout/shipping" }}
	<h3>Shipping Address</h3>

	<div>
		<select name="shipping_country" required>
			<option selected disabled>Select a country</option>
			{{ dictionary:countries emojis="false" }}
				<option value="{{ value }}">{{ label }}</option>
			{{ /dictionary:countries }}
		</select>
		<input type="text" name="shipping_line_1" placeholder="Shipping Line 1" required>
		<input type="text" name="shipping_line_2" placeholder="Shipping Line 2">
		<input type="text" name="shipping_city" placeholder="Town/City" required>
		<input type="text" name="shipping_postcode" placeholder="Postcode" required>
		<select name="shipping_state" required>
			<option selected disabled>Select a state</option>
			{{# States will be magically injected using JavaScript #}}
		</select>
	</div>

	<h3>Billing Address</h3>

	<div>
		<select name="billing_country" required>
			<option selected disabled>Select a country</option>
			{{ dictionary:countries emojis="false" }}
				<option value="{{ value }}">{{ label }}</option>
			{{ /dictionary:countries }}
		</select>
		<input type="text" name="billing_line_1" placeholder="Billing Line 1" required>
		<input type="text" name="billing_line_2" placeholder="Billing Line 2">
		<input type="text" name="billing_city" placeholder="Town/City" required>
		<input type="text" name="billing_postcode" placeholder="Postcode" required>
		<select name="billing_state" required>
			<option selected disabled>Select a state</option>
			{{# States will be magically injected using JavaScript #}}
		</select>
	</div>

	<button>Continue</button>
{{ /cart:update }}

{{ cart }}
	{{ partial:checkout/summary }}
{{ /cart }}

<script>
    // Listen to changes on the shipping_country dropdown
    document.getElementsByName('shipping_country')[0].addEventListener('change', (e) => {
        // Fetch the country's states
        fetch(`{{ route:statamic.cargo.states }}?country=${e.target.value}`)
            .then(response => response.json())
            .then((data) => {
                let stateDropdown = document.getElementsByName('shipping_state')[0];
                
                // Remove every option, apart from the first one.
                while (stateDropdown.options.length > 1) {
                    stateDropdown.remove(1);
                }

                // Add the new options
                Object.values(data).forEach((state) => stateDropdown.add(new Option(state.name, state.code)));
            });
    });

    // Listen to changes on the billing_country dropdown
    document.getElementsByName('billing_country')[0].addEventListener('change', (e) => {
        // Fetch the country's states
        fetch(`{{ route:statamic.cargo.states }}?country=${e.target.value}`)
            .then(response => response.json())
            .then((data) => {
                let stateDropdown = document.getElementsByName('billing_state')[0];
                
                // Remove every option, apart from the first one.
                while (stateDropdown.options.length > 1) {
                    stateDropdown.remove(1);
                }

                // Add the new options
                Object.values(data).forEach((state) => stateDropdown.add(new Option(state.name, state.code)));
            });
    });
</script>
```
::tab blade
```blade
<h1 class="mb-2">Checkout</h1>  
  
<h2>Addresses</h2>  
  
<s:cart:update redirect="/checkout/shipping">  
    <h3>Shipping Address</h3>  
  
    <div>  
        <select name="shipping_country" required>  
            <option selected disabled>Select a country</option>  
            <s:dictionary handle="countries" emojis="false">  
                <option value="{{ $value }}">{{ $label }}</option>  
            </s:dictionary>  
        </select>  
        <input type="text" name="shipping_line_1" placeholder="Shipping Line 1" required>  
        <input type="text" name="shipping_line_2" placeholder="Shipping Line 2">  
        <input type="text" name="shipping_city" placeholder="Town/City" required>  
        <input type="text" name="shipping_postcode" placeholder="Postcode" required>  
        <select name="shipping_state" required>  
            <option selected disabled>Select a state</option>  
            {{# States will be magically injected using JavaScript #}}  
        </select>  
    </div>  
  
    <h3>Billing Address</h3>  
  
    <div>  
        <select name="billing_country" required>  
            <option selected disabled>Select a country</option>  
            <s:dictionary handle="countries" emojis="false">  
                <option value="{{ $value }}">{{ $label }}</option>  
            </s:dictionary>  
        </select>  
        <input type="text" name="billing_line_1" placeholder="Billing Line 1" required>  
        <input type="text" name="billing_line_2" placeholder="Billing Line 2">  
        <input type="text" name="billing_city" placeholder="Town/City" required>  
        <input type="text" name="billing_postcode" placeholder="Postcode" required>  
        <select name="billing_state" required>  
            <option selected disabled>Select a state</option>  
            {{# States will be magically injected using JavaScript #}}  
        </select>  
    </div>  
  
    <button>Continue</button>  
</s:cart:update>  
  
<s:cart>  
    @include('checkout/summary')  
</s:cart>  
  
<script>  
    // Listen to changes on the shipping_country dropdown  
    document.getElementsByName('shipping_country')[0].addEventListener('change', (e) => {  
        // Fetch the country's states  
        fetch(`{{ route('statamic.cargo.states') }}?country=${e.target.value}`)  
            .then(response => response.json())  
            .then((data) => {  
                let stateDropdown = document.getElementsByName('shipping_state')[0];  
  
                // Remove every option, apart from the first one.  
                while (stateDropdown.options.length > 1) {  
                    stateDropdown.remove(1);  
                }  
  
                // Add the new options  
                Object.values(data).forEach((state) => stateDropdown.add(new Option(state.name, state.code)));  
            });  
    });  
  
    // Listen to changes on the billing_country dropdown  
    document.getElementsByName('billing_country')[0].addEventListener('change', (e) => {  
        // Fetch the country's states  
        fetch(`{{ route('statamic.cargo.states') }}?country=${e.target.value}`)  
            .then(response => response.json())  
            .then((data) => {  
                let stateDropdown = document.getElementsByName('billing_state')[0];  
  
                // Remove every option, apart from the first one.  
                while (stateDropdown.options.length > 1) {  
                    stateDropdown.remove(1);  
                }  
  
                // Add the new options  
                Object.values(data).forEach((state) => stateDropdown.add(new Option(state.name, state.code)));  
            });  
    });  
</script>
```
::

After the form has been submitted, the cart's taxes will be calculated.

## Shipping
You can use the `{{ shipping_options }}` tag to loop through the available shipping options for the cart.

Inside the loop, you have access to the following variables:
* `name` 
* `handle` 
* `price` 
* `shipping_method`

When you submit the form, Cargo will also expect you to pass a `shipping_method` input. You can see this in the below example, it gets set `onchange`.

::tabs
::tab antlers
```antlers
<h1>Checkout</h1>

<h2>Shipping</h2>
<p>Select your preferred shipping option.</p>

{{ cart:update redirect="/checkout/payment" }}
	<input type="hidden" name="shipping_method">

	{{ shipping_options }}
		<div>
			<input 
				id="{{ handle }}" 
                type="radio" 
                name="shipping_option" 
                value="{{ handle }}" 
           <!-- onchange="document.getElementsByName('shipping_method')[0].value = '{{ shipping_method }}'" -->
                required>

            <label for="{{ handle }}">{{ name }} ({{ price }})</label>
        </div>
	{{ /shipping_options }}

	<button>Continue</button>
{{ /cart:update }}

{{ cart }}
	{{ partial:checkout/summary }}
{{ /cart }}
```
::tab blade
```blade
<h1>Checkout</h1>  
  
<h2>Shipping</h2>  
<p>Select your preferred shipping option.</p>  
  
<s:cart:update redirect="/checkout/payment">  
    <input type="hidden" name="shipping_method">  
  
    @foreach($shipping_options as $shippingOption)  
        <div>  
            <input  
                id="{{ $handle }}"  
                type="radio"  
                name="shipping_option"  
                value="{{ $handle }}"  
           {{## onchange="document.getElementsByName('shipping_method')[0].value = '{{ $shipping_method }}'" ##}}
            required>  
  
            <label for="{{ $handle }}">{{ $name }} ({{ $price }})</label>  
        </div>  
    @endforeach  
  
    <button>Continue</button>  
</s:cart:update>  
  
<s:cart>  
    @include('checkout/summary')  
</s:cart>
```
::

:::tip note
The `onchange` attribute is commented out in the above example, as it causes an infinite loop with the syntax highlighter. You should uncomment it in your code.
:::

After the form has been submitted, the cart's shipping costs will be calculated.

## Payment
It's finally time for the step I know you've been waiting for.... **payments!** 🎉

In all seriousness though, this step isn't as big and scary as it sounds...

For **free carts**, all you need to do is display a simple `<form>`, that when submitted will create the order without payment.

For **paid carts**, you need to loop through the available payment gateways using the aptly named `{{ payment_gateways }}` tag and display the payment form for each.

Inside the loop, you have access to the following variables:
* `name` 
* `handle` 
* `checkout_url` 
* Anything returned by the payment gateway's `setup` method.

::tabs
::tab antlers
```antlers
<h1>Checkout</h1>

<h2>Payment</h2>

{{ if {get_error:checkout} }}
	<p>{{ get_error:checkout }}</p>
{{ /if }}

{{ if {cart:is_free} }}
	<form action="{{ route:statamic.cargo.cart.checkout }}">
		<p>No payment required. Continue to checkout.</p>

		<button>Checkout</button>
	</form>
{{ else }}
	<p>Select your preferred payment method and pay.</p>

	<div>
		{{ payment_gateways }}
			<details name="payment_gateway" {{ if first }} open {{ /if }}>
				<summary>{{ name }}</summary>
				<div>
					{{ partial src="checkout/payment-forms/{handle}" }}
				</div>
			</details>
		{{ /payment_gateways }}
	</div>
{{ /if }}
        
{{ cart }}
	{{ partial:checkout/summary }}
{{ /cart }}
``` 
::tab blade
```blade
<h1>Checkout</h1>  
  
<h2>Payment</h2>  
  
<s:get_error:fieldname>  
    <p>{{ $message }}</p>  
</s:get_error:fieldname>  
  
@if(Statamic::tag('cart:is_free'))  
    <form action="{{ route('statamic.cargo.cart.checkout') }}">  
        <p>No payment required. Continue to checkout.</p>  
  
        <button>Checkout</button>  
    </form>  
@else  
    <p>Select your preferred payment method and pay.</p>  
  
    <div>  
        @foreach(Statamic::tag('payment_gateways') as $paymentGateway)  
            <details name="payment_gateway" @if($loop->first) open @endif>  
                <summary>{{ $paymentGateway->name }}</summary>  
                <div>  
                    @include('checkout/payment-forms/' . $paymentGateway->handle)  
                </div>  
            </details>  
        @endforeach  
    </div>  
@endif  
  
<s:cart>  
    @include('checkout/summary')  
</s:cart>
```
::

Every [payment gateway](/docs/payment-gateways) should provide a "payment form" template in its documentation. 

In the above example, each payment form lives in its own partial, so you can create one and copy the provided code into there.

After completing the payment form, the customer should be taken to the gateway's unique "checkout URL" which will handle validation and creation of the order.

## Confirmation
Once the order has been created, the customer will be redirected to the confirmation step, where you can display details about their order.

Cargo generates a temporary signed URL, meaning the confirmation page will be valid for one hour after checkout.

The URL contains the `order_id` as a query parameter, which you can pass into the [`{{ orders }}`](/docs/tags/orders) tag to display the order information:

::tabs
::tab antlers
```antlers
{{ orders :id:is="get:order_id" }}
	<h1>Thanks for your order!</h1>
	<p>Your order number is <strong>#{{ order_number }}</strong>. We'll send you a confirmation email once we've finished processing your payment.</p>

	<hr>

	<p>{{ customer:name }} - {{ customer:email }}</p>
	<p>{{ shipping_line_1 }}, {{ shipping_line_2 }}, ...</p>
	<p>{{ billing_line_1 }}, {{ billing_line_2 }}, ...</p>
{{ /orders }}
``` 
::tab blade
```blade
<s:orders id:is="{{ request()->input('order_id') }}">  
    <h1>Thanks for your order!</h1>  
    <p>Your order number is <strong>#{{ $order_number }}</strong>. We'll send you a confirmation email once we've finished processing your payment.</p>  
      
    <hr>  
      
    <p>{{ $customer->name }} - {{ $customer->email }}</p>  
    <p>{{ $shipping_line_1 }}, {{ $shipping_line_2 }}, ...</p>  
    <p>{{ $billing_line_1 }}, {{ $billing_line_2 }}, ...</p>  
</s:orders>
```
::