<?php

namespace Database\Seeders;

use App\Models\FraudRule;
use Illuminate\Database\Seeder;

class FraudRulesSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'name' => 'High Cost Destination Alert',
                'type' => 'high_cost_destination',
                'description' => 'Detect calls to known premium rate destinations (900, 901, 902, etc.)',
                'threshold' => null,
                'parameters' => [
                    'prefixes' => ['900', '901', '902', '905', '803', '806', '807', '1900', '1976'],
                ],
                'action' => 'alert',
                'severity' => 'high',
                'active' => true,
            ],
            [
                'name' => 'Traffic Spike Detection',
                'type' => 'traffic_spike',
                'description' => 'Alert when traffic exceeds 200% of normal average',
                'threshold' => 200,
                'parameters' => [
                    'comparison_period_hours' => 24,
                    'window_minutes' => 15,
                ],
                'action' => 'alert',
                'severity' => 'medium',
                'active' => true,
            ],
            [
                'name' => 'Wangiri Pattern Detection',
                'type' => 'wangiri',
                'description' => 'Detect potential Wangiri fraud (many short calls < 6 seconds)',
                'threshold' => 50,
                'parameters' => [
                    'max_duration_seconds' => 6,
                    'window_minutes' => 5,
                ],
                'action' => 'alert',
                'severity' => 'high',
                'active' => true,
            ],
            [
                'name' => 'Short Calls Burst Detection',
                'type' => 'short_calls_burst',
                'description' => 'Detect bursts of very short calls that may indicate fraud or testing',
                'threshold' => 30,
                'parameters' => [
                    'max_duration_seconds' => 10,
                    'window_minutes' => 5,
                ],
                'action' => 'alert',
                'severity' => 'medium',
                'active' => true,
            ],
            [
                'name' => 'Unusual Destination Alert',
                'type' => 'unusual_destination',
                'description' => 'Alert when customer calls to a country/prefix never used before',
                'threshold' => null,
                'parameters' => [
                    'lookback_days' => 30,
                ],
                'action' => 'alert',
                'severity' => 'low',
                'active' => true,
            ],
            [
                'name' => 'High Failure Rate Detection',
                'type' => 'high_failure_rate',
                'description' => 'Alert when failure rate exceeds 80% in a short period',
                'threshold' => 80,
                'parameters' => [
                    'window_minutes' => 15,
                    'min_calls' => 20,
                ],
                'action' => 'alert',
                'severity' => 'medium',
                'active' => true,
            ],
            [
                'name' => 'Off-Hours Traffic Alert',
                'type' => 'off_hours_traffic',
                'description' => 'Alert when significant traffic occurs during off hours (22:00-06:00)',
                'threshold' => 10,
                'parameters' => [
                    'off_hours_start' => '22:00',
                    'off_hours_end' => '06:00',
                    'min_calls_in_window' => 10,
                ],
                'action' => 'alert',
                'severity' => 'low',
                'active' => true,
            ],
            [
                'name' => 'Caller ID Manipulation Detection',
                'type' => 'caller_id_manipulation',
                'description' => 'Alert when too many different caller IDs are used from same source',
                'threshold' => 10,
                'parameters' => [
                    'window_minutes' => 60,
                ],
                'action' => 'alert',
                'severity' => 'medium',
                'active' => true,
            ],
            [
                'name' => 'Accelerated Consumption Alert',
                'type' => 'accelerated_consumption',
                'description' => 'Alert when minute consumption rate exceeds 300% of normal',
                'threshold' => 300,
                'parameters' => [
                    'comparison_period_days' => 7,
                ],
                'action' => 'alert',
                'severity' => 'high',
                'active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            FraudRule::updateOrCreate(
                ['type' => $rule['type'], 'customer_id' => null],
                $rule
            );
        }
    }
}
