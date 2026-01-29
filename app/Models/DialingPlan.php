<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DialingPlan extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'default_action',
        'block_premium',
        'active',
    ];

    protected $casts = [
        'block_premium' => 'boolean',
        'active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function rules(): HasMany
    {
        return $this->hasMany(DialingPlanRule::class)->orderBy('priority');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Check if a number is allowed by this dialing plan
     */
    public function isNumberAllowed(string $number, ?DestinationPrefix $prefix = null): array
    {
        $number = ltrim($number, '+');

        // Check premium blocking first
        if ($this->block_premium && $prefix && $prefix->is_premium) {
            return [
                'allowed' => false,
                'reason' => 'premium_blocked',
                'message' => 'Premium destinations are blocked',
            ];
        }

        // Get active rules ordered by priority
        $rules = $this->rules()->where('active', true)->orderBy('priority')->get();

        foreach ($rules as $rule) {
            if ($rule->matchesNumber($number)) {
                return [
                    'allowed' => $rule->type === 'allow',
                    'reason' => $rule->type === 'allow' ? 'rule_allow' : 'rule_deny',
                    'message' => $rule->description ?? "Matched rule: {$rule->pattern}",
                    'rule_id' => $rule->id,
                ];
            }
        }

        // No rule matched, use default action
        return [
            'allowed' => $this->default_action === 'allow',
            'reason' => 'default_action',
            'message' => $this->default_action === 'allow'
                ? 'Allowed by default'
                : 'Denied by default (no matching rule)',
        ];
    }

    /**
     * Get summary of allowed/denied patterns
     */
    public function getRulesSummaryAttribute(): array
    {
        $rules = $this->rules()->where('active', true)->get();

        return [
            'allow' => $rules->where('type', 'allow')->pluck('pattern')->toArray(),
            'deny' => $rules->where('type', 'deny')->pluck('pattern')->toArray(),
            'default' => $this->default_action,
            'block_premium' => $this->block_premium,
        ];
    }
}
