<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CertificateRedemption extends Model
{
    use HasFactory;

    protected $table = 'certificate_redemptions';

    protected $fillable = [
        'certificate_id',
        'business_id',
        'location_id',
        'amount',
        'pin_code',
        'qr_data',
        'verification_data',
        'ip_address',
        'user_agent',
        'redeemed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verification_data' => 'array',
        'redeemed_at' => 'datetime',
    ];

    // Relationships
    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // Scopes
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('redeemed_at', $date);
    }

    public function scopeByBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2, '.', ' ') . ' ₽';
    }
}
