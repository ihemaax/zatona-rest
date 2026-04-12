<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymobService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class PaymobPaymentController extends Controller
{
    public function start(Request $request, Order $order, PaymobService $paymobService)
    {
        Log::info('paymob.start.begin', ['order_id' => $order->id, 'method' => $request->method()]);

        if (!$this->canAccessOrder($request, $order)) {
            abort(403);
        }

        $payment = $order->payment;

        if (!$payment || $payment->provider !== 'paymob') {
            return redirect()->route('checkout.index')->with('error', 'لا يمكن بدء عملية الدفع لهذا الطلب.');
        }

        try {
            $result = $paymobService->initiate($order->loadMissing('items'), $payment);
        } catch (\Throwable $e) {
            Log::error('paymob.start.failed', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('checkout.index')->with('error', 'تعذر بدء الدفع الإلكتروني الآن. حاول مرة أخرى.');
        }

        session()->forget('cart');
        session()->forget('checkout_coupon_code');
        session()->forget('checkout_phone_verified');
        session()->forget('checkout_pending_payload');

        Log::info('paymob.bridge.rendered', ['order_id' => $order->id, 'payment_id' => $payment->id]);

        return response()->view('front.payments.paymob-bridge', [
            'redirectUrl' => $result['redirect_url'],
            'order' => $order,
        ]);
    }

    public function success(Request $request)
    {
        Log::info('paymob.success.params', ['params' => $request->except(['hmac'])]);

        $payment = $this->resolvePaymentFromReturnParams($request);

        if (!$payment) {
            return view('front.payments.paymob-pending', ['order' => null]);
        }

        $payment->callback_payload = $request->all();
        $payment->save();

        if ($payment->status === 'paid') {
            return view('front.payments.paymob-success', ['order' => $payment->order]);
        }

        return view('front.payments.paymob-pending', ['order' => $payment->order]);
    }

    public function fail(Request $request)
    {
        Log::info('paymob.fail.params', ['params' => $request->except(['hmac'])]);

        $payment = $this->resolvePaymentFromReturnParams($request);
        if (!$payment) {
            return view('front.payments.paymob-fail', ['order' => null]);
        }

        $payment->callback_payload = $request->all();
        if ($payment->status !== 'paid') {
            $payment->status = 'pending';
        }
        $payment->save();

        return view('front.payments.paymob-fail', ['order' => $payment->order]);
    }

    public function pending(Request $request)
    {
        Log::info('paymob.pending.params', ['params' => $request->except(['hmac'])]);

        $payment = $this->resolvePaymentFromReturnParams($request);

        return view('front.payments.paymob-pending', ['order' => $payment?->order]);
    }

    public function webhook(Request $request, PaymobService $paymobService): Response
    {
        Log::info('paymob.webhook.received', ['payload' => $request->all()]);

        $payload = $request->all();
        $hmac = (string) ($request->input('hmac') ?? $request->header('X-Paymob-Hmac', ''));

        $isValid = $paymobService->verifyWebhookHmac($payload, $hmac);

        Log::info('paymob.webhook.hmac', ['valid' => $isValid]);

        if (!$isValid) {
            return response('invalid hmac', 422);
        }

        $providerReference = (string) Arr::get($payload, 'obj.order.id', '');
        $payment = Payment::where('provider', 'paymob')
            ->where('provider_reference', $providerReference)
            ->first();

        if (!$payment) {
            return response('payment not found', 404);
        }

        $amountCents = (int) Arr::get($payload, 'obj.amount_cents', -1);
        $expectedCents = (int) round(((float) $payment->amount) * 100);

        if ($amountCents !== $expectedCents || strtoupper((string) Arr::get($payload, 'obj.currency', '')) !== 'EGP') {
            Log::warning('paymob.webhook.amount_currency_mismatch', [
                'payment_id' => $payment->id,
                'amount_cents' => $amountCents,
                'expected_cents' => $expectedCents,
                'currency' => Arr::get($payload, 'obj.currency'),
            ]);

            return response('amount mismatch', 422);
        }

        $paymobService->applyTrustedWebhookUpdate($payment, $payload);

        return response('ok');
    }

    protected function resolvePaymentFromReturnParams(Request $request): ?Payment
    {
        $providerReference = (string) ($request->query('order') ?? $request->query('id') ?? $request->input('order'));

        if ($providerReference === '') {
            return null;
        }

        return Payment::query()
            ->with('order')
            ->where('provider', 'paymob')
            ->where('provider_reference', $providerReference)
            ->first();
    }

    protected function canAccessOrder(Request $request, Order $order): bool
    {
        if ($order->user_id) {
            return auth()->check() && (int) auth()->id() === (int) $order->user_id;
        }

        return (string) $request->query('token') !== ''
            && hash_equals((string) $order->guest_token, (string) $request->query('token'));
    }
}
