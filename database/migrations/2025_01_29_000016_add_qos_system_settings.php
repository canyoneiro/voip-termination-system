<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            // QoS Thresholds
            ['category' => 'qos', 'name' => 'mos_excellent_threshold', 'value' => '4.0', 'type' => 'string', 'description' => 'MOS score threshold for excellent quality'],
            ['category' => 'qos', 'name' => 'mos_good_threshold', 'value' => '3.5', 'type' => 'string', 'description' => 'MOS score threshold for good quality'],
            ['category' => 'qos', 'name' => 'mos_fair_threshold', 'value' => '3.0', 'type' => 'string', 'description' => 'MOS score threshold for fair quality'],
            ['category' => 'qos', 'name' => 'mos_poor_threshold', 'value' => '2.5', 'type' => 'string', 'description' => 'MOS score threshold for poor quality'],
            ['category' => 'qos', 'name' => 'pdd_warning_threshold', 'value' => '3000', 'type' => 'int', 'description' => 'PDD threshold (ms) to trigger warning'],
            ['category' => 'qos', 'name' => 'alert_on_poor_quality', 'value' => '1', 'type' => 'bool', 'description' => 'Send alert when call quality is poor or bad'],

            // Fraud Detection Settings
            ['category' => 'fraud', 'name' => 'high_cost_prefixes', 'value' => '900,901,902,905,803,806,807,118', 'type' => 'string', 'description' => 'Comma-separated premium prefixes'],
            ['category' => 'fraud', 'name' => 'traffic_spike_threshold', 'value' => '200', 'type' => 'int', 'description' => 'Traffic spike threshold (% over average)'],
            ['category' => 'fraud', 'name' => 'short_call_threshold', 'value' => '50', 'type' => 'int', 'description' => 'Number of short calls (<6s) in 5 min to trigger Wangiri alert'],
            ['category' => 'fraud', 'name' => 'unusual_destination_alert', 'value' => '1', 'type' => 'bool', 'description' => 'Alert on calls to never-before-seen destinations'],
            ['category' => 'fraud', 'name' => 'failure_rate_threshold', 'value' => '80', 'type' => 'int', 'description' => 'Failure rate threshold (%) to trigger alert'],
            ['category' => 'fraud', 'name' => 'off_hours_start', 'value' => '22:00', 'type' => 'string', 'description' => 'Off-hours start time (HH:MM)'],
            ['category' => 'fraud', 'name' => 'off_hours_end', 'value' => '06:00', 'type' => 'string', 'description' => 'Off-hours end time (HH:MM)'],
            ['category' => 'fraud', 'name' => 'caller_id_changes_threshold', 'value' => '10', 'type' => 'int', 'description' => 'Max different caller IDs in 1 hour before alert'],
            ['category' => 'fraud', 'name' => 'minutes_acceleration_threshold', 'value' => '300', 'type' => 'int', 'description' => 'Minutes consumption acceleration threshold (%)'],
            ['category' => 'fraud', 'name' => 'detection_enabled', 'value' => '1', 'type' => 'bool', 'description' => 'Enable fraud detection system'],
            ['category' => 'fraud', 'name' => 'auto_block_enabled', 'value' => '0', 'type' => 'bool', 'description' => 'Enable automatic blocking on critical fraud'],

            // LCR Settings
            ['category' => 'lcr', 'name' => 'routing_mode', 'value' => 'lcr', 'type' => 'string', 'description' => 'Routing mode: lcr (least cost) or priority'],
            ['category' => 'lcr', 'name' => 'fallback_to_priority', 'value' => '1', 'type' => 'bool', 'description' => 'Fallback to priority routing if no rates found'],
            ['category' => 'lcr', 'name' => 'default_billing_increment', 'value' => '1', 'type' => 'int', 'description' => 'Default billing increment in seconds'],
            ['category' => 'lcr', 'name' => 'default_min_duration', 'value' => '0', 'type' => 'int', 'description' => 'Default minimum billable duration in seconds'],

            // Report Settings
            ['category' => 'reports', 'name' => 'storage_path', 'value' => 'reports', 'type' => 'string', 'description' => 'Path for generated reports'],
            ['category' => 'reports', 'name' => 'retention_days', 'value' => '30', 'type' => 'int', 'description' => 'Days to keep generated reports'],
            ['category' => 'reports', 'name' => 'max_records_per_report', 'value' => '100000', 'type' => 'int', 'description' => 'Maximum records per report'],
        ];

        DB::table('system_settings')->insert($settings);
    }

    public function down(): void
    {
        DB::table('system_settings')
            ->whereIn('category', ['qos', 'fraud', 'lcr', 'reports'])
            ->delete();
    }
};
