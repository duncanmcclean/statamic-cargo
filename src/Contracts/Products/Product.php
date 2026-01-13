<?php

namespace DuncanMcClean\Cargo\Contracts\Products;

use DuncanMcClean\Cargo\Products\ProductVariant;
use Illuminate\Support\Collection;

interface Product
{
    public function isStandardProduct(): bool;

    public function isVariantProduct(): bool;

    public function price(): ?int;

    public function productVariants(): array;

    public function stock(): ?int;

    public function isStockEnabled(): ?bool;

    public function variantOptions(): Collection;

    public function variant(string $optionKey): ?ProductVariant;
}