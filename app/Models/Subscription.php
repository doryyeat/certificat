<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'plan',
        'status',
        'price',
        'certificates_limit',
        'commission_rate',
        'analytics_enabled',
        'api_enabled',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'canceled_at'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'analytics_enabled' => 'boolean',
        'api_enabled' => 'boolean',
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('ends_at', '<=', now());
    }

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' &&
            ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function getIsTrialAttribute(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    public function getDaysLeftAttribute(): ?int
    {
        if ($this->ends_at === null) {
            return null;
        }

        return now()->diffInDays($this->ends_at, false);
    }

    // Methods
    public function cancel(): void
    {
        $this->update([
            'status' => 'canceled',
            'canceled_at' => now(),
            'ends_at' => now(),
        ]);
    }

    public function renew(): void
    {
        $this->update([
            'status' => 'active',
            'canceled_at' => null,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);
    }
}
