<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Purchasable;

class ProductNoStockRemaining
{
    public function __construct(public Purchasable $purchasable) {}
}
