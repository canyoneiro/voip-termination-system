<?php

namespace Tests\Unit\Services;

use App\Models\Carrier;
use App\Models\Cdr;
use App\Models\Customer;
use App\Models\QosMetric;
use App\Services\QosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QosServiceTest extends TestCase
{
    use RefreshDatabase;

    protected QosService $qosService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->qosService = app(QosService::class);
    }

    public function test_calculate_mos_from_cdr_with_good_codec(): void
    {
        $customer = Customer::factory()->create();
        $carrier = Carrier::factory()->create();

        $cdr = Cdr::factory()->answered()->create([
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'codec_used' => 'PCMA',
            'pdd' => 100, // Low PDD
            'duration' => 60,
        ]);

        $mos = $this->qosService->calculateMos($cdr);

        // PCMA codec with low PDD should give good MOS
        $this->assertGreaterThanOrEqual(4.0, $mos);
        $this->assertLessThanOrEqual(5.0, $mos);
    }

    public function test_calculate_mos_from_cdr_with_high_pdd(): void
    {
        $customer = Customer::factory()->create();
        $carrier = Carrier::factory()->create();

        $cdr = Cdr::factory()->answered()->create([
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'codec_used' => 'PCMA',
            'pdd' => 1200, // High PDD
            'duration' => 60,
        ]);

        $mos = $this->qosService->calculateMos($cdr);

        // High PDD should reduce MOS score
        $this->assertLessThan(4.5, $mos);
    }

    public function test_calculate_quality_rating_excellent(): void
    {
        $rating = $this->qosService->calculateQualityRating(4.5);
        $this->assertEquals('excellent', $rating);
    }

    public function test_calculate_quality_rating_good(): void
    {
        $rating = $this->qosService->calculateQualityRating(3.7);
        $this->assertEquals('good', $rating);
    }

    public function test_calculate_quality_rating_fair(): void
    {
        $rating = $this->qosService->calculateQualityRating(3.2);
        $this->assertEquals('fair', $rating);
    }

    public function test_calculate_quality_rating_poor(): void
    {
        $rating = $this->qosService->calculateQualityRating(2.7);
        $this->assertEquals('poor', $rating);
    }

    public function test_calculate_quality_rating_bad(): void
    {
        $rating = $this->qosService->calculateQualityRating(2.0);
        $this->assertEquals('bad', $rating);
    }

    public function test_process_call_qos_creates_metric(): void
    {
        $customer = Customer::factory()->create();
        $carrier = Carrier::factory()->create();

        $cdr = Cdr::factory()->answered()->create([
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'codec_used' => 'G729',
            'pdd' => 200,
            'duration' => 120,
            'start_time' => now(),
        ]);

        $metric = $this->qosService->processCallQos($cdr);

        $this->assertNotNull($metric);
        $this->assertInstanceOf(QosMetric::class, $metric);
        $this->assertEquals($cdr->id, $metric->cdr_id);
        $this->assertEquals($customer->id, $metric->customer_id);
        $this->assertEquals($carrier->id, $metric->carrier_id);
        $this->assertEquals('G729', $metric->codec_used);
        $this->assertNotNull($metric->mos_score);
        $this->assertNotNull($metric->quality_rating);
    }

    public function test_process_call_qos_returns_null_for_unanswered_calls(): void
    {
        $customer = Customer::factory()->create();

        $cdr = Cdr::factory()->failed()->create([
            'customer_id' => $customer->id,
            'sip_code' => 486,
            'duration' => 0,
        ]);

        $metric = $this->qosService->processCallQos($cdr);

        $this->assertNull($metric);
    }

    public function test_get_realtime_qos(): void
    {
        $customer = Customer::factory()->create();
        $carrier = Carrier::factory()->create();

        // Create CDRs first
        $cdr1 = Cdr::factory()->answered()->create([
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
        ]);

        $cdr2 = Cdr::factory()->answered()->create([
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
        ]);

        // Create some QOS metrics
        QosMetric::create([
            'cdr_id' => $cdr1->id,
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'mos_score' => 4.2,
            'pdd' => 150,
            'quality_rating' => 'excellent',
            'call_time' => now(),
        ]);

        QosMetric::create([
            'cdr_id' => $cdr2->id,
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'mos_score' => 3.5,
            'pdd' => 250,
            'quality_rating' => 'good',
            'call_time' => now(),
        ]);

        $qos = $this->qosService->getRealtimeQos(1);

        $this->assertArrayHasKey('total_calls', $qos);
        $this->assertArrayHasKey('avg_mos', $qos);
        $this->assertArrayHasKey('avg_pdd', $qos);
        $this->assertArrayHasKey('poor_calls', $qos);
        $this->assertEquals(2, $qos['total_calls']);
        $this->assertEquals(3.85, $qos['avg_mos']);
    }
}
