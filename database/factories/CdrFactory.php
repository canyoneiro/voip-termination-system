<?php

namespace Database\Factories;

use App\Models\Cdr;
use App\Models\Customer;
use App\Models\Carrier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CdrFactory extends Factory
{
    protected $model = Cdr::class;

    public function definition(): array
    {
        $startTime = fake()->dateTimeBetween('-1 week', 'now');
        $answered = fake()->boolean(70);
        $duration = $answered ? fake()->numberBetween(10, 600) : 0;

        return [
            'uuid' => Str::uuid(),
            'call_id' => Str::uuid() . '@' . fake()->domainName(),
            'customer_id' => Customer::factory(),
            'carrier_id' => $answered ? Carrier::factory() : null,
            'source_ip' => fake()->ipv4(),
            'caller' => '+' . fake()->numerify('34#########'),
            'caller_original' => '+' . fake()->numerify('34#########'),
            'callee' => '+' . fake()->numerify('34#########'),
            'callee_original' => '+' . fake()->numerify('34#########'),
            'destination_ip' => $answered ? fake()->ipv4() : null,
            'start_time' => $startTime,
            'progress_time' => $answered ? (clone $startTime)->modify('+1 second') : null,
            'answer_time' => $answered ? (clone $startTime)->modify('+2 seconds') : null,
            'end_time' => (clone $startTime)->modify('+' . ($duration + 2) . ' seconds'),
            'duration' => $duration,
            'billable_duration' => $duration,
            'pdd' => $answered ? fake()->numberBetween(500, 3000) : null,
            'sip_code' => $answered ? 200 : fake()->randomElement([404, 486, 503, 408]),
            'sip_reason' => $answered ? 'OK' : fake()->randomElement(['Not Found', 'Busy Here', 'Service Unavailable', 'Request Timeout']),
            'hangup_cause' => $answered ? fake()->randomElement(['caller', 'callee']) : 'failed',
            'codec_used' => $answered ? fake()->randomElement(['G729', 'PCMA', 'PCMU']) : null,
        ];
    }

    public function answered(): static
    {
        return $this->state(function (array $attributes) {
            $duration = fake()->numberBetween(30, 600);
            return [
                'sip_code' => 200,
                'sip_reason' => 'OK',
                'duration' => $duration,
                'billable_duration' => $duration,
                'hangup_cause' => fake()->randomElement(['caller', 'callee']),
            ];
        });
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'carrier_id' => null,
            'answer_time' => null,
            'duration' => 0,
            'billable_duration' => 0,
            'sip_code' => fake()->randomElement([404, 486, 503, 408]),
            'sip_reason' => fake()->randomElement(['Not Found', 'Busy Here', 'Service Unavailable', 'Request Timeout']),
            'hangup_cause' => 'failed',
            'codec_used' => null,
        ]);
    }
}
