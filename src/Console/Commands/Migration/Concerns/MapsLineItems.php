<?php

namespace DuncanMcClean\Cargo\Console\Commands\Migration\Concerns;

use Illuminate\Support\Collection;

trait MapsLineItems
{
    private function mapLineItems(Collection $data): array
    {
        return collect($data->get('items'))->map(function ($lineItem) {
            return array_filter([
                'id' => $lineItem['id'],
                'product' => $lineItem['product'],
                'variant' => $lineItem['variant']['variant'] ?? $lineItem['variant'] ?? null,
                'quantity' => $lineItem['quantity'] ?? 1,
                'unit_price' => $lineItem['total'] ?? 0,
                'sub_total' => $lineItem['total'] ?? 0,
                'total' => $lineItem['total'] ?? 0,
                'tax_breakdown' => isset($lineItem['tax']) ? [
                    [
                        'rate' => $lineItem['tax']['rate'],
                        'description' => 'Unknown',
                        'zone' => 'Unknown',
                        'amount' => $lineItem['tax']['amount'],
                    ],
                ] : null,
                'download_count' => isset($lineItem['metadata']['download_history'])
                    ? count($lineItem['metadata']['download_history'])
                    : null,
                ...$lineItem['metadata'] ?? [],
            ]);
        })->values()->all();
    }
}
