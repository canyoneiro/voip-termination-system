<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DialingPlanRule extends Model
{
    protected $fillable = [
        'dialing_plan_id',
        'type',
        'pattern',
        'description',
        'priority',
        'active',
    ];

    protected $casts = [
        'priority' => 'integer',
        'active' => 'boolean',
    ];

    public function dialingPlan(): BelongsTo
    {
        return $this->belongsTo(DialingPlan::class);
    }

    /**
     * Check if a number matches this rule's pattern
     * Supports wildcards: 34* matches 34xxx, 346? matches 3460-3469
     */
    public function matchesNumber(string $number): bool
    {
        $number = ltrim($number, '+');
        $pattern = $this->pattern;

        // Exact match
        if ($pattern === $number) {
            return true;
        }

        // Wildcard match with *
        if (str_contains($pattern, '*')) {
            $regex = '/^' . str_replace(['*', '?'], ['.*', '.'], preg_quote($pattern, '/')) . '$/';
            $regex = str_replace('\.\*', '.*', $regex);
            $regex = str_replace('\.', '.', $regex);
            return (bool) preg_match($regex, $number);
        }

        // Prefix match (pattern is a prefix of number)
        if (str_starts_with($number, $pattern)) {
            return true;
        }

        return false;
    }

    /**
     * Get human-readable type
     */
    public function getTypeColorAttribute(): string
    {
        return $this->type === 'allow' ? 'success' : 'danger';
    }

    /**
     * Get type badge HTML
     */
    public function getTypeBadgeAttribute(): string
    {
        $color = $this->type === 'allow' ? 'success' : 'danger';
        $text = $this->type === 'allow' ? 'ALLOW' : 'DENY';
        return "<span class=\"badge bg-{$color}\">{$text}</span>";
    }
}
