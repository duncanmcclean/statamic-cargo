<?php

namespace DuncanMcClean\Cargo\Widgets;

use Illuminate\Support\Arr;
use Statamic\Facades\Entry;
use Statamic\Widgets\VueComponent;
use Statamic\Widgets\Widget;

class LowStockProducts extends Widget
{
    public function component()
    {
        $limit = $this->config('limit', 5);
        $collections = config('statamic.cargo.products.collections', ['products']);
        $threshold = config('statamic.cargo.products.low_stock_threshold', 10);

        $items = collect();

        Entry::query()
            ->whereIn('collection', $collections)
            ->chunk(100, function ($products) use ($items, $threshold) {
                $products->each(function ($product) use ($items, $threshold) {
                    if ($product->get('stock') !== null && $product->get('stock') < $threshold) {
                        $items->push([
                            'id' => $product->id(),
                            'title' => $product->get('title'),
                            'stock' => $product->get('stock'),
                            'price' => $product->get('price'),
                            'edit_url' => $product->editUrl(),
                        ]);
                    }

                    if ($productVariants = $product->get('product_variants')) {
                        $options = Arr::get($productVariants, 'options', []);

                        foreach ($options as $variant) {
                            if (isset($variant['stock']) && $variant['stock'] < $threshold) {
                                $items->push([
                                    'id' => $product->id().'::'.$variant['key'],
                                    'title' => $product->get('title').' - '.$variant['variant'],
                                    'stock' => $variant['stock'],
                                    'price' => $variant['price'],
                                    'edit_url' => $product->editUrl(),
                                ]);
                            }
                        }
                    }
                });
            });

        $products = $items
            ->sortBy('stock')
            ->take($limit)
            ->values();

        return VueComponent::render('low-stock-products-widget', [
            'title' => $this->config('title', __('Low Stock Products')),
            'products' => $products,
        ]);
    }
}
