<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => fake()->company(),
            'company' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'max_channels' => fake()->numberBetween(5, 50),
            'max_cps' => fake()->numberBetween(1, 20),
            'max_daily_minutes' => fake()->optional()->numberBetween(100, 10000),
            'max_monthly_minutes' => fake()->optional()->numberBetween(1000, 100000),
            'used_daily_minutes' => 0,
            'used_monthly_minutes' => 0,
            'active' => true,
            'notes' => fake()->optional()->sentence(),
            'alert_email' => fake()->optional()->safeEmail(),
            'notify_low_balance' => true,
            'notify_channels_warning' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    public function withUsage(int $dailyMinutes, int $monthlyMinutes): static
    {
        return $this->state(fn (array $attributes) => [
            'used_daily_minutes' => $dailyMinutes,
            'used_monthly_minutes' => $monthlyMinutes,
        ]);
    }
}
