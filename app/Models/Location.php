<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'address',
        'lat',
        'lng',
        'radius_km',
        'is_active'
    ];

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function redemptions()
    {
        return $this->hasMany(CertificateRedemption::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInRadius($query, $lat, $lng, $radius)
    {
        // Формула гаверсинуса для MySQL
        return $query->whereRaw(
            "(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) <= ?",
            [$lat, $lng, $lat, $radius]
        );
    }

    // Accessors
    public function getFullAddressAttribute(): string
    {
        return $this->address;
    }

    public function getCoordinatesAttribute(): array
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng,
        ];
    }
}
