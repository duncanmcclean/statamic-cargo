<?php

namespace DuncanMcClean\Cargo\Contracts\Taxes;

use DuncanMcClean\Cargo\Contracts\Purchasable;
use DuncanMcClean\Cargo\Data\Address;
use DuncanMcClean\Cargo\Orders\LineItem;
use Illuminate\Support\Collection;

interface Driver
{
    public function setAddress(Address $address): self;

    public function setPurchasable(Purchasable $purchasable): self;

    public function setLineItem(LineItem $lineItem): self;

    public function getBreakdown(int $total): Collection;
}
