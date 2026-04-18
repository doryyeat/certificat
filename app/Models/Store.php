<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Store extends Model
{
    protected $fillable = [
        'organization_id',
        'name',
        'address',
        'opening_hours',
        'phone',
        'lat',
        'lng',
        'main_image_id'
    ];

    protected $appends = ['image_data']; // Добавляем computed поле

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function giftCertificates(): HasMany
    {
        return $this->hasMany(GiftCertificate::class, 'store_id');
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    // Получаем первое изображение
    public function getImageAttribute()
    {
        return $this->images->first();
    }

    // Получаем данные изображения для Vue
    public function getImageDataAttribute(): ?array
    {
        $image = $this->images->first();
        if (!$image) {
            return null;
        }

        return [
            'id' => $image->id,
            'small_uri' => $image->small_uri,
            'medium_uri' => $image->medium_uri,
            'original_uri' => $image->original_uri,
        ];
    }
}
