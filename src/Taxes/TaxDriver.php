<?php

namespace DuncanMcClean\Cargo\Taxes;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Contracts\Purchasable;
use DuncanMcClean\Cargo\Contracts\Taxes\Driver as DriverContract;
use DuncanMcClean\Cargo\Data\Address;
use DuncanMcClean\Cargo\Orders\LineItem;
use Illuminate\Support\Collection;

abstract class TaxDriver implements DriverContract
{
    public $cart;
    public $address;
    public $purchasable;
    public $lineItem;

    public function setCart(Cart $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

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

    abstract public function getBreakdown(int $total): Collection;
}
