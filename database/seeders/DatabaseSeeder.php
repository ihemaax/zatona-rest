<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $meals = Category::create([
            'name' => 'وجبات',
            'slug' => 'meals',
            'is_active' => true,
        ]);

        $sandwiches = Category::create([
            'name' => 'ساندويتشات',
            'slug' => 'sandwiches',
            'is_active' => true,
        ]);

        $extras = Category::create([
            'name' => 'إضافات',
            'slug' => 'extras',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $meals->id,
            'name' => 'فرايد تشيكن 4 قطع',
            'slug' => Str::slug('fried chicken 4 pieces'),
            'description' => 'وجبة فرايد تشيكن مع بطاطس وكول سلو',
            'price' => 180,
            'image' => 'https://via.placeholder.com/400x300',
            'is_available' => true,
        ]);

        Product::create([
            'category_id' => $meals->id,
            'name' => 'فرايد تشيكن 8 قطع',
            'slug' => Str::slug('fried chicken 8 pieces'),
            'description' => 'وجبة عائلية',
            'price' => 320,
            'image' => 'https://via.placeholder.com/400x300',
            'is_available' => true,
        ]);

        Product::create([
            'category_id' => $sandwiches->id,
            'name' => 'ساندويتش زنجر',
            'slug' => Str::slug('zinger sandwich'),
            'description' => 'ساندويتش زنجر حار',
            'price' => 95,
            'image' => 'https://via.placeholder.com/400x300',
            'is_available' => true,
        ]);

        Product::create([
            'category_id' => $extras->id,
            'name' => 'بطاطس',
            'slug' => Str::slug('fries'),
            'description' => 'بطاطس مقرمشة',
            'price' => 45,
            'image' => 'https://via.placeholder.com/400x300',
            'is_available' => true,
        ]);

        Setting::create([
            'restaurant_name' => 'Fried Chicken',
            'restaurant_phone' => '01000000000',
            'restaurant_address' => 'Alexandria, Egypt',
            'delivery_fee' => 30,
            'is_open' => true,
        ]);
    }
}