<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Cdr;
use App\Models\QosDailyStat;
use App\Models\QosMetric;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;

class QosService
{
    protected array $codecQuality = [
        'PCMU' => 4.5,
        'PCMA' => 4.5,
        'G722' => 4.0,
        'G729' => 3.92,
        'GSM' => 3.5,
        'OPUS' => 4.5,
        'SPEEX' => 3.8,
        'iLBC' => 3.7,
    ];

    protected ?array $thresholds = null;

    /**
     * Process QoS metrics for a completed CDR
     */
    public function processCallQos(Cdr $cdr): ?QosMetric
    {
        // Only process answered calls
        if (!$cdr->isAnswered()) {
            return null;
        }

        $mos = $this->calculateMos($cdr);
        $rating = $this->calculateQualityRating($mos);

        $metric = QosMetric::create([
            'cdr_id' => $cdr->id,
            'customer_id' => $cdr->customer_id,
            'carrier_id' => $cdr->carrier_id,
            'mos_score' => $mos,
            'pdd' => $cdr->pdd,
            'jitter' => null, // Would need RTP stats
            'packet_loss' => null, // Would need RTP stats
            'rtt' => null,
            'codec_used' => $cdr->codec_used,
            'quality_rating' => $rating,
            'call_time' => $cdr->start_time,
        ]);

        // Check if we should alert on poor quality
        $this->checkQualityAlerts($metric);

        return $metric;
    }

    /**
     * Calculate MOS score (E-model simplified)
     */
    public function calculateMos(Cdr $cdr): float
    {
        // Base score from codec
        $codec = strtoupper($cdr->codec_used ?? 'PCMA');
        $baseScore = $this->codecQuality[$codec] ?? 3.5;

        // PDD penalty (longer PDD = worse quality perception)
        $pddPenalty = 0;
        if ($cdr->pdd) {
            // Penalize if PDD > 150ms
            $pddPenalty = max(0, ($cdr->pdd - 150) / 1000);
            $pddPenalty = min($pddPenalty, 1.0); // Cap penalty
        }

        // Duration factor (very short calls might indicate issues)
        $durationPenalty = 0;
        if ($cdr->duration < 10) {
            $durationPenalty = 0.2;
        }

        $mos = $baseScore - $pddPenalty - $durationPenalty;

        // Ensure MOS is within valid range
        return max(1.0, min(5.0, round($mos, 2)));
    }

    /**
     * Determine quality rating based on MOS score
     */
    public function calculateQualityRating(float $mos): string
    {
        $thresholds = $this->getThresholds();

        if ($mos >= $thresholds['excellent']) {
            return 'excellent';
        }
        if ($mos >= $thresholds['good']) {
            return 'good';
        }
        if ($mos >= $thresholds['fair']) {
            return 'fair';
        }
        if ($mos >= $thresholds['poor']) {
            return 'poor';
        }
        return 'bad';
    }

    /**
     * Check if alerts should be generated
     */
    public function checkQualityAlerts(QosMetric $metric): void
    {
        $thresholds = $this->getThresholds();

        if (!$thresholds['alert_on_poor']) {
            return;
        }

        if (in_array($metric->quality_rating, ['poor', 'bad'])) {
            $this->createQualityAlert($metric);
        }
    }

    /**
     * Create quality degradation alert
     */
    protected function createQualityAlert(QosMetric $metric): void
    {
        $severity = $metric->quality_rating === 'bad' ? 'critical' : 'warning';

        Alert::create([
            'type' => 'qos_degradation',
            'severity' => $severity,
            'source_type' => 'customer',
            'source_id' => $metric->customer_id,
            'source_name' => $metric->customer?->name,
            'title' => "Call quality degradation detected",
            'message' => sprintf(
                "Call to %s had MOS score %.2f (%s). Carrier: %s, PDD: %dms",
                $metric->cdr?->callee ?? 'unknown',
                $metric->mos_score,
                $metric->quality_rating,
                $metric->carrier?->name ?? 'unknown',
                $metric->pdd ?? 0
            ),
            'metadata' => [
                'cdr_id' => $metric->cdr_id,
                'carrier_id' => $metric->carrier_id,
                'mos_score' => $metric->mos_score,
                'pdd' => $metric->pdd,
                'codec' => $metric->codec_used,
            ],
        ]);
    }

    /**
     * Calculate daily QoS statistics
     */
    public function calculateDailyStats($date, ?int $customerId = null, ?int $carrierId = null): QosDailyStat
    {
        $query = QosMetric::whereDate('call_time', $date);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }
        if ($carrierId) {
            $query->where('carrier_id', $carrierId);
        }

