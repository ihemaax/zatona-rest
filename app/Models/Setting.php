<?php

namespace App\Models;

use App\Models\Concerns\NormalizesMediaPath;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use NormalizesMediaPath;
    protected $fillable = [
        'restaurant_name',
        'restaurant_phone',
        'restaurant_address',
        'logo',
        'banner',
        'delivery_fee',
        'is_open',
        'front_theme',
    ];



    public function getLogoAttribute(mixed $value): ?string
    {
        return $this->normalizeMediaPath($value);
    }

    public function getBannerAttribute(mixed $value): ?string
    {
        return $this->normalizeMediaPath($value);
    }

}
