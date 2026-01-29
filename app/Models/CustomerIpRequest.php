<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CustomerIpRequest extends Model
{
    protected $fillable = [
        'uuid',
        'customer_id',
        'customer_user_id',
        'ip_address',
        'description',
        'justification',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public const STATUSES = [
        'pending' => 'Pending Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerUser(): BelongsTo
    {
        return $this->belongsTo(CustomerUser::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function approve(int $userId, string $notes = null): CustomerIp
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        // Create the actual IP authorization
        return CustomerIp::create([
            'customer_id' => $this->customer_id,
            'ip_address' => $this->ip_address,
            'description' => $this->description,
            'active' => true,
        ]);
    }

    public function reject(int $userId, string $notes = null): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }
}
