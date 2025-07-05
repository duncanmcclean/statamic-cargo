---
title: "PHP APIs: Products"
---

Since products are just entires, you can use Statamic's [Entry Repository](https://statamic.dev/repositories/entry-repository) to query/create/update products.

### Product Variants
If you need to add product variants programatically, you will need to manually construct the `product_variants` array, which would otherwise be done by the variants fieldtype.

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

The `product_variants` array is split in two parts:

* The `variants` array contains the variant options, things like size/colour/length/etc.
* The `options` array should contain every possible combination of the variant options. 
	* The `key` must be in "title snake case", combining the option keys.
	* The `variant` must be the combination of the option keys, but separated by commas.
	* All options should have a `price`.
	* If you've configured any "option fields", you can include their values in this array. 