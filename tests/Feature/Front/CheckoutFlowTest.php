<?php

namespace Tests\Feature\Front;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_complete_delivery_checkout_flow(): void
    {
        Setting::create([
            'restaurant_name' => 'Test Restaurant',
            'delivery_fee' => 20,
            'is_open' => true,
        ]);

        $category = Category::create([
            'name' => 'Pizza',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Margherita',
            'price' => 100,
            'is_available' => true,
        ]);

        $this->post(route('cart.add', $product), [
            'quantity' => 2,
        ])->assertRedirect();

        $this->post(route('checkout.store'), [
            'order_type' => 'delivery',
            'customer_name' => 'Guest User',
            'customer_phone' => '01000000000',
            'address_line' => 'Alex Street 1',
            'area' => 'Alex',
            'latitude' => 31.2,
            'longitude' => 29.9,
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Guest User',
            'order_type' => 'delivery',
            'subtotal' => 200,
            'delivery_fee' => 20,
            'total' => 220,
        ]);

        $this->assertDatabaseCount('order_items', 1);
    }
}
