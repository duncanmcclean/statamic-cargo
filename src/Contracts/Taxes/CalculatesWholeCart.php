<?php

namespace DuncanMcClean\Cargo\Contracts\Taxes;

use Illuminate\Support\Collection;

interface CalculatesWholeCart
{
    public function getCartBreakdown(): Collection;
}
