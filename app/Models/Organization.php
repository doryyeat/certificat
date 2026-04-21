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
        'branding_logo_path',
        'branding_colors',
        'branding_background_path',
    ];

    protected $casts = [
        'subscription_active_until' => 'datetime',
        'branding_colors' => 'array',
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

    public function planKey(): string
    {
        $key = strtolower((string) $this->plan_name);
        return in_array($key, ['free', 'start', 'pro'], true) ? $key : 'free';
    }

    public function brandingAllowed(): bool
    {
        return in_array($this->planKey(), ['start', 'pro'], true);
    }

    public function brandingMaxColors(): int
    {
        return match ($this->planKey()) {
            'start' => 1,
            'pro' => 3,
            default => 0,
        };
    }

    public function brandingBackgroundAllowed(): bool
    {
        return $this->planKey() === 'pro';
    }
}

