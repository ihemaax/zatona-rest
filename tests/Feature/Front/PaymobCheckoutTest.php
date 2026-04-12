<?php

namespace Tests\Feature\Front;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymobCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.wapilot.enabled' => false,
            'paymob.enabled' => true,
            'paymob.api_key' => 'paymob-key',
            'paymob.iframe_id' => 899959,
            'paymob.card_integration_id' => 4951530,
            'paymob.base_url' => 'https://accept.paymob.com/api',
            'paymob.hmac_secret' => 'hmac-secret',
        ]);

        Setting::create([
            'restaurant_name' => 'Test Restaurant',
            'delivery_fee' => 20,
            'is_open' => true,
        ]);
    }

    public function test_cash_checkout_still_works(): void
    {
        $this->seedCart();

        $this->post(route('checkout.store'), $this->payload(['payment_method' => 'cash']))
            ->assertRedirect();

        $this->assertDatabaseHas('orders', ['payment_method' => 'cash']);
        $this->assertDatabaseHas('payments', ['provider' => 'cash', 'status' => 'paid']);
    }

    public function test_paymob_initiation_success_path_renders_bridge_page(): void
    {
        $this->seedCart();
        Http::fake([
            'accept.paymob.com/api/auth/tokens' => Http::response(['token' => 'auth-1']),
            'accept.paymob.com/api/ecommerce/orders' => Http::response(['id' => 777]),
            'accept.paymob.com/api/acceptance/payment_keys' => Http::response(['token' => str_repeat('t', 400)]),
        ]);

        $storeResponse = $this->post(route('checkout.store'), $this->payload(['payment_method' => 'paymob']));
        $storeResponse->assertRedirect();

        $order = Order::firstOrFail();

        $start = $this->get(route('checkout.paymob.start', ['order' => $order->id, 'token' => $order->guest_token]));

        $start->assertOk();
        $start->assertSee('الانتقال إلى صفحة الدفع');
        $start->assertSee('https://accept.paymob.com/api/acceptance/iframes/899959?payment_token=');

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'provider' => 'paymob',
            'provider_reference' => '777',
        ]);
    }

    public function test_paymob_initiation_failure_keeps_cart_intact(): void
    {
        $this->seedCart();
        Http::fake([
            'accept.paymob.com/api/*' => Http::response(['error' => 'fail'], 500),
        ]);

        $this->post(route('checkout.store'), $this->payload(['payment_method' => 'paymob']))->assertRedirect();

        $order = Order::firstOrFail();
        $this->get(route('checkout.paymob.start', ['order' => $order->id, 'token' => $order->guest_token]))
            ->assertRedirect(route('checkout.index'));

        $this->assertNotEmpty(session('cart'));
    }

    public function test_invalid_iframe_id_fails_clearly(): void
    {
        config(['paymob.iframe_id' => 0]);
        $this->seedCart();

        Http::fake([
            'accept.paymob.com/api/auth/tokens' => Http::response(['token' => 'auth-1']),
            'accept.paymob.com/api/ecommerce/orders' => Http::response(['id' => 777]),
            'accept.paymob.com/api/acceptance/payment_keys' => Http::response(['token' => str_repeat('z', 500)]),
        ]);

        $this->post(route('checkout.store'), $this->payload(['payment_method' => 'paymob']))->assertRedirect();
        $order = Order::firstOrFail();

        $this->get(route('checkout.paymob.start', ['order' => $order->id, 'token' => $order->guest_token]))
            ->assertRedirect(route('checkout.index'));
    }

    public function test_long_payment_token_persists_safely(): void
    {
        $this->seedCart();
        $token = str_repeat('a', 2000);

        Http::fake([
            'accept.paymob.com/api/auth/tokens' => Http::response(['token' => 'auth-1']),
            'accept.paymob.com/api/ecommerce/orders' => Http::response(['id' => 777]),
            'accept.paymob.com/api/acceptance/payment_keys' => Http::response(['token' => $token]),
        ]);

        $this->post(route('checkout.store'), $this->payload(['payment_method' => 'paymob']))->assertRedirect();
        $order = Order::firstOrFail();
        $this->get(route('checkout.paymob.start', ['order' => $order->id, 'token' => $order->guest_token]))->assertOk();

        $this->assertDatabaseHas('payments', ['order_id' => $order->id]);
        $this->assertSame($token, Payment::where('order_id', $order->id)->value('payment_key'));
    }

    public function test_return_routes_do_not_404(): void
    {
        $this->get('/payment/paymob/success')->assertOk();
        $this->get('/payment/paymob/fail')->assertOk();
        $this->get('/payment/paymob/pending')->assertOk();
    }

    public function test_valid_hmac_webhook_marks_paid(): void
    {
        $payment = Payment::create([
            'order_id' => $this->createOrderForWebhook()->id,
            'provider' => 'paymob',
            'provider_reference' => '555',
            'status' => 'pending',
            'amount' => 220,
            'currency' => 'EGP',
        ]);

        $payload = $this->webhookPayload('555', true, false, 22000);
        $payload['hmac'] = $this->hmacForPayload($payload);

        $this->post(route('payment.paymob.webhook'), $payload)->assertOk();
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'paid']);
    }

    public function test_invalid_hmac_is_rejected(): void
    {
        $payload = $this->webhookPayload('555', true, false, 22000);
        $payload['hmac'] = 'bad';

        $this->post(route('payment.paymob.webhook'), $payload)->assertStatus(422);
    }

    public function test_pending_status_stays_pending(): void
    {
        $order = $this->createOrderForWebhook();
        Payment::create([
            'order_id' => $order->id,
            'provider' => 'paymob',
            'provider_reference' => '889',
            'status' => 'pending',
            'amount' => 220,
            'currency' => 'EGP',
        ]);

        $this->get('/payment/paymob/success?order=889')->assertSee('جارٍ تأكيد العملية');
    }

    protected function seedCart(): void
    {
        $category = Category::create(['name' => 'Pizza', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Margherita',
            'price' => 100,
            'is_available' => true,
        ]);

        $this->post(route('cart.add', $product), ['quantity' => 2])->assertRedirect();
    }

    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'order_type' => 'delivery',
            'customer_name' => 'Guest User',
            'customer_phone' => '1000000000',
            'address_line' => 'Alex Street 1',
            'area' => 'Alex',
            'latitude' => 31.2,
            'longitude' => 29.9,
            'payment_method' => 'cash',
        ], $overrides);
    }

    protected function webhookPayload(string $providerOrderId, bool $success, bool $pending, int $amountCents): array
    {
        return [
            'obj' => [
                'amount_cents' => $amountCents,
                'created_at' => '2026-04-12T10:00:00+00:00',
                'currency' => 'EGP',
                'error_occured' => false,
                'has_parent_transaction' => false,
                'id' => 500,
                'integration_id' => 4951530,
                'is_3d_secure' => true,
                'is_auth' => false,
                'is_capture' => false,
                'is_refunded' => false,
                'is_standalone_payment' => true,
                'is_voided' => false,
                'order' => ['id' => $providerOrderId],
                'owner' => 10,
                'pending' => $pending,
                'source_data' => ['pan' => '1234', 'sub_type' => 'MasterCard', 'type' => 'card'],
                'success' => $success,
            ],
        ];
    }

    protected function hmacForPayload(array $payload): string
    {
        $obj = Arr::get($payload, 'obj', []);
        $values = [
            Arr::get($obj, 'amount_cents', ''),
            Arr::get($obj, 'created_at', ''),
            Arr::get($obj, 'currency', ''),
            Arr::get($obj, 'error_occured', ''),
            Arr::get($obj, 'has_parent_transaction', ''),
            Arr::get($obj, 'id', ''),
            Arr::get($obj, 'integration_id', ''),
            Arr::get($obj, 'is_3d_secure', ''),
            Arr::get($obj, 'is_auth', ''),
            Arr::get($obj, 'is_capture', ''),
            Arr::get($obj, 'is_refunded', ''),
            Arr::get($obj, 'is_standalone_payment', ''),
            Arr::get($obj, 'is_voided', ''),
            Arr::get($obj, 'order.id', ''),
            Arr::get($obj, 'owner', ''),
            Arr::get($obj, 'pending', ''),
            Arr::get($obj, 'source_data.pan', ''),
            Arr::get($obj, 'source_data.sub_type', ''),
            Arr::get($obj, 'source_data.type', ''),
            Arr::get($obj, 'success', ''),
        ];

        $concatenated = collect($values)->map(fn ($v) => is_bool($v) ? ($v ? 'true' : 'false') : (string) $v)->implode('');

        return hash_hmac('sha512', $concatenated, 'hmac-secret');
    }
}
