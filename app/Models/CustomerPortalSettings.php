<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPortalSettings extends Model
{
    protected $fillable = [
        'customer_id',
        'portal_enabled',
        'allow_api_tokens',
        'allow_ip_requests',
        'allow_webhook_management',
        'show_billing_summary',
        'show_carrier_names',
        'show_sip_traces',
        'show_cost_info',
        'cdr_retention_days',
        'max_api_tokens',
        'max_users',
        'allowed_features',
        'custom_logo',
        'custom_theme',
    ];

    protected $casts = [
        'portal_enabled' => 'boolean',
        'allow_api_tokens' => 'boolean',
        'allow_ip_requests' => 'boolean',
        'allow_webhook_management' => 'boolean',
        'show_billing_summary' => 'boolean',
        'show_carrier_names' => 'boolean',
        'show_sip_traces' => 'boolean',
        'show_cost_info' => 'boolean',
        'cdr_retention_days' => 'integer',
        'max_api_tokens' => 'integer',
        'max_users' => 'integer',
        'allowed_features' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isFeatureAllowed(string $feature): bool
    {
        if ($this->allowed_features === null) {
            return true; // All features allowed by default
        }

        return in_array($feature, $this->allowed_features);
    }

    public function canAddMoreUsers(): bool
    {
        $currentCount = $this->customer->portalUsers()->count();
        return $currentCount < $this->max_users;
    }

    public function canAddMoreApiTokens(): bool
    {
        $currentCount = $this->customer->apiTokens()->count();
        return $currentCount < $this->max_api_tokens;
    }

    public static function getDefaults(): array
    {
        return [
            'portal_enabled' => false,
            'allow_api_tokens' => true,
            'allow_ip_requests' => true,
            'allow_webhook_management' => false,
            'show_billing_summary' => true,
            'show_carrier_names' => false,
            'show_sip_traces' => false,
            'show_cost_info' => false,
            'cdr_retention_days' => 90,
            'max_api_tokens' => 5,
            'max_users' => 5,
        ];
    }
}
