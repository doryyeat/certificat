<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificateTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'type',
        'amount',
        'currency',
        'valid_days',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'valid_days' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(GiftCertificate::class, 'template_id');
    }
}

