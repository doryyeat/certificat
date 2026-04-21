<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchasedCertificateTransaction extends Model
{
    protected $table = 'purchased_certificate_transactions';

    protected $fillable = [
        'pc_id',
        'type',
        'amount',
        'description',
        'balance_after',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    const TYPE_ISSUE = 'issue';
    const TYPE_REDEEM = 'redeem';

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(PurchasedCertificate::class, 'pc_id');
    }
}
