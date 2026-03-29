<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'code',
        'amount',
        'balance',
        'currency',
        'status',
        'expires_at',
        'recipient_name',
        'recipient_email',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_REDEEMED = 'redeemed';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public function transactions(): HasMany
    {
        return $this->hasMany(GiftCertificateTransaction::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'gift_certificate_order')
            ->withPivot('amount_applied')
            ->withTimestamps();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id');
    }
}

