<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BillingTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'customer_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'reference',
        'cdr_id',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'balance_before' => 'decimal:4',
        'balance_after' => 'decimal:4',
        'metadata' => 'array',
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

    public function cdr(): BelongsTo
    {
        return $this->belongsTo(Cdr::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getIsPositiveAttribute(): bool
    {
        return in_array($this->type, ['credit', 'refund']);
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'credit' => 'green',
            'refund' => 'blue',
            'debit' => 'red',
            'call_charge' => 'orange',
            'adjustment' => 'purple',
            default => 'slate',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'credit' => 'Recarga',
            'refund' => 'Reembolso',
            'debit' => 'Cargo',
            'call_charge' => 'Llamada',
            'adjustment' => 'Ajuste',
            default => $this->type,
        };
    }
}
