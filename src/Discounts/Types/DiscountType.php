<?php

namespace DuncanMcClean\Cargo\Discounts\Types;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Contracts\Discounts\Discount;
use DuncanMcClean\Cargo\Orders\LineItem;
use Illuminate\Support\Carbon;
use Statamic\Extend\HasFields;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;

abstract class DiscountType
{
    use HasFields, HasHandle, HasTitle, RegistersItself;

    public $discount;

    public function setDiscount(Discount $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    abstract public function calculate(Cart $cart, LineItem $lineItem): int;

    public function isValidForLineItem(Cart $cart, LineItem $lineItem): bool
    {
        if ($this->discount->get('start_date') !== null) {
            if (Carbon::parse($this->discount->get('start_date'))->isFuture()) {
                return false;
            }
        }

        if ($this->discount->has('end_date') && $this->discount->get('end_date') !== null) {
            if (Carbon::parse($this->discount->get('end_date'))->isPast()) {
                return false;
            }
        }

        if ($this->discount->has('minimum_order_value') && $cart->subTotal()) {
            if ($cart->subTotal() < $this->discount->get('minimum_order_value')) {
                return false;
            }
        }

        if ($this->discount->has('maximum_uses') && $this->discount->get('maximum_uses') !== null) {
            if ($this->discount->get('redemptions_count') >= $this->discount->get('maximum_uses')) {
                return false;
            }
        }

        $products = $this->discount->get('products', []);

        if (count($products) >= 1 && ! in_array($lineItem->product()->id(), $products)) {
            return false;
        }

        $customers = $this->discount->get('customers', []);

        if (count($customers) >= 1 && ! $cart->customer()) {
            return false;
        }

        if (count($customers) >= 1 && ! in_array($cart->customer()->id(), $customers)) {
            return false;
        }

        return true;
    }

    public function fieldItems(): array
    {
        return [];
    }
}
