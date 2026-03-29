<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'user_id',
        'customer_email',
        'customer_name',
        'customer_phone',
        'amount',
        'commission',
        'payment_method',
        'payment_system',
        'status',
        'payment_data',
        'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission' => 'decimal:2',
        'payment_data' => 'array',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('paid_at', [$from, $to]);
    }

    // Accessors
    public function getTotalWithCommissionAttribute(): float
    {
        return $this->amount + $this->commission;
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    // Methods
    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'paid_at' => now(),
        ]);
    }

    public function fail(): void
    {
        $this->update(['status' => 'failed']);
    }

    public function refund(): void
    {
        $this->update(['status' => 'refunded']);
    }
}
