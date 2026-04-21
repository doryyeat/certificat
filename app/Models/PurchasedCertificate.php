<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasedCertificate extends Model
{
    use HasFactory;

    protected $table = 'purchased_certificates';

    protected $fillable = [
        'organization_id',
        'store_id',
        'template_id',
        'source_certificate_id',
        'code',
        'title',
        'amount',
        'balance',
        'currency',
        'category',
        'validity_days',
        'terms_of_use',
        'status',
        'expires_at',
        'recipient_name',
        'recipient_email',
        'notes',
        'created_by',
        'sold_at',
        'sold_order_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'expires_at' => 'datetime',
        'sold_at' => 'datetime',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_REDEEMED = 'redeemed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    // Связь с транзакциями (используем pc_id)
    public function transactions()
    {
        return $this->hasMany(PurchasedCertificateTransaction::class, 'pc_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function sourceCertificate()
    {
        return $this->belongsTo(GiftCertificate::class, 'source_certificate_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'sold_order_id');
    }

    // Метод для списания средств
    public function redeem(float $amount, string $description = null): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            throw new \RuntimeException('Certificate is not active');
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            $this->status = self::STATUS_EXPIRED;
            $this->save();
            throw new \RuntimeException('Certificate has expired');
        }

        if ($amount > $this->balance) {
            throw new \RuntimeException('Amount exceeds balance');
        }

        $this->balance -= $amount;

        if ($this->balance <= 0) {
            $this->status = self::STATUS_REDEEMED;
        }

        $this->save();

        // Создаем транзакцию
        $this->transactions()->create([
            'type' => 'redeem',
            'amount' => $amount,
            'description' => $description ?? 'Redeemed',
            'balance_after' => $this->balance,
        ]);

        return true;
    }
}
