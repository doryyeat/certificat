<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'inn',
        'phone',
        'address',
        'description',
        'website',
        'logo',
        'subscription',
        'verified_at'
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function certificates()
    {
        return $this->hasMany(GiftCertificate::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function redemptions()
    {
        return $this->hasMany(CertificateRedemption::class);
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    public function scopeBySubscription($query, $plan)
    {
        return $query->where('subscription', $plan);
    }

    // Accessors
    public function getIsVerifiedAttribute(): bool
    {
        return !is_null($this->verified_at);
    }

    public function getSubscriptionDetailsAttribute(): array
    {
        return match($this->subscription) {
            'free' => [
                'price' => 0,
                'certificates_limit' => 50,
                'commission_rate' => 3.0,
                'analytics' => 'basic',
                'api_enabled' => false
            ],
            'start' => [
                'price' => 990,
                'certificates_limit' => 500,
                'commission_rate' => 2.0,
                'analytics' => 'medium',
                'api_enabled' => true
            ],
            'pro' => [
                'price' => 2990,
                'certificates_limit' => null,
                'commission_rate' => 1.5,
                'analytics' => 'full',
                'api_enabled' => true
            ],
            default => []
        };
    }

    public function getCertificatesCountAttribute(): int
    {
        return $this->certificates()->count();
    }

    public function getActiveCertificatesCountAttribute(): int
    {
        return $this->certificates()->where('status', 'active')->count();
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    // Methods
    public function canCreateCertificate(): bool
    {
        $details = $this->subscription_details;

        if ($details['certificates_limit'] === null) {
            return true;
        }

        return $this->active_certificates_count < $details['certificates_limit'];
    }

    public function verify(): void
    {
        $this->update(['verified_at' => now()]);
    }
}
