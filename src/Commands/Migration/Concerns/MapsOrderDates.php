<?php

namespace DuncanMcClean\Cargo\Commands\Migration\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

trait MapsOrderDates
{
    public function mapOrderDate(Collection $data): Carbon
    {
        if ($orderDate = $data->get('order_date')) {
            return Carbon::parse($orderDate);
        }

        if ($date = $data->get('date')) {
            return Carbon::parse($date);
        }

        $statusLogPlacedTimestamp = collect($data->get('status_log'))
            ->where('status', 'placed')
            ->sortByDesc('timestamp')
            ->pluck('timestamp')
            ->first();

        if ($statusLogPlacedTimestamp) {
            return Carbon::parse($statusLogPlacedTimestamp);
        }

        if ($createdAt = $data->get('created_at')) {
            return Carbon::parse($createdAt);
        }

        if ($updatedAt = $data->get('updated_at')) {
            return Carbon::parse($updatedAt);
        }

        return Carbon::now()->startOfDay();
    }
}
