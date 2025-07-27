<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * CalculationService
 *
 * Handles all business logic related to SPPD cost calculations
 * Provides automatic cost estimation and validation
 */
class CalculationService
{
    /**
     * Standard rates for automatic calculation
     */
    protected array $standardRates = [
        'uang_harian' => [
            'dalam_kota' => 250000,      // Rp 250k/hari
            'luar_kota' => 350000,       // Rp 350k/hari
            'luar_provinsi' => 500000,   // Rp 500k/hari
        ],
        'penginapan' => [
            'guest_house' => 400000,     // Rp 400k/malam
            'hotel_standar' => 750000,   // Rp 750k/malam
            'hotel_bintang' => 1200000,  // Rp 1.2jt/malam
        ],
        'transportasi' => [
            'pesawat' => 2500000,        // Rp 2.5jt PP
            'kereta_api' => 500000,      // Rp 500k PP
            'bus' => 250000,             // Rp 250k PP
            'kendaraan_dinas' => 300000, // Rp 300k (BBM + tol)
            'kendaraan_pribadi' => 400000, // Rp 400k (reimburse)
        ],
    ];

    /**
     * Calculate travel duration in days
     *
     * @param string $startDate
     * @param string $endDate
     * @return int
     */
    public function calculateDuration(string $startDate, string $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Minimum 1 day
        return max(1, $start->diffInDays($end) + 1);
    }

    /**
     * Calculate accommodation nights (duration - 1)
     *
     * @param int $duration
     * @return int
     */
    public function calculateAccommodationNights(int $duration): int
    {
        return max(0, $duration - 1);
    }

    /**
     * Auto-suggest transportation cost based on type
     *
     * @param string $transportationType
     * @return int
     */
    public function suggestTransportationCost(string $transportationType): int
    {
        $typeMap = [
            'Pesawat' => 'pesawat',
            'Kereta Api' => 'kereta_api',
            'Bus' => 'bus',
            'Kendaraan Dinas' => 'kendaraan_dinas',
            'Kendaraan Pribadi' => 'kendaraan_pribadi',
        ];

        $key = $typeMap[$transportationType] ?? null;

        return $key ? $this->standardRates['transportasi'][$key] : 0;
    }

    /**
     * Auto-calculate daily allowance based on destination and duration
     *
     * @param string $destination
     * @param int $duration
     * @return int
     */
    public function calculateDailyAllowance(string $destination, int $duration): int
    {
        $rate = $this->getDailyAllowanceRate($destination);
        return $rate * $duration;
    }

    /**
     * Get daily allowance rate based on destination
     *
     * @param string $destination
     * @return int
     */
    protected function getDailyAllowanceRate(string $destination): int
    {
        $destination = strtolower($destination);

        // Check if destination is out of province
        $outOfProvinceKeywords = ['jakarta', 'bandung', 'surabaya', 'yogyakarta', 'medan', 'makassar'];
        foreach ($outOfProvinceKeywords as $keyword) {
            if (strpos($destination, $keyword) !== false) {
                return $this->standardRates['uang_harian']['luar_provinsi'];
            }
        }

        // Check if destination is within province but outside city
        $outsideCityKeywords = ['kpu provinsi', 'kab', 'kabupaten', 'kota'];
        foreach ($outsideCityKeywords as $keyword) {
            if (strpos($destination, $keyword) !== false) {
                return $this->standardRates['uang_harian']['luar_kota'];
            }
        }

        // Default to local rate
        return $this->standardRates['uang_harian']['dalam_kota'];
    }

    /**
     * Auto-calculate accommodation cost based on duration and type
     *
     * @param int $duration
     * @param string $accommodationType
     * @return int
     */
    public function calculateAccommodationCost(int $duration, string $accommodationType = 'hotel_standar'): int
    {
        $nights = $this->calculateAccommodationNights($duration);

        if ($nights <= 0) {
            return 0;
        }

        $rate = $this->getAccommodationRate($accommodationType);
        return $rate * $nights;
    }

    /**
     * Get accommodation rate based on type
     *
     * @param string $type
     * @return int
     */
    protected function getAccommodationRate(string $type): int
    {
        $type = strtolower($type);

        if (strpos($type, 'guest') !== false || strpos($type, 'wisma') !== false) {
            return $this->standardRates['penginapan']['guest_house'];
        }

        if (strpos($type, 'bintang') !== false || strpos($type, 'star') !== false) {
            return $this->standardRates['penginapan']['hotel_bintang'];
        }

        return $this->standardRates['penginapan']['hotel_standar'];
    }

