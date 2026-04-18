<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    protected $guarded = [];

    // Добавляем виртуальные поля для URL
    protected $appends = ['small_uri', 'medium_uri', 'original_uri'];

    // Аксессор для маленького изображения
    public function getSmallUriAttribute(): ?string
    {
        if (!$this->small_path) {
            return null;
        }
        return Storage::disk($this->disk ?? 'public')->url($this->small_path);
    }

    // Аксессор для среднего изображения
    public function getMediumUriAttribute(): ?string
    {
        if ($this->medium_path) {
            return Storage::disk($this->disk ?? 'public')->url($this->medium_path);
        }
        return $this->small_uri;
    }

    // Аксессор для оригинального изображения
    public function getOriginalUriAttribute(): ?string
    {
        if ($this->original_path) {
            return Storage::disk($this->disk ?? 'public')->url($this->original_path);
        }
        return $this->small_uri;
    }

    // Связь с родительской моделью
    public function imageable()
    {
        return $this->morphTo();
    }
    public function mainPhoto()
    {
        return $this->belongsTo(Image::class, 'main_photo_id');
    }
}
