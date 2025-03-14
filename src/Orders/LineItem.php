<?php

namespace DuncanMcClean\Cargo\Orders;

use DuncanMcClean\Cargo\Contracts\Products\Product as ProductContract;
use DuncanMcClean\Cargo\Facades\Product;
use DuncanMcClean\Cargo\Products\ProductVariant;
use Statamic\Contracts\Data\Augmented;
use Statamic\Data\ContainsData;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Fields\Blueprint as StatamicBlueprint;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class LineItem
{
    use ContainsData, FluentlyGetsAndSets, HasAugmentedInstance;

    public $id;
    public $product;
    public $variant;
    public $quantity;
    public $unitPrice;
    public $subTotal;
    public $taxTotal;
    public $total;

    public function __construct()
    {
        $this->data = collect();
    }

    public function id($id = null)
    {
        return $this
            ->fluentlyGetOrSet('id')
            ->args(func_get_args());
    }

    public function product($product = null)
    {
        return $this
            ->fluentlyGetOrSet('product')
            ->getter(function ($product) {
                if (! $product) {
                    return null;
                }

                return Product::find($product);
            })
            ->setter(function ($product) {
                if ($product instanceof ProductContract) {
                    return $product->id();
                }

                return $product;
            })
            ->args(func_get_args());
    }

    public function variant($variant = null)
    {
        return $this
            ->fluentlyGetOrSet('variant')
            ->getter(function ($variant) {
                if (! $variant) {
                    return null;
                }

                return $this->product()?->variant($variant);
            })
            ->setter(function ($variant) {
                if ($variant instanceof ProductVariant) {
                    return $variant->id();
                }

                return $variant;
            })
            ->args(func_get_args());
    }

    public function quantity($quantity = null)
    {
        return $this
            ->fluentlyGetOrSet('quantity')
            ->args(func_get_args());
    }

    public function unitPrice()
    {
        return $this
            ->fluentlyGetOrSet('unitPrice')
            ->args(func_get_args());
    }

    public function subTotal()
    {
        return $this
            ->fluentlyGetOrSet('subTotal')
            ->args(func_get_args());
    }

    public function taxTotal($taxTotal = null)
    {
        return $this
            ->fluentlyGetOrSet('taxTotal')
            ->args(func_get_args());
    }

    public function total($total = null)
    {
        return $this
            ->fluentlyGetOrSet('total')
            ->args(func_get_args());
    }

    public function defaultAugmentedArrayKeys()
    {
        return [];
    }

    public function shallowAugmentedArrayKeys()
    {
        return ['id', 'product', 'variant', 'quantity', 'unit_price', 'sub_total', 'tax_total', 'total'];
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedLineItem($this);
    }

    public function blueprint(): StatamicBlueprint
    {
        return LineItems::blueprint();
    }

    public function fileData(): array
    {
        return $this->data()->merge([
            'id' => $this->id,
            'product' => $this->product,
            'variant' => $this->variant,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'sub_total' => $this->subTotal,
            'tax_total' => $this->taxTotal,
            'total' => $this->total,
        ])->filter()->all();
    }
}
