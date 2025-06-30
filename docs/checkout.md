---
title: Checkout
---

It's all well and good having a website listing your products, but if your customers can't actually buy anything, then what's the point?

Cargo includes a **pre-built checkout flow** - featuring a minimal design and flexible Antlers templates, making it easy to customise for your project.

![Screenshot of Cargo's pre-built checkout flow](/images/prebuilt-checkout.png)

If you wish, you can also build your own checkout flow from scratch, using nothing but Cargo's Antlers tags.

Either way, you can find documentation on both approaches below.

## Pre-built checkout flow
### Publishing
You may have published the pre-built checkout flow during the install process, if so you can skip this step.

To publish the pre-built checkout flow into your project, run this command:

```
php artisan vendor:publish --tag=cargo-prebuilt-checkout
```

Then, add the following to your `routes/web.php` file:

```php
Route::statamic('checkout', 'checkout.index', ['title' => 'Checkout', 'layout' => 'checkout.layout'])  
	->name('checkout');  
		  
Route::statamic('checkout/confirmation', 'checkout.confirmation', ['title' => 'Order Confirmation', 'layout' => 'checkout.layout'])  
	->name('checkout.confirmation')  
	->middleware('signed');
```

You can find the pre-built checkout views in `resources/views/checkout`, and you can access the checkout page at `/checkout` (assuming you have something in your cart).

**You own the published code**, meaning you can make any modifications you want, without needing to worry about your changes being overwritten by updates. 

![FREEDOM!!! 🏴󠁧󠁢󠁳󠁣󠁴󠁿](/images/braveheart-freedom.gif)

### How it works
If you're curious, here's a brief rundown of how the pre-built Checkout page works:

* It's built with Tailwind CSS and Alpine.js
* Most of the JavaScript heavy lifting happens in `resources/views/checkout/js/checkout.antlers.html`.
	* It handles the checkout step logic, sending requests and maintaining the `cart` state used for pre-filling inputs and displaying totals in the cart summary.
* Every checkout step uses a `step` partial which registers the step in JS and renders the step contents inside a `{{ cart:update }}` form.
	* Ultimately, the `{{ cart:update }}` tag outputs a standard `<form>` element. 
	* However, when the form is submitted, JavaScript takes over and sends the request over AJAX (to prevent a full-page reload from happening).
	* Then, when a response comes back, the `cart` object in Alpine's data will be updated.
* When you reach the shipping option or payment steps, they both make AJAX requests to get the available shipping options / payment gateways.
	* We're not using the equivalent Antlers tags for this as the available options could change from when the page is loaded vs when the customer is presented with the options.
		* For example: some shipping options may only be available for certain areas, but when the page is loaded, we don't have the customer's address yet so we need to fetch them later.

### Customization
#### Header / Logo
You can customize the header, including the logo, by editing the `resources/views/checkout/_header.antlers.html` view:

```antlers
<header class="p-8 md:pb-0 flex justify-center md:justify-start">
    <a href="{{ link to="/" }}" target="_blank" alt="{{ site:name }}">
        <img src="{{ asset:src src="images/logo.svg" }}" alt="{{ site:name }}" class="h-12"> {{# [tl! add] #}}
    </a>
</header>
```

#### Adding additional steps
You can find the existing checkout steps in `resources/views/checkout/index.antlers.html`. 

To add your own step, simply [create a partial](https://statamic.dev/tags/partial), and reference it from within the `index` template. 

All steps should be wrapped in the provided `step` partial, like this:

```antlers
{{ partial:checkout/step title="Gift" }}  
    <h2 class="mb-2">If this is a gift, let us know the name of the recipient and we can include a special gift note with the order.</h2>  
  
    <div class="flex flex-col space-y-4">  
        {{ partial:checkout/input  
            name="gift_recipient"  
            label="Gift Recipient"  
            type="text"  
            placeholder="John"  
        }}  
    </div>  
  
    {{ slot:footer }}  
        {{ partial:checkout/button label="Continue to Payment" }}  
    {{ /slot:footer }}  
{{ /partial:checkout/step }}
```

You should make sure to pass a `title` parameter to the partial, then provide the contents for the step inside the `{{ partial }}` tag.

By default, all steps are wrapped in the `{{ cart:update }}` tag, meaning whenever you submit the step, it'll make an AJAX request and update Alpine's `cart` object (you can read more about this under [How it works](#how-it-works)). You can provide the `formless` parameter to opt-out of this behaviour.

#### Using your own Tailwind CSS build
When you publish the pre-built checkout flow, a compiled `.css` file will be copied into your site's `public` directory.

To make changes to the design of the checkout page, you should integrate the styles into your own Tailwind CSS build, rather than using the pre-built one.

:::tip Note
These steps assume you already have Tailwind CSS setup in your project. If you don't, you can follow the [Tailwind CSS installation guide](https://tailwindcss.com/docs/installation/using-vite) to get started.
:::

First, install the `@tailwindcss/forms` plugin:

```bash
npm install @tailwindcss/forms
```

Next, import the plugin and add the required colours to your Tailwind CSS config:

::tabs
::tab tailwind4
```css
/* site.css */

@import "tailwindcss";

@plugin '@tailwindcss/forms'; /* [tl! highlight] */

@theme { /* [tl! highlight:3] */
	--color-brand: oklch(62.7% 0.194 149.214);
	--color-secondary: oklch(0.398438 0.090625 160);
}
```
::tab tailwind3
```js
// tailwind.config.js

module.exports = {
	theme: {
		extend: {
			colors: { // [tl! highlight:3]
				brand: 'oklch(62.7% 0.194 149.214)',
				secondary: 'oklch(0.398438 0.090625 160)',
			},
		},
	},
	plugins: [
		require('@tailwindcss/forms'), // [tl! highlight]
	],
};
```
::

Finally, update the `checkout/layout.antlers.html` file to reference your own stylesheet:

```antlers
{{ vite directory="checkout" src="resources/css/checkout.css" }} {{# [tl! remove] #}}
{{ vite src="resources/css/site.css" }} {{# [tl! add] #}}
```

To stay organised, you may now delete the `public/checkout` directory.

## Custom checkout flow
We recommend splitting your checkout flow into multiple steps, to allow for totals to be recalculated when the customer's cart is updated.

Most stores split their checkout flow into the following steps:
* Customer information
* Shipping and/or billing addresses
* Shipping method
* Payment
* Confirmation

In this guide, we're going to cover how to handle each of these steps. 

:::tip Note
It's hard to build a good checkout flow. If you'd rather not build everything from scratch, we recommend publishing the [pre-built checkout flow](#pre-built-checkout-flow) and customising it to your needs.
:::

### Setup
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

```files
resources
  views
    checkout
      customer.antlers.html
      addresses.antlers.html
      shipping.antlers.html
      confirmation.antlers.html
```

We're also going to create a `summary.antlers.html` view, which we'll use to display the cart's line items and totals.

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

Finally, we need to update the route names in the `cargo.php` file to match the routes we just created. Cargo uses these to handle redirects.

```php
// config/cargo.php

'routes' => [  
    'checkout' => 'checkout',  
    'checkout_confirmation' => 'checkout.confirmation',  
],
```

### Customer
Usually, when a customer is logged in, it means you already have their details, therefore there isn't much point in collecting their details again, so they can be redirected onto the next step.

However, for logged out users, you probably want to give them the option to login/register for an account **or** continue as a guest customer.

You can use Statamic's [`{{ user:login_form }}`](https://statamic.dev/tags/user-login_form) and [`{{ user:register_form }}`](https://statamic.dev/tags/user-register_form) tags to handle authentication, then use Cargo's `{{ cart:update }}` tag to create [guest customers](/docs/customers).

You should specify the `redirect` parameter on all form tags, so whichever action the customer takes, they end up on the next step.

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

### Addresses
Most of the time, you'll want to collect the customer's shipping and billing addresses during checkout. If you're only selling digital products, you can leave out the shipping address. 

The form fields are pretty self explanatory - just make sure the input names match the ones in this example.

You might notice that the state dropdowns in the example below are a *little* weird... this is because the options need to be pulled from the backend whenever the country dropdown is changed. This is achieved using a JavaScript event listener, seen at the bottom of the template.

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

After the form has been submitted, the cart's taxes will be calculated.

### Shipping
You can use the `{{ shipping_options }}` tag to loop through the available shipping options for the cart.

Inside the loop, you have access to the following variables:
* `name` 
* `handle` 
* `price` 
* `shipping_method`

When you submit the form, Cargo will also expect you to pass a `shipping_method` input. You can see this in the below example, it gets set `onchange`.

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
<!--                 onchange="document.getElementsByName('shipping_method')[0].value = '{{ shipping_method }}'" -->
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

After the form has been submitted, the cart's shipping costs will be calculated.

### Payment
It's finally time for the step I know you've been waiting for.... **payments!** 🎉

In all seriousness though, this step isn't as big and scary as it sounds...

For **free carts**, all you need to do is display a simple `<form>`, that when submitted will create the order without payment.

For **paid carts**, you need to loop through the available payment gateways using the aptly named `{{ payment_gateways }}` tag and display the payment form for each.

Inside the loop, you have access to the following variables:
* `name` 
* `handle` 
* `checkout_url` 
* Anything returned by the payment gateway's `setup` method.

```antlers
<h1>Checkout</h1>

<h2>Payment</h2>

{{ if {get_error:checkout} }}
	<p>{{ get_error:checkout }}</p>
{{ /if }}

{{ if {cart:is_free} }}
	<form action="{{ route:statamic.cargo.cart.checkout }}">
		<p>{{ 'No payment required. Continue to checkout.' | trans }}</p>

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

Every [payment gateway](/docs/payment-gateways) should provide a "payment form" template in its documentation. 

In the above example, each payment form lives in its own partial, so you can create one and copy the provided code into there.

After completing the payment form, the customer should be taken to the gateway's unique "checkout URL" which will handle validation and creation of the order.

### Confirmation
Once the order has been created, the customer will be redirected to the confirmation step, where you can display details about their order.

Cargo generates a temporary signed URL, meaning the confirmation page will be valid for one hour after checkout.

The URL contains the `order_id` as a query parameter, which you can pass into the [`{{ orders }}`](/docs/orders-tag) tag to display the order information:

```html
{{ orders :id:is="get:order_id" }}
	<h1>Thanks for your order!</h1>
	<p>Your order number is <strong>#{{ order_number }}</strong>. We'll send you a confirmation email once we've finished processing your payment.</p>

	<hr>

	<p>{{ customer:name }} - {{ customer:email }}</p>
	<p>{{ shipping_line_1 }}, {{ shipping_line_2 }}, ...</p>
	<p>{{ billing_line_1 }}, {{ billing_line_2 }}, ...</p>
{{ /orders }}
``` 