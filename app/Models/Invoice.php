<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'customer_id',
        'invoice_number',
        'period_start',
        'period_end',
        'issue_date',
        'due_date',
        'status',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total',
        'paid_amount',
        'currency',
        'notes',
        'sent_at',
        'paid_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:4',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:4',
        'total' => 'decimal:4',
        'paid_amount' => 'decimal:4',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->invoice_number)) {
                $model->invoice_number = self::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $lastInvoice = self::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $sequence = $lastInvoice ? ((int) substr($lastInvoice->invoice_number, -6) + 1) : 1;

        return sprintf('INV-%s-%06d', $year, $sequence);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function getBalanceDueAttribute(): float
    {
        return max(0, $this->total - $this->paid_amount);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid' || $this->balance_due <= 0;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'paid' && $this->due_date && $this->due_date->endOfDay()->isPast();
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'slate',
            'sent' => 'blue',
            'paid' => 'green',
            'overdue' => 'red',
            'cancelled' => 'gray',
            default => 'slate',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Borrador',
            'sent' => 'Enviada',
            'paid' => 'Pagada',
            'overdue' => 'Vencida',
            'cancelled' => 'Cancelada',
            default => $this->status,
        };
    }

    public function recalculateTotals(): void
    {
        $this->subtotal = $this->items()->sum('total');
        $this->tax_amount = $this->subtotal * ($this->tax_rate / 100);
        $this->total = $this->subtotal + $this->tax_amount;
        $this->save();
    }
}
