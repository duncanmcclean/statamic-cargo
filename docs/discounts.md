---
title: Discounts
---

Discounts are simply reductions to a cart's grand total, calculated using a set of user-defined rules.

Cargo supports two different types of discounts:
* Site-wide discounts
* Code-based discounts

Both of them are created in the same way, and work exactly the same, except for the fact that coupon-based discounts have a "Discount code" configured.

When a "Discount code" is set, the discount will only be applied when a customer enters the code. Otherwise, when a code isn't set, it will apply to everyone, without them needing to do anything.

You can create as many discounts as you like, and they can be used together. However, customers can only use on discount code at a time.

## Creating discounts
You can create discounts in the Control Panel. 

Discounts can be configured using various conditions and limitations. Most of the options are pretty self-explanatory:


![Discount Create Form](/images/discount-publish-form.png)

Cargo supports "Amount off" and "Percentage off" discount types out-of-the-box, with more on the way. If you need to, you can also [build your own discount type](#building-your-own-discount-type).

## Redeem discount codes
You can redeem discount codes using the `{{ cart:update }}` form. Simply provide a `discount_code` input so the customer can enter their discount code.

::tabs  
::tab antlers  
```antlers  
{{ cart:update }}    
    <input type="text" name="discount_code" value="{{ discount_code }}" required>    
	<button>Update</button>  
{{ /cart:update }}  
```  
::tab blade  
```blade  
<s:cart:update>    
    <input type="text" name="discount_code" value="{{ $discount_code }}" required>
	<button>Update</button>  
</s:cart:update>  
```  
::

When the customer attempts to use an invalid discount code, you use Statamic's [`{{ get_errors }}`](https://statamic.dev/tags/get_errors) tag to display the validation error. 

## Building your own discount type
If you need to support some kind of discount that Cargo doesn't already support, you can build your own discount type.

To get started, run the following command:

```
php please make:discount-type AmountOff
```

This will create a file in `app/DiscountTypes` which looks like this:

```php
<?php  
  
namespace App\DiscountTypes;  
  
use DuncanMcClean\Cargo\Contracts\Cart\Cart;  
use DuncanMcClean\Cargo\Discounts\Types\DiscountType;  
use DuncanMcClean\Cargo\Orders\LineItem;  
  
class AmountOff extends DiscountType  
{  
    public function calculate(Cart $cart, LineItem $lineItem): int  
    {  
        return (int) $this->discount->get('amount_off');  
    }  
  
    public function fieldItems(): array  
    {  
        return [  
            'amount_off' => [  
                'display' => __('Amount'),  
                'type' => 'money',  
                'validate' => ['required', 'min:0'],  
            ],  
        ];  
    }  
}
```

As you might expect, the `calculate` method should calculate the discount amount for a line item, returning it as an integer (in pence).

The `fieldItems` method allows you to define any config fields for the discount type. You can then access them using `$this->discount->get('field_handle')`.