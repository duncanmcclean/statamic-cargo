---
title: Actions
---

Cargo provides PHP actions for performing cart operations outside of Antlers tags - useful when working with Livewire components, custom controllers, or event listeners.

This page documents the available actions along with their parameters.

## AddToCart

The `AddToCart` action is responsible for adding products to the cart. 

It'll validate stock, check for prerequisite products, check if the product already exists in the customer's cart and finally add a line item to the cart. 

```php
use DuncanMcClean\Cargo\Cart\Actions\AddToCart;
use DuncanMcClean\Cargo\Facades\Cart;

$cart = Cart::current();

app(AddToCart::class)->handle(
    $cart,
    $product,
    $variant,
    $quantity,
    $data
);

$cart->save();
```

| Parameter  | Description                                                                                                            |
|------------|------------------------------------------------------------------------------------------------------------------------|
| `cart`     | Instance of [`Cart`](/extending/php-apis/carts)                                                                        |
| `product`  | Instance of [`Product`](/extending/php-apis/products)                                                                  |
| `variant`  | Optional. Instance of `ProductVariant`. You can do `$product->productVariant($key)` to get a product variant instance. |
| `quantity` | Optional. Line item quantity. Defaults to `1`                                                                          |
| `data`     | Optional. Laravel [`Collection`](https://laravel.com/docs/master/collections) instance containing line item data.      |

## PrerequisiteProductsCheck

The `PrerequisiteProductsCheck` action is responsible for ensuring that the customer has purchased the specified "prerequisite products" in the past.

A `ValidationException` will be thrown when a customer is missing from the cart or the customer hasn't purchased the prerequisite products.

```php
use DuncanMcClean\Cargo\Cart\Actions\PrerequisiteProductsCheck;
use DuncanMcClean\Cargo\Facades\Cart;

$cart = Cart::current();

app(PrerequisiteProductsCheck::class)->handle($cart, $product);

$cart->save();
```

| Parameter | Description                                           |
|-----------|-------------------------------------------------------|
| `cart`    | Instance of [`Cart`](/extending/php-apis/carts)       |
| `product` | Instance of [`Product`](/extending/php-apis/products) |

## UpdateDiscounts

Typically run during the Checkout process, the `UpdateDiscounts` action is responsible for updating the "redemption count" on discounts, and dispatching the [`DiscountRedeemed`](/extending/events/list#discountredeemed) event.

```php
use DuncanMcClean\Cargo\Discounts\Actions\UpdateDiscounts;

app(UpdateDiscounts::class)->handle($order);
```

| Parameter | Description                                       |
|-----------|---------------------------------------------------|
| `order`   | Instance of [`Order`](/extending/php-apis/orders) |

## UpdateStock

Typically run during the Checkout process, the `UpdateStock` action is responsible for updating the [stock counters](/docs/products#inventory--stock-tracking) on products and variants. It also dispatches various [stock-related events](/extending/events/list#productnostockremaining).

```php
use DuncanMcClean\Cargo\Products\Actions\UpdateStock;

app(UpdateStock::class)->handle($order);
```

| Parameter | Description                                       |
|-----------|---------------------------------------------------|
| `order`   | Instance of [`Order`](/extending/php-apis/orders) |

## ValidateStock

The `ValidateStock` action is responsible for ensuring that products have sufficient stock to fulfill the customer's order. 

A `ValidationException` will be thrown when there's insufficient stock to fulfill the customer's order.

```php
use DuncanMcClean\Cargo\Products\Actions\ValidateStock;

app(ValidateStock::class)->handle(
    $lineItem,
    $product,
    $variant,
    $quantity,
);
```

| Parameter  | Description                                                                                                                                                |
|------------|------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `lineItem` | Instance of `LineItem`. Can be `null` when the `$product` parameter is present.                                                                            |
| `product`  | Instance of [`Product`](/extending/php-apis/products). Can be `null` when the `$lineItem` parameter is present.                                            |
| `variant`  | Required when dealing with a variant product. Instance of `ProductVariant`. You can do `$product->productVariant($key)` to get a product variant instance. |
| `quantity` | Line item quantity. Can be `null` when the `$lineItem` parameter is present.                                                                               |