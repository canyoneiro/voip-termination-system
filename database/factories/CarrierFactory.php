<?php

namespace Database\Factories;

use App\Models\Carrier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CarrierFactory extends Factory
{
    protected $model = Carrier::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => fake()->company() . ' Carrier',
            'host' => fake()->domainName(),
            'port' => 5060,
            'transport' => fake()->randomElement(['udp', 'tcp', 'tls']),
            'codecs' => 'G729,PCMA,PCMU',
            'priority' => fake()->numberBetween(1, 10),
            'weight' => fake()->numberBetween(50, 100),
            'tech_prefix' => fake()->optional()->numerify('##'),
            'strip_digits' => fake()->numberBetween(0, 3),
            'max_cps' => fake()->numberBetween(5, 30),
            'max_channels' => fake()->numberBetween(20, 100),
            'state' => 'active',
            'daily_calls' => 0,
            'daily_minutes' => 0,
            'daily_failed' => 0,
            'failover_count' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'inactive',
        ]);
    }

    public function probing(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'probing',
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'disabled',
        ]);
    }
}
