<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'organization_id',
        'user_id',
        'number',
        'status',
        'total_amount',
        'total_products',
        'total_discount',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_products' => 'decimal:2',
        'total_discount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function giftCertificates(): BelongsToMany
    {
        return $this->belongsToMany(GiftCertificate::class, 'gift_certificate_order')
            ->withPivot('amount_applied')
            ->withTimestamps();
    }
}

