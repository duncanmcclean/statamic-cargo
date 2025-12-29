<?php

namespace DuncanMcClean\Cargo\Commands\Migration\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Collection;

trait MapsTimelineEvents
{
    private function mapTimelineEvents(Collection $data): array
    {
        $previousStatus = null;

        return collect($data->get('status_log'))
            ->map(function (array $statusLogEvent) use (&$previousStatus): ?array {
                $status = $statusLogEvent['status'];
                $timestamp = Carbon::parse($statusLogEvent['timestamp'])->timestamp;

                $mappedStatus = $this->mapStatusLogStatus($status);
                $previousStatus = $mappedStatus;

                if ($status === 'placed') {
                    return [
                        'timestamp' => $timestamp,
                        'type' => 'order_created',
                    ];
                }

                return [
                    'timestamp' => $timestamp,
                    'type' => 'order_status_changed',
                    'metadata' => array_filter([
                        'original' => $previousStatus,
                        'new' => $status,
                    ]),
                ];
            })
            ->values()
            ->all();
    }

    private function mapStatusLogStatus(string $status): string
    {
        return match ($status) {
            'placed' => 'payment_pending',
            'paid' => 'payment_received',
            'dispatched', 'delivered' => 'shipped',
            'cancelled' => 'cancelled',
            'returned' => 'returned',
            default => $status,
        };
    }
}
