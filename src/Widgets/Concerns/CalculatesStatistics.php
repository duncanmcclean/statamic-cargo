<?php

namespace DuncanMcClean\Cargo\Widgets\Concerns;

trait CalculatesStatistics
{
    private function calculateTrend(int $currentStatistic, int $previousStatistic): array
    {
        $percentage = 0;
        $direction = null;

        if ($previousStatistic > 0) {
            $percentage = (($currentStatistic - $previousStatistic) / $previousStatistic) * 100;
            $direction = $currentStatistic > $previousStatistic ? 'up' : ($currentStatistic < $previousStatistic ? 'down' : null);
        } elseif ($currentStatistic > 0) {
            $direction = 'up';
            $percentage = 100;
        }

        return [round($percentage), $direction];
    }
}
