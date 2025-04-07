---
title: Products
---

Products are just entries, with a few additional fields (Price / Type / Tax Class). 

Most of the time, products will live in a "Products" collection, however, you're more than welcome to use a different collection, or even configure multiple product collections.

```php
// config/statamic/cargo.php

'products' => [  
    'collections' => ['products'],  
],
```

## Digital Products
By default, Cargo assumes that all products are physical products. However, if you're planning to sell digital goods, like downloadable files, you should enable the `digital_products` option in the `cargo.php` config file:

```php
// config/statamic/simple-commerce.php

'products' => [
	'digital_products' => true,
],
```

Once enabled, a toggle will appear on product entries allowing you to determine if a product is "Physical" or "Digital". 

:::tip Note
Right now, Cargo doesn't support digital downloads, like Simple Commerce did. However, it's coming soon.
:::

## Inventory / Stock Tracking
If you want to keep track of inventory, add a "Stock" field to your collection blueprint and specify a stock count on each product.

Then, whenever an order is created, Cargo will update the product stock field.

Cargo will also dispatch events when a product is out of stock, and when a product is running low on stock. You can configure the "low on stock" threshold in the `cargo.php` config file:

```php
// config/statamic/cargo.php

'products' => [  
    'low_stock_threshold' => 5,  
],
```

:::tip Note
If you're using [product variants](#product-variants), you should add "Stock" as a variant options field.
:::

## Product Variants
Cargo supports product variants, allowing you to specify different groups of options (like size, colours, etc). 

From those options, it'll create a matrix of all the various combinations and allow you to define the price, stock levels, and any other fields for each item.

This is what it looks like in the Control Panel:


![[product-variants.png]]

To use product variants...
1. Remove the "Price" field from the collection blueprint
	* You may want to create a separate blueprint for variant products, if you need to support non-variant products too.
2. Add a new "Product Variants" field to your collection blueprint
	* Make sure the field's handle is `product_variants`, otherwise it won't get picked up.
	* If needed, you can configure "option fields" so you can define things like SKUs or product photos on each option.
3. Now, you may configure product variants. 

### Templating
You can loop through variant options like this:

::tabs
::tab antlers
```antlers
{{ product_variants:options }}
	{{ variant }} - {{ price }}
{{ /product_variants:options }}
```
::tab blade
```blade
@foreach($product_variants['options'] as $option)
	{{ $option['variant'] }} - {{ $option['price'] }}
@endforeach
```
::

When adding a variant to the cart, make sure to include a `variant` input:

::tabs
::tab antlers
```antlers
{{ cart:add }}
	<input type="hidden" name="product" value="{{ page:id }}">
	<input type="hidden" name="variant" value="chosen-variant-key">
	<button>Add to cart</button>
{{ /cart:add }}
```
::tab blade
```blade
<s:cart:add>
	<input type="hidden" name="product" value="{{ $page['id'] }}">
	<input type="hidden" name="variant" value="chosen-variant-key">
	<button>Add to cart</button>
</s:cart:add>
```
::
