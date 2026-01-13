<?php

namespace DuncanMcClean\Cargo\Products;

use DuncanMcClean\Cargo\Contracts\Products\Product as Contract;
use DuncanMcClean\Cargo\Contracts\Purchasable;
use Statamic\Entries\Entry;

class Product extends Entry implements Contract, Purchasable
{
    use Productable;
}
