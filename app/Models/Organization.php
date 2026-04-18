<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'plan_name',
        'subscription_active_until',
        'primary_color',
        'logo_url',
    ];

    protected $casts = [
        'subscription_active_until' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function giftCertificates(): HasMany
    {
        return $this->hasMany(GiftCertificate::class);
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function transactionFeePercent(): float
    {
        return match (strtolower((string) $this->plan_name)) {
            'start' => 2.0,
            'pro' => 1.5,
            'free', 'demo', 'standard' => 3.0,
            default => 3.0,
        };
    }
}