    /**
     * Calculate total cost from all components
     *
     * @param array $costs
     * @return int
     */
    public function calculateTotalCost(array $costs): int
    {
        $total = 0;
        $costComponents = ['biaya_transport', 'biaya_penginapan', 'uang_harian', 'biaya_lainnya'];

        foreach ($costComponents as $component) {
            $total += $this->normalizeCost($costs[$component] ?? 0);
        }

        return $total;
    }

    /**
     * Normalize cost input (remove formatting, convert to integer)
     *
     * @param mixed $cost
     * @return int
     */
    public function normalizeCost($cost): int
    {
        if (is_numeric($cost)) {
            return (int) $cost;
        }

        if (is_string($cost)) {
            // Remove all non-digit characters
            return (int) preg_replace('/[^\d]/', '', $cost);
        }

        return 0;
    }

    /**
     * Format cost for display (add thousand separators)
     *
     * @param int $cost
     * @return string
     */
    public function formatCost(int $cost): string
    {
        return number_format($cost, 0, ',', '.');
    }

    /**
     * Auto-calculate all costs based on travel details
     *
     * @param array $travelDetails
     * @return array
     */
    public function autoCalculateAllCosts(array $travelDetails): array
    {
        $duration = $this->calculateDuration(
            $travelDetails['tanggal_berangkat'],
            $travelDetails['tanggal_kembali']
        );

        $transportCost = $this->suggestTransportationCost($travelDetails['transportasi'] ?? '');

        $dailyAllowance = $this->calculateDailyAllowance(
            $travelDetails['tujuan'] ?? '',
            $duration
        );

        $accommodationCost = $this->calculateAccommodationCost(
            $duration,
            $travelDetails['tempat_menginap'] ?? 'hotel_standar'
        );

        $totalCost = $transportCost + $accommodationCost + $dailyAllowance;

        return [
            'lama_perjalanan' => $duration,
            'biaya_transport' => $transportCost,
            'biaya_penginapan' => $accommodationCost,
            'uang_harian' => $dailyAllowance,
            'biaya_lainnya' => 0,
            'total_biaya' => $totalCost,
            'formatted' => [
                'biaya_transport' => $this->formatCost($transportCost),
                'biaya_penginapan' => $this->formatCost($accommodationCost),
                'uang_harian' => $this->formatCost($dailyAllowance),
                'total_biaya' => $this->formatCost($totalCost),
            ]
        ];
    }

    /**
     * Validate cost amounts
     *
     * @param array $costs
     * @return array Validation errors
     */
    public function validateCosts(array $costs): array
    {
        $errors = [];
        $totalCost = $this->calculateTotalCost($costs);

        // Check for reasonable limits
        if ($totalCost > 50000000) { // 50 million
            $errors[] = 'Total biaya terlalu besar (maksimal Rp 50.000.000)';
        }

        if ($totalCost < 0) {
            $errors[] = 'Total biaya tidak boleh negatif';
        }

        // Check individual components
        $transportCost = $this->normalizeCost($costs['biaya_transport'] ?? 0);
        if ($transportCost > 10000000) { // 10 million
            $errors[] = 'Biaya transportasi terlalu besar (maksimal Rp 10.000.000)';
        }

        $accommodationCost = $this->normalizeCost($costs['biaya_penginapan'] ?? 0);
        if ($accommodationCost > 20000000) { // 20 million
            $errors[] = 'Biaya penginapan terlalu besar (maksimal Rp 20.000.000)';
        }

        return $errors;
    }

    /**
     * Get cost estimation guide
     *
     * @return array
     */
    public function getCostGuide(): array
    {
        return [
            'transportasi' => [
                'Pesawat' => 'Rp 1.5-3 juta (PP)',
                'Kereta Api' => 'Rp 400-600rb (PP)',
                'Bus' => 'Rp 200-300rb (PP)',
                'Kendaraan Dinas' => 'Rp 200-400rb (BBM+tol)',
                'Kendaraan Pribadi' => 'Rp 300-500rb (reimburse)',
            ],
            'penginapan' => [
                'Hotel Standar' => 'Rp 500-800rb/malam',
                'Hotel Bintang 3+' => 'Rp 800rb-1.2jt/malam',
                'Guest House' => 'Rp 300-500rb/malam',
            ],
            'uang_harian' => [
                'Dalam Kota' => 'Rp 200-300rb/hari',
                'Luar Kota' => 'Rp 300-500rb/hari',
                'Luar Provinsi' => 'Rp 400-600rb/hari',
            ]
        ];
    }

    /**
     * Get standard rates configuration
     *
     * @return array
     */
    public function getStandardRates(): array
    {
        return $this->standardRates;
    }

    /**
     * Update standard rates (for admin configuration)
     *
     * @param array $rates
     * @return void
     */
    public function updateStandardRates(array $rates): void
    {
        $this->standardRates = array_merge($this->standardRates, $rates);
    }
}
