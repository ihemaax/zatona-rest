<?php

namespace Tests\Feature\Front;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.wpsenderx.enabled' => false,
            'services.wpsenderx.api_key' => 'test-key',
            'services.wpsenderx.base_url' => 'https://backendapi.wpsenderx.com/api',
        ]);
    }

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
            'customer_phone' => '1000000000',
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

    public function test_coupon_is_applied_during_checkout(): void
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

        Coupon::create([
            'code' => 'SAVE10',
            'type' => 'percent',
            'value' => 10,
            'is_active' => true,
        ]);

        $this->post(route('cart.add', $product), [
            'quantity' => 2,
        ])->assertRedirect();

        $this->post(route('checkout.store'), [
            'order_type' => 'delivery',
            'customer_name' => 'Guest User',
            'customer_phone' => '1000000000',
            'address_line' => 'Alex Street 1',
            'area' => 'Alex',
            'latitude' => 31.2,
            'longitude' => 29.9,
            'coupon_code' => 'save10',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'coupon_code' => 'SAVE10',
            'discount_amount' => 20,
            'total' => 200,
        ]);

        $this->assertDatabaseHas('coupons', [
            'code' => 'SAVE10',
            'used_count' => 1,
        ]);
    }

    public function test_checkout_is_blocked_when_otp_not_verified(): void
    {
        config(['services.wpsenderx.enabled' => true]);
        $this->seedCheckoutData();

        $this->post(route('checkout.store'), $this->checkoutPayload())
            ->assertSessionHas('error', 'لازم تأكد رقم الموبايل بكود واتساب قبل تأكيد الطلب.');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_otp_send_success(): void
    {
        config(['services.wpsenderx.enabled' => true]);
        Http::fake([
            'backendapi.wpsenderx.com/*' => Http::response([
                'status' => 'success',
                'data' => ['status' => 'pending', 'message' => 'جاري الارسال'],
            ], 200),
        ]);

        $this->postJson(route('checkout.otp.send'), ['customer_phone' => '1200000000'])
            ->assertOk()
            ->assertJson(['ok' => true]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://backendapi.wpsenderx.com/api/otp/send'
                && $request->method() === 'POST'
                && $request->header('Accept')[0] === 'application/json'
                && $request->header('Content-Type')[0] === 'application/json'
                && $request->header('X-API-Key')[0] === 'test-key'
                && $request['recipient'] === '+201200000000'
                && !array_key_exists('message', $request->data())
                && !array_key_exists('session_id', $request->data());
        });
    }


    public function test_provider_json_fail_is_not_classified_as_provider_protection(): void
    {
        config(['services.wpsenderx.enabled' => true]);
        Http::fake([
            'backendapi.wpsenderx.com/*' => Http::response([
                'status' => 'fail',
                'message' => 'رقم غير صالح',
            ], 200),
        ]);

        $this->postJson(route('checkout.otp.send'), ['customer_phone' => '1200000000'])
            ->assertStatus(422)
            ->assertJson([
                'ok' => false,
                'type' => 'provider',
                'message' => 'رقم غير صالح',
            ]);
    }

    public function test_otp_verify_success_allows_checkout(): void
    {
        config(['services.wpsenderx.enabled' => true]);
        $this->seedCheckoutData();

        Http::fake([
            'backendapi.wpsenderx.com/api/otp/send' => Http::response(['status' => 'success'], 200),
            'backendapi.wpsenderx.com/api/otp/verify' => Http::response(['status' => 'success'], 200),
        ]);

        $this->postJson(route('checkout.otp.send'), ['customer_phone' => '1000000000'])
            ->assertOk();

        $this->postJson(route('checkout.otp.verify'), [
            'customer_phone' => '1000000000',
            'otp_code' => '123456',
        ])->assertOk()->assertJson(['ok' => true]);

        $this->post(route('checkout.store'), $this->checkoutPayload())
            ->assertRedirect();

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_invalid_phone_format_is_rejected_for_otp_send(): void
    {
        config(['services.wpsenderx.enabled' => true]);

        $this->postJson(route('checkout.otp.send'), ['customer_phone' => '12345'])
            ->assertStatus(422);
    }

    public function test_provider_html_challenge_is_classified_as_provider_protection(): void
    {
        config(['services.wpsenderx.enabled' => true]);
        Http::fake([
            'backendapi.wpsenderx.com/*' => Http::response('<html>Just a moment...</html>', 403, ['Content-Type' => 'text/html']),
        ]);

        $this->postJson(route('checkout.otp.send'), ['customer_phone' => '1200000000'])
            ->assertStatus(403)
            ->assertJson([
                'ok' => false,
                'type' => 'provider_protection',
            ]);
    }

    protected function seedCheckoutData(): void
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
    }

    protected function checkoutPayload(): array
    {
        return [
            'order_type' => 'delivery',
            'customer_name' => 'Guest User',
            'customer_phone' => '1000000000',
            'address_line' => 'Alex Street 1',
            'area' => 'Alex',
            'latitude' => 31.2,
            'longitude' => 29.9,
        ];
    }
}
