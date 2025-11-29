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

        $timelineEvents = collect($statusLog)->map(function ($log) {
            $timestamp = $this->parseStatusLogTimestamp($log->timestamp ?? $log['timestamp'] ?? null);
            $status = $log->status ?? $log['status'] ?? null;

            if (! $timestamp || ! $status) {
                return null;
            }

            $mappedStatus = $this->mapStatusLogStatus($status);

            return [
                'timestamp' => $timestamp,
                'event' => 'order_status_changed',
                'metadata' => [
                    'new' => $mappedStatus,
                ],
            ];
        })->filter()->values()->all();

        return $timelineEvents;
    }

    private function parseStatusLogTimestamp(mixed $timestamp): ?int
    {
        if (! $timestamp) {
            return null;
        }

        try {
            // If it's already a timestamp, return it
            if (is_numeric($timestamp)) {
                return (int) $timestamp;
            }

            // Parse the datetime string and convert to timestamp
            return Carbon::parse($timestamp)->timestamp;
        } catch (\Exception $e) {
            return null;
        }
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
