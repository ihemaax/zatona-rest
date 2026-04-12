<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PaymobService
{
    public function __construct(private readonly HttpFactory $http)
    {
    }

    public function initiate(Order $order, Payment $payment): array
    {
        $this->validateConfigOrFail();
        $totals = $this->validateTotalsOrFail($order, $payment);

        $authToken = $this->requestAuthToken();
        $providerOrder = $this->createProviderOrder($order, $authToken, $totals['amount_cents']);
        $paymentKey = $this->createPaymentKey($order, $authToken, (int) $providerOrder['id'], $totals['amount_cents']);

        $payment->forceFill([
            'provider_reference' => (string) $providerOrder['id'],
            'payment_key' => $paymentKey,
        ])->save();

        Log::info('paymob.metadata.persisted', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'provider_reference' => $payment->provider_reference,
            'payment_token_length' => mb_strlen($paymentKey),
        ]);

        $redirectUrl = $this->buildIframeUrl($paymentKey);

        Log::info('paymob.redirect_url.built', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'iframe_id' => config('paymob.iframe_id'),
        ]);

        return [
            'redirect_url' => $redirectUrl,
            'provider_reference' => (string) $providerOrder['id'],
            'payment_key' => $paymentKey,
        ];
    }

    public function buildIframeUrl(string $paymentToken): string
    {
        $iframeId = config('paymob.iframe_id');

        if (!is_numeric($iframeId) || (int) $iframeId <= 0) {
            throw new RuntimeException('PAYMOB_IFRAME_ID must be numeric and greater than zero.');
        }

        return sprintf(
            'https://accept.paymob.com/api/acceptance/iframes/%d?payment_token=%s',
            (int) $iframeId,
            urlencode($paymentToken)
        );
    }

    public function verifyWebhookHmac(array $payload, ?string $receivedHmac): bool
    {
        $secret = (string) config('paymob.hmac_secret');

        if ($secret === '' || !$receivedHmac) {
            return false;
        }

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
        $calculated = hash_hmac('sha512', $concatenated, $secret);

        return hash_equals($calculated, strtolower($receivedHmac));
    }

    public function applyTrustedWebhookUpdate(Payment $payment, array $payload): Payment
    {
        $obj = Arr::get($payload, 'obj', []);
        $isSuccess = (bool) Arr::get($obj, 'success', false);
        $isPending = (bool) Arr::get($obj, 'pending', true);

        $payment->webhook_payload = $payload;

        if ($isSuccess && !$isPending) {
            $payment->status = 'paid';
            $payment->paid_at = now();
            $payment->failure_reason = null;
        } elseif ($isPending) {
            $payment->status = 'pending';
        } else {
            $payment->status = 'failed';
            $payment->failure_reason = (string) Arr::get($obj, 'data.message', 'Payment failed from provider');
        }

        $payment->save();

        Log::info('paymob.payment.final_save', [
            'payment_id' => $payment->id,
            'order_id' => $payment->order_id,
            'status' => $payment->status,
            'paid_at' => optional($payment->paid_at)->toDateTimeString(),
        ]);

        return $payment;
    }


    protected function validateConfigOrFail(): void
    {
        if (!(bool) config('paymob.enabled')) {
            throw new RuntimeException('Paymob is disabled.');
        }

        if ((string) config('paymob.api_key') === '') {
            throw new RuntimeException('PAYMOB_API_KEY is required.');
        }

        if (!is_numeric(config('paymob.iframe_id')) || (int) config('paymob.iframe_id') <= 0) {
            throw new RuntimeException('PAYMOB_IFRAME_ID must be numeric and greater than zero.');
        }
    }

    protected function validateTotalsOrFail(Order $order, Payment $payment): array
    {
        $orderTotalCents = (int) round(((float) $order->total) * 100);
        $paymentCents = (int) round(((float) $payment->amount) * 100);
        $itemsCents = (int) round(((float) $order->items()->sum('total')) * 100);
        $deliveryFeeCents = (int) round(((float) $order->delivery_fee) * 100);
        $discountCents = (int) round(((float) $order->discount_amount) * 100);
        $composedCents = max(0, $itemsCents + $deliveryFeeCents - $discountCents);

        Log::info('paymob.totals.check', [
            'order_id' => $order->id,
            'order_total_cents' => $orderTotalCents,
            'payment_amount_cents' => $paymentCents,
            'composed_total_cents' => $composedCents,
        ]);

        if ($orderTotalCents !== $paymentCents || $paymentCents !== $composedCents) {
            throw new RuntimeException('Paymob initiation blocked due to totals mismatch.');
        }

        return ['amount_cents' => $orderTotalCents];
    }

    protected function requestAuthToken(): string
    {
        Log::info('paymob.auth.request');

        $response = $this->httpClient()->post('/auth/tokens', [
            'api_key' => (string) config('paymob.api_key'),
        ])->throw();

        Log::info('paymob.auth.response', ['status' => $response->status()]);

        return (string) $response->json('token');
    }

    protected function createProviderOrder(Order $order, string $authToken, int $amountCents): array
    {
        Log::info('paymob.provider_order.request', ['order_id' => $order->id]);

        $response = $this->httpClient()->post('/ecommerce/orders', [
            'auth_token' => $authToken,
            'delivery_needed' => false,
            'amount_cents' => $amountCents,
            'currency' => config('paymob.currency', 'EGP'),
            'merchant_order_id' => (string) $order->id,
            'items' => $order->items->map(function ($item) {
                return [
                    'name' => $item->product_name,
                    'amount_cents' => (int) round(((float) $item->price) * 100),
                    'description' => $item->product_name,
                    'quantity' => (int) $item->quantity,
                ];
            })->values()->all(),
        ])->throw();

        Log::info('paymob.provider_order.response', [
            'status' => $response->status(),
            'provider_order_id' => $response->json('id'),
        ]);

        return $response->json();
    }

    protected function createPaymentKey(Order $order, string $authToken, int $providerOrderId, int $amountCents): string
    {
        Log::info('paymob.payment_key.request', ['order_id' => $order->id, 'provider_order_id' => $providerOrderId]);

        $response = $this->httpClient()->post('/acceptance/payment_keys', [
            'auth_token' => $authToken,
            'amount_cents' => $amountCents,
            'expiration' => 3600,
            'order_id' => $providerOrderId,
            'billing_data' => [
                'first_name' => $order->customer_name,
                'last_name' => '-',
                'phone_number' => $order->customer_phone,
                'email' => 'guest@local.test',
                'apartment' => 'NA',
                'floor' => 'NA',
                'street' => $order->address_line ?: 'NA',
                'building' => 'NA',
                'shipping_method' => 'NA',
                'postal_code' => '00000',
                'city' => $order->area ?: 'NA',
                'country' => 'EG',
                'state' => $order->area ?: 'NA',
            ],
            'currency' => config('paymob.currency', 'EGP'),
            'integration_id' => (int) config('paymob.card_integration_id'),
            'lock_order_when_paid' => true,
        ])->throw();

        Log::info('paymob.payment_key.response', [
            'status' => $response->status(),
            'token_length' => mb_strlen((string) $response->json('token')),
        ]);

        return (string) $response->json('token');
    }

    protected function httpClient()
    {
        return $this->http
            ->acceptJson()
            ->contentType('application/json')
            ->baseUrl((string) config('paymob.base_url'))
            ->timeout((int) config('paymob.timeout', 20));
    }
}
