<?php

namespace DuncanMcClean\Cargo\Products;

use DuncanMcClean\Cargo\Contracts\Products\Product as Contract;
use DuncanMcClean\Cargo\Contracts\Purchasable;
use Statamic\Eloquent\Entries\Entry as EloquentEntry;

class EloquentProduct extends EloquentEntry implements Contract, Purchasable
{
    use Productable;
}
