<?php

namespace App\Services;

class ForecastService
{
    /**
     * Return forecast data used by the dashboard charts.
     * In future this can be replaced by a model or external forecast provider.
     *
     * @return array
     */
    public function getForecastData(): array
    {
        return [
            ['date' => '2025-11-18', 'actual' => 10, 'forecast' => 10],
            ['date' => '2025-11-19', 'actual' => 15, 'forecast' => 10],
            ['date' => '2025-11-20', 'actual' => 9,  'forecast' => 12],
            ['date' => '2025-11-21', 'actual' => 13, 'forecast' => 10.8],
            ['date' => '2025-11-22', 'actual' => 13, 'forecast' => 11.68],
            ['date' => '2025-11-23', 'actual' => 13, 'forecast' => 12.21],
            ['date' => '2025-11-24', 'actual' => 12, 'forecast' => 12.52],
            ['date' => '2025-11-25', 'actual' => 55, 'forecast' => 12.31],
            ['date' => '2025-11-26', 'actual' => null, 'forecast' => 29.39],
        ];
    }

    /**
     * Get the latest forecasted evacuees value from forecast data.
     *
     * @param array $forecastData
     * @return int
     */
    public function getLatestForecastedEvacuees(array $forecastData): int
    {
        if (empty($forecastData)) {
            return 0;
        }

        $last = $forecastData[count($forecastData) - 1];
        return (int) round($last['forecast'] ?? 0);
    }
}
