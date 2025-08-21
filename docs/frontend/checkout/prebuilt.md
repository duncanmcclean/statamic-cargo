---
title: Pre-built checkout flow
description: "Cargo comes with a pre-built checkout flow, which you can publish and customise to suit your needs. This page explains how to install and use the pre-built checkout flow."
---
## Publishing
You may have published the pre-built checkout flow during the install process, if so, you can skip this step.

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

## How it works
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
You can customise the header, including the logo, by editing the `resources/views/checkout/_header.antlers.html` view:

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

:::tip note
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
{{ vite directory="checkout" hot="checkout" src="resources/css/checkout.css" }} {{# [tl! remove] #}}
{{ vite src="resources/css/site.css" }} {{# [tl! add] #}}
```

To stay organised, you may now delete the `public/checkout` directory.