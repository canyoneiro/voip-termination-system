<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'total',
        'period',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:4',
        'total' => 'decimal:4',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->total = $model->quantity * $model->unit_price;
        });

        static::saved(function ($model) {
            $model->invoice->recalculateTotals();
        });

        static::deleted(function ($model) {
            $model->invoice->recalculateTotals();
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
