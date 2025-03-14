<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Purchasable;

class ProductStockLow
{
    public function __construct(public Purchasable $purchasable) {}
}
