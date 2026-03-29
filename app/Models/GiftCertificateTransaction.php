<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftCertificateTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'gift_certificate_id',
        'type',
        'amount',
        'description',
        'order_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public const TYPE_ISSUE = 'issue';
    public const TYPE_REDEEM = 'redeem';
    public const TYPE_ADJUST = 'adjust';

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(GiftCertificate::class, 'gift_certificate_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