        $stats = $query->selectRaw('
            COUNT(*) as measured_calls,
            AVG(mos_score) as avg_mos,
            MIN(mos_score) as min_mos,
            MAX(mos_score) as max_mos,
            AVG(pdd) as avg_pdd,
            MIN(pdd) as min_pdd,
            MAX(pdd) as max_pdd,
            AVG(jitter) as avg_jitter,
            AVG(packet_loss) as avg_packet_loss,
            SUM(CASE WHEN quality_rating = "excellent" THEN 1 ELSE 0 END) as excellent_count,
            SUM(CASE WHEN quality_rating = "good" THEN 1 ELSE 0 END) as good_count,
            SUM(CASE WHEN quality_rating = "fair" THEN 1 ELSE 0 END) as fair_count,
            SUM(CASE WHEN quality_rating = "poor" THEN 1 ELSE 0 END) as poor_count,
            SUM(CASE WHEN quality_rating = "bad" THEN 1 ELSE 0 END) as bad_count
        ')->first();

        // Count total CDRs for the period
        $totalCalls = Cdr::whereDate('start_time', $date)
            ->where('sip_code', 200)
            ->when($customerId, fn($q) => $q->where('customer_id', $customerId))
            ->when($carrierId, fn($q) => $q->where('carrier_id', $carrierId))
            ->count();

        return QosDailyStat::updateOrCreate(
            [
                'date' => $date,
                'customer_id' => $customerId,
                'carrier_id' => $carrierId,
            ],
            [
                'total_calls' => $totalCalls,
                'measured_calls' => $stats->measured_calls ?? 0,
                'avg_mos' => $stats->avg_mos,
                'min_mos' => $stats->min_mos,
                'max_mos' => $stats->max_mos,
                'avg_pdd' => $stats->avg_pdd ? (int) $stats->avg_pdd : null,
                'min_pdd' => $stats->min_pdd,
                'max_pdd' => $stats->max_pdd,
                'avg_jitter' => $stats->avg_jitter,
                'avg_packet_loss' => $stats->avg_packet_loss,
                'excellent_count' => $stats->excellent_count ?? 0,
                'good_count' => $stats->good_count ?? 0,
                'fair_count' => $stats->fair_count ?? 0,
                'poor_count' => $stats->poor_count ?? 0,
                'bad_count' => $stats->bad_count ?? 0,
            ]
        );
    }

    /**
     * Get real-time QoS metrics
     */
    public function getRealtimeQos(int $hours = 1): array
    {
        $from = now()->subHours($hours);

        $metrics = QosMetric::where('call_time', '>=', $from)
            ->selectRaw('
                COUNT(*) as total_calls,
                AVG(mos_score) as avg_mos,
                MIN(mos_score) as min_mos,
                MAX(mos_score) as max_mos,
                AVG(pdd) as avg_pdd,
                SUM(CASE WHEN quality_rating IN ("poor", "bad") THEN 1 ELSE 0 END) as poor_calls
            ')
            ->first();

        return [
            'total_calls' => $metrics->total_calls ?? 0,
            'avg_mos' => $metrics->avg_mos ? round($metrics->avg_mos, 2) : null,
            'min_mos' => $metrics->min_mos ? round($metrics->min_mos, 2) : null,
            'max_mos' => $metrics->max_mos ? round($metrics->max_mos, 2) : null,
            'avg_pdd' => $metrics->avg_pdd ? (int) $metrics->avg_pdd : null,
            'poor_calls' => $metrics->poor_calls ?? 0,
            'poor_percentage' => $metrics->total_calls > 0
                ? round(($metrics->poor_calls / $metrics->total_calls) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get QoS trends for a period
     */
    public function getTrends(string $from, string $to, string $granularity = 'hour'): array
    {
        $format = match($granularity) {
            'hour' => '%Y-%m-%d %H:00',
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d %H:00',
        };

        return QosMetric::whereBetween('call_time', [$from, $to])
            ->selectRaw("
                DATE_FORMAT(call_time, '{$format}') as period,
                COUNT(*) as calls,
                AVG(mos_score) as avg_mos,
                AVG(pdd) as avg_pdd,
                SUM(CASE WHEN quality_rating IN ('poor', 'bad') THEN 1 ELSE 0 END) as poor_calls
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(function ($row) {
                return [
                    'period' => $row->period,
                    'calls' => $row->calls,
                    'avg_mos' => $row->avg_mos ? round($row->avg_mos, 2) : null,
                    'avg_pdd' => $row->avg_pdd ? (int) $row->avg_pdd : null,
                    'poor_percentage' => $row->calls > 0
                        ? round(($row->poor_calls / $row->calls) * 100, 2)
                        : 0,
                ];
            })
            ->toArray();
    }

    /**
     * Get QoS by carrier
     */
    public function getByCarrier(string $from, string $to): array
    {
        return QosMetric::with('carrier')
            ->whereBetween('call_time', [$from, $to])
            ->whereNotNull('carrier_id')
            ->selectRaw('
                carrier_id,
                COUNT(*) as calls,
                AVG(mos_score) as avg_mos,
                AVG(pdd) as avg_pdd,
                SUM(CASE WHEN quality_rating IN ("poor", "bad") THEN 1 ELSE 0 END) as poor_calls
            ')
            ->groupBy('carrier_id')
            ->orderByDesc('calls')
            ->get()
            ->map(function ($row) {
                return [
                    'carrier_id' => $row->carrier_id,
                    'carrier_name' => $row->carrier?->name ?? 'Unknown',
                    'calls' => $row->calls,
                    'avg_mos' => $row->avg_mos ? round($row->avg_mos, 2) : null,
                    'avg_pdd' => $row->avg_pdd ? (int) $row->avg_pdd : null,
                    'poor_percentage' => $row->calls > 0
                        ? round(($row->poor_calls / $row->calls) * 100, 2)
                        : 0,
                ];
            })
            ->toArray();
    }

    /**
     * Get thresholds from settings
     */
    protected function getThresholds(): array
    {
        if ($this->thresholds === null) {
            $settings = SystemSetting::where('category', 'qos')
                ->pluck('value', 'name')
                ->toArray();

            $this->thresholds = [
                'excellent' => (float) ($settings['mos_excellent_threshold'] ?? 4.0),
                'good' => (float) ($settings['mos_good_threshold'] ?? 3.5),
                'fair' => (float) ($settings['mos_fair_threshold'] ?? 3.0),
                'poor' => (float) ($settings['mos_poor_threshold'] ?? 2.5),
                'pdd_warning' => (int) ($settings['pdd_warning_threshold'] ?? 3000),
                'alert_on_poor' => (bool) ($settings['alert_on_poor_quality'] ?? true),
            ];
        }

        return $this->thresholds;
    }
}
