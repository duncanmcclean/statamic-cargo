<?php

namespace DuncanMcClean\Cargo\Contracts;

use DuncanMcClean\Cargo\Contracts\Taxes\TaxClass;

interface Purchasable
{
    public function purchasablePrice(): int;

    public function purchasableTaxClass(): ?TaxClass;
}
