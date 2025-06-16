---
title: Taxes
---

No one likes taxes, but we have to deal with them anyway. Cargo represents taxes using tax classes and tax zones, all configurable in the Control Panel.

If you need to deal with complicated taxation (eg. US states), we recommend integrating with a service like [TaxJar](https://www.taxjar.com).

## Tax Classes
Every product designates a tax class which determines how it's taxes:

![Tax Class field on product publish form](/images/product-tax-class-field.png)

Cargo will automatically create a "General" tax class for you when it's installed, however, you can add as many tax classes as you need.

You can either create a new tax class from the product publish form, or in the Control Panel under **Store -> Tax Classes**.

## Tax Zones
A tax zone represents a physical area where certain tax rates apply.

When a billing address falls within that area, any rates tied to that zone may be applied. Orders may have more than one matching tax zone, and all of them will be taken into account. 

You can create a tax zone in the Control Panel under **Store -> Tax Zones**:

![Screenshot of create tax zone page](/images/create-tax-zone.png)

A tax zone consists of a name, a physical location (specific countries, specific states, specific postcodes), as well as the tax rates which should be applied for each tax class.

When configuring tax zones based on postcodes, you can use wildcards like `?` and `*`. For example: `G2*`.

By default, prices are **inclusive** of tax. If you would rather them be exclusive of taxes instead, you can disable the behaviour in the `cargo.php` config file:

```php
// config/statamic/cargo.php

'taxes' => [  
    // Enable this when product prices are entered inclusive of tax.  
    // When calculating taxes, the tax will be deducted from the product price, then added back on at the end.    
    'price_includes_tax' => true,  
],
```

## Templating
You can display the cart's tax total using `{{ cart:tax_total }}`.

If needed, you can also display taxes on a more granular level, looping through each tax class and it's amount:

::tabs
::tab antlers
```antlers
{{ cart:tax_breakdown }}
	{{ description }} - {{ amount }}
{{ /cart:tax_breakdown }}
```
::tab blade
```blade
<s:cart:tax_breakdown>  
    {{ $description }} - {{ $amount }}  
</s:cart:tax_breakdown>
```
::

## Shipping
Cargo supports two different methods of calculating shipping taxes:

* **Tax Class:** Creates a "Shipping" tax class, allowing to assign a specific tax rate for all shipping costs.
* **Highest Rate:** Charges the highest tax rate from the cart's line items.

You can configure the method used in your `cargo.php` config file:

```php
// config/statamic/cargo.php

'taxes' => [  
	'shipping_tax_behaviour' => 'tax_class', // Either "tax_class" or "highest_tax_rate"
],
```

## Custom Tax Driver
If you need to integrate with a third-party taxation system, or just prefer to handle it yourself, you can create a custom tax driver.

In its simplest form, a tax driver looks like this:

```php
<?php  
  
namespace App\Cargo;  
  
use DuncanMcClean\Cargo\Contracts\Purchasable;  
use DuncanMcClean\Cargo\Contracts\Taxes\Driver as DriverContract;  
use DuncanMcClean\Cargo\Data\Address;  
use DuncanMcClean\Cargo\Facades\TaxZone as TaxZoneFacade;  
use DuncanMcClean\Cargo\Orders\LineItem;  
use Illuminate\Support\Collection;  
  
class CustomTaxDriver implements DriverContract  
{  
    public $address;  
    public $purchasable;  
    public $lineItem;  
  
    public function setAddress(Address $address): self  
    {  
        $this->address = $address;  
  
        return $this;  
    }  
  
    public function setPurchasable(Purchasable $purchasable): self  
    {  
        $this->purchasable = $purchasable;  
  
        return $this;  
    }  
  
    public function setLineItem(LineItem $lineItem): self  
    {  
        $this->lineItem = $lineItem;  
  
        return $this;  
    }  
  
    public function getBreakdown(int $total): Collection  
    {  
        $breakdown = collect();  

		// Calculate the taxes

		// Add each "tax" to the tax breakdown collection
		$breakdown->push(TaxCalculation::make(  
            rate: 20,  
            description: 'UK VAT',  
            zone: 'United Kingdom',  
            amount: 100  
        )); 

		return $breakdown;
    }
}
```

The `set` method are used by the calculator to provide context about the line item and billing address being calculated.

Then, in the `getBreakdown` method you can do your calculations, and return a collection of `TaxCalculation` objects.

You can then bind the tax driver in your `AppServiceProvider`:

```php
// app/Providers/AppServiceProvider.php

public function boot(): void
{
	$this->app->bind(
		\DuncanMcClean\Cargo\Contracts\Taxes\Driver::class,
		\App\Cargo\CustomTaxDriver::class
	);
}
```

:::tip Note
The "Tax Class" and "Tax Zone" features will be unavailable unless your custom tax driver extends Cargo's `DefaultTaxDriver` class.
:::