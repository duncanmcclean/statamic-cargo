<?php

namespace DuncanMcClean\Cargo\Commands\Migration\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Collection;

trait MapsTimelineEvents
{
    private function mapTimelineEvents(Collection $data): array
    {
        $statusLog = $data->get('status_log', []);

        if (empty($statusLog)) {
            return [];
        }

        return collect($statusLog)->map(function ($log) {
            $timestamp = $log->timestamp ?? $log['timestamp'];
            $status = $log->status ?? $log['status'] ?? null;

            if (! $timestamp || ! $status) {
                return null;
            }

            $mappedStatus = $this->mapStatusLogStatus($status);

            return [
                'timestamp' => $timestamp,
                'event' => 'order_status_changed',
                'metadata' => [
                    // todo: original status?
                    'new' => $mappedStatus,
                ],
            ];
        })->filter()->values()->all();
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
