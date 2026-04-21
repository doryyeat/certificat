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

    public const CATEGORY_HORECA = 'horeca';

    public const CATEGORY_RETAIL = 'retail';

    public const CATEGORY_SERVICES = 'services';

    public const CATEGORY_SPORT = 'sport';

    public const CATEGORY_ENTERTAINMENT = 'entertainment';

    public const CATEGORY_EDUCATION = 'education';

    public const CATEGORIES = [
        self::CATEGORY_HORECA,
        self::CATEGORY_RETAIL,
        self::CATEGORY_SERVICES,
        self::CATEGORY_SPORT,
        self::CATEGORY_ENTERTAINMENT,
        self::CATEGORY_EDUCATION,
    ];

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

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id');
    }

    public function sourceCertificate(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_certificate_id');
    }
}

