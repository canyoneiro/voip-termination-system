<?php

namespace Tests\Unit\Services;

use App\Models\Carrier;
use App\Models\Cdr;
use App\Models\Customer;
use App\Models\FraudIncident;
use App\Models\FraudRule;
use App\Models\SystemSetting;
use App\Services\FraudDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FraudDetectionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FraudDetectionService $fraudService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fraudService = app(FraudDetectionService::class);

        // Enable fraud detection
        SystemSetting::updateOrCreate(
            ['category' => 'fraud', 'name' => 'detection_enabled'],
            ['value' => '1', 'type' => 'bool']
        );
    }

    protected function createFraudRule(array $attributes = []): FraudRule
    {
        return FraudRule::create(array_merge([
            'name' => 'Test Rule',
            'type' => 'traffic_spike',
            'severity' => 'medium',
            'threshold' => 100,
            'action' => 'alert',
            'parameters' => [],  // Required field
            'active' => true,
        ], $attributes));
    }

    public function test_calculate_risk_score_for_customer_with_no_incidents(): void
    {
        $customer = Customer::factory()->create();

        $score = $this->fraudService->calculateRiskScore($customer);

        $this->assertEquals(0, $score);
    }

    public function test_calculate_risk_score_increases_with_incidents(): void
    {
        $customer = Customer::factory()->create();
        $rule = $this->createFraudRule();

        FraudIncident::create([
            'fraud_rule_id' => $rule->id,
            'customer_id' => $customer->id,
            'type' => 'traffic_spike',
            'severity' => 'medium',
            'title' => 'Test Incident',
            'description' => 'Test',
            'status' => 'pending',
            'action_taken' => 'none',
        ]);

        $score = $this->fraudService->calculateRiskScore($customer);

        $this->assertEquals(15, $score); // Medium severity = 15 points
    }

    public function test_risk_score_capped_at_100(): void
    {
        $customer = Customer::factory()->create();
        $rule = $this->createFraudRule(['severity' => 'critical']);

        // Create multiple critical incidents (50 points each)
        for ($i = 0; $i < 5; $i++) {
            FraudIncident::create([
                'fraud_rule_id' => $rule->id,
                'customer_id' => $customer->id,
                'type' => 'traffic_spike',
                'severity' => 'critical',
                'title' => "Test Incident {$i}",
                'description' => 'Test',
                'status' => 'pending',
                'action_taken' => 'none',
            ]);
        }

        $score = $this->fraudService->calculateRiskScore($customer);

        // 5 incidents * 50 points = 250, but capped at 100
        $this->assertEquals(100, $score);
    }

    public function test_false_positive_incidents_are_not_counted(): void
    {
        $customer = Customer::factory()->create();
        $rule = $this->createFraudRule(['severity' => 'high']);

        FraudIncident::create([
            'fraud_rule_id' => $rule->id,
            'customer_id' => $customer->id,
            'type' => 'traffic_spike',
            'severity' => 'high',
            'title' => 'False Positive',
            'description' => 'Test',
            'status' => 'false_positive', // This should not count
            'action_taken' => 'none',
        ]);

        $score = $this->fraudService->calculateRiskScore($customer);

        $this->assertEquals(0, $score); // False positive not counted
    }

    public function test_get_stats_returns_correct_structure(): void
    {
        $from = now()->subDays(7)->toDateTimeString();
        $to = now()->toDateTimeString();

        $stats = $this->fraudService->getStats($from, $to);

        $this->assertArrayHasKey('total_incidents', $stats);
        $this->assertArrayHasKey('by_type', $stats);
        $this->assertArrayHasKey('by_severity', $stats);
        $this->assertArrayHasKey('by_status', $stats);
        $this->assertArrayHasKey('pending_count', $stats);
    }

    public function test_get_stats_counts_incidents_correctly(): void
    {
        $customer = Customer::factory()->create();
        $rule = $this->createFraudRule();

        FraudIncident::create([
            'fraud_rule_id' => $rule->id,
            'customer_id' => $customer->id,
            'type' => 'traffic_spike',
            'severity' => 'medium',
            'title' => 'Test 1',
            'description' => 'Test',
            'status' => 'pending',
            'action_taken' => 'none',
        ]);

        FraudIncident::create([
            'fraud_rule_id' => $rule->id,
            'customer_id' => $customer->id,
            'type' => 'wangiri',
            'severity' => 'high',
            'title' => 'Test 2',
            'description' => 'Test',
            'status' => 'resolved',
            'action_taken' => 'notified',
        ]);

        $from = now()->subDays(7)->toDateTimeString();
        $to = now()->addDay()->toDateTimeString();

        $stats = $this->fraudService->getStats($from, $to);

        $this->assertEquals(2, $stats['total_incidents']);
        $this->assertEquals(1, $stats['pending_count']);
        $this->assertArrayHasKey('traffic_spike', $stats['by_type']);
        $this->assertArrayHasKey('wangiri', $stats['by_type']);
    }
}
