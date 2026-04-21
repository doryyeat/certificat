<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftCertificateRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'gift_certificate_id',
        'organization_id',
        'store_id',
        'cashier_user_id',
        'amount',
        'qr_data',
        'verification_data',
        'ip_address',
        'user_agent',
        'redeemed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verification_data' => 'array',
        'redeemed_at' => 'datetime',
    ];

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(GiftCertificate::class, 'gift_certificate_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_user_id');
    }
}

