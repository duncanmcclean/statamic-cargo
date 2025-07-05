---
title: "Checkout: Introduction"
---
It's all well and good having a website listing your products, but if your customers can't actually buy anything, then what's the point?

## Pre-built checkout flow
Cargo ships with a **pre-built checkout flow**, saving you precious development time. Handling the whole process on a single page, it features a minimal design and flexible Antlers templates, making it easy to customise for your project.

![Screenshot of Cargo's pre-built checkout flow](/images/prebuilt-checkout.png)

@blade
<x-button :href="url('frontend/checkout/prebuilt')" text="Find out more" />
@endblade

## Custom checkout flow
If you need more control over the checkout process, you can also build your own checkout flow from scratch, using Cargo's [Antlers tags](/frontend/tags/cart) and [JSON API endpoints](/frontend/json-api/introduction).

@blade
<x-button :href="url('frontend/checkout/custom')" text="Find out more" />
@endblade