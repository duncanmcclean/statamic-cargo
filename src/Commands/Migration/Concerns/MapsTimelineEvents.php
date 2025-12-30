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
                $datetime = Carbon::parse($statusLogEvent['timestamp'])->format('Y-m-d H:i:s');

                $mappedStatus = $this->mapStatusLogStatus($status);

                if ($status === 'placed') {
                    return [
                        'datetime' => $datetime,
                        'type' => 'order_created',
                    ];
                }

                $event = [
                    'datetime' => $datetime,
                    'type' => 'order_status_changed',
                    'metadata' => array_filter([
                        'Original Status' => $previousStatus,
                        'New Status' => $mappedStatus,
                    ]),
                ];

                $previousStatus = $mappedStatus;

                return $event;
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
