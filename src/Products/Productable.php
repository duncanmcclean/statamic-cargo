<?php

namespace DuncanMcClean\Cargo\Products;

use DuncanMcClean\Cargo\Contracts\Taxes\TaxClass as TaxClassContract;
use DuncanMcClean\Cargo\Facades\TaxClass;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait Productable
{
    public function isStandardProduct(): bool
    {
        return $this->value('product_variants') === null;
    }

    public function isVariantProduct(): bool
    {
        return $this->value('product_variants') !== null;
    }

    public function price(): ?int
    {
        if ($this->isVariantProduct()) {
            return null;
        }

        $price = $this->value('price');

        if (str_contains($price, '.')) {
            $price = number_format($price, 2, '.', '');
            $price = (int) str_replace('.', '', (string) $price);
        }

        return $price ?? 0;
    }

    public function productVariants(): array
    {
        return $this->value('product_variants', []);
    }

    public function stock(): ?int
    {
        if ($this->isVariantProduct()) {
            return null;
        }

        return $this->value('stock');
    }

    public function isStockEnabled(): ?bool
    {
        if ($this->isVariantProduct()) {
            return null;
        }

        return $this->blueprint()->hasField('stock') && $this->stock() !== null;
    }

    public function variantOptions(): Collection
    {
        if (! $this->value('product_variants')) {
            return collect();
        }

        return collect(Arr::get($this->value('product_variants'), 'options'))
            ->map(function ($variantOption) {
                return (new ProductVariant)
                    ->key($variantOption['key'])
                    ->product($this)
                    ->name($variantOption['variant'])
                    ->price($variantOption['price'])
                    ->when(isset($variantOption['stock']), function ($productVariant) use ($variantOption) {
                        $productVariant->stock($variantOption['stock']);
                    })
                    ->data(Arr::except($variantOption, ['key', 'variant', 'price', 'stock']));
            });
    }

    public function variant(string $optionKey): ?ProductVariant
    {
        return $this->variantOptions()->filter(function ($variant) use ($optionKey) {
            return $optionKey === $variant->key();
        })->first();
    }

    public function purchasablePrice(): int
    {
        return $this->price();
    }

    public function purchasableTaxClass(): ?TaxClassContract
    {
        if (! $taxClass = $this->value('tax_class')) {
            return null;
        }

        return TaxClass::find($taxClass);
    }
}