<?php

namespace Tests\Unit\Services;

use App\Models\Cdr;
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

    public function test_calculate_mos_score_excellent(): void
    {
        // Excellent conditions: low latency, no jitter, no packet loss
        $mos = $this->qosService->calculateMosScore(
            latency: 50,
            jitter: 5,
            packetLoss: 0
        );

        $this->assertGreaterThanOrEqual(4.0, $mos);
        $this->assertLessThanOrEqual(5.0, $mos);
    }

    public function test_calculate_mos_score_poor(): void
    {
        // Poor conditions: high latency, high jitter, packet loss
        $mos = $this->qosService->calculateMosScore(
            latency: 400,
            jitter: 50,
            packetLoss: 5
        );

        $this->assertLessThan(3.0, $mos);
    }

    public function test_quality_rating_excellent(): void
    {
        $rating = $this->qosService->getQualityRating(4.5);
        $this->assertEquals('excellent', $rating);
    }

    public function test_quality_rating_good(): void
    {
        $rating = $this->qosService->getQualityRating(3.8);
        $this->assertEquals('good', $rating);
    }

    public function test_quality_rating_fair(): void
    {
        $rating = $this->qosService->getQualityRating(3.2);
        $this->assertEquals('fair', $rating);
    }

    public function test_quality_rating_poor(): void
    {
        $rating = $this->qosService->getQualityRating(2.8);
        $this->assertEquals('poor', $rating);
    }

    public function test_quality_rating_bad(): void
    {
        $rating = $this->qosService->getQualityRating(2.0);
        $this->assertEquals('bad', $rating);
    }

    public function test_get_quality_distribution(): void
    {
        // Create QoS metrics with different ratings
        $cdr1 = Cdr::factory()->answered()->create();
        $cdr2 = Cdr::factory()->answered()->create();
        $cdr3 = Cdr::factory()->answered()->create();

        QosMetric::create([
            'cdr_id' => $cdr1->id,
            'mos_score' => 4.5,
            'quality_rating' => 'excellent',
            'pdd' => 500,
        ]);

        QosMetric::create([
            'cdr_id' => $cdr2->id,
            'mos_score' => 3.2,
            'quality_rating' => 'fair',
            'pdd' => 1500,
        ]);

        QosMetric::create([
            'cdr_id' => $cdr3->id,
            'mos_score' => 2.5,
            'quality_rating' => 'poor',
            'pdd' => 3000,
        ]);

        $distribution = $this->qosService->getQualityDistribution(now()->subDay(), now());

        $this->assertArrayHasKey('excellent', $distribution);
        $this->assertArrayHasKey('fair', $distribution);
        $this->assertArrayHasKey('poor', $distribution);
        $this->assertEquals(1, $distribution['excellent']);
        $this->assertEquals(1, $distribution['fair']);
        $this->assertEquals(1, $distribution['poor']);
    }
}
