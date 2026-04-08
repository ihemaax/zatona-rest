<?php

namespace App\Models;

use App\Models\Concerns\NormalizesMediaPath;
use Illuminate\Database\Eloquent\Model;

class DigitalMenuSetting extends Model
{
    use NormalizesMediaPath;
    protected $fillable = [
        'title',
        'slug',
        'subtitle',
        'logo',
        'banner',
        'phone',
        'address',
        'show_prices',
        'show_descriptions',
        'is_active',
    ];

    protected $casts = [
        'show_prices' => 'boolean',
        'show_descriptions' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function categories()
    {
        return $this->hasMany(DigitalMenuCategory::class)->orderBy('sort_order');
    }



    public function getLogoAttribute(mixed $value): ?string
    {
        return $this->normalizeMediaPath($value);
    }

    public function getBannerAttribute(mixed $value): ?string
    {
        return $this->normalizeMediaPath($value);
    }

}
