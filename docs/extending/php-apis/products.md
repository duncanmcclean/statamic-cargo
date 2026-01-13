---
title: "PHP APIs: Products"
---

Products are entries, so you can use Statamic's [Entry Repository](https://statamic.dev/repositories/entry-repository) to query, create and update products.

Cargo will define an `entry_class` on your product collections, meaning that any queries will return `Product` (or `EloquentProduct`) instances, rather than standard `Entry` instances.

## Product Variants
### Retrieving variants
The `variantOptions` method returns a `Collection` of `ProductVariant` instances for every possible variant combination.

```php
$variant = $product->variantOptions()->first();

$variant->key();
$variant->product();
$variant->name();
$variant->price();
$variant->stock();
$variant->isStockEnabled();
```

You can use the `variant()` method to retrieve a single variant option. Simply pass the variant's key:

```php
$product->variant('Small_Red');
```

### Defining variants
In order to add product variants programmatically, you will need to manually construct the `product_variants` array, which would usually be done by the Product Variants fieldtype.

```php
$product->set('product_variants', [
	'variants' => [
		['name' => 'Size', 'values' => ['Small', 'Medium', 'Large']],
		['name' => 'Colour', 'values' => ['Red', 'Yellow']],
	],
	'options' => [
		['key' => 'Small_Red', 'variant' => 'Small, Red', 'price' => 2599],
		['key' => 'Small_Yellow', 'variant' => 'Small, Yellow', 'price' => 2599],
		['key' => 'Medium_Red', 'variant' => 'Medium, Red', 'price' => 2599],
		['key' => 'Medium_Yellow', 'variant' => 'Medium, Yellow', 'price' => 2599],
		['key' => 'Large_Red', 'variant' => 'Large, Red', 'price' => 2599],
		['key' => 'Large_Yellow', 'variant' => 'Large, Yellow', 'price' => 2599],
	],
]);
```

The `product_variants` array is split into two parts:

* The `variants` array contains the variant options, things like size/colour/length/etc.
* The `options` array should contain every possible combination of the variant options. 
	* The `key` must be in "title snake case", combining the option keys.
	* The `variant` must be the combination of the option keys, but separated by commas.
	* All options should have a `price`.
	* If you've configured any "option fields", you can include their values in this array.

## Product facade
While it doesn't do much compared to Statamic's `Entry` facade, Cargo provides a `Product` facade, responsible for scoping down entry queries to the relevant product collections.

```php
use DuncanMcClean\Cargo\Facades\Product;

Product::all();
Product::find('product-id');
Product::findOrFail('product-id');
```