<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\UserAddress;
use App\Services\WapilotService;
use App\Support\ContactValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    protected int $otpTtlMinutes = 10;

    public function method()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('home')->with('error', 'السلة فارغة');
        }

        return view('front.checkout-method');
    }

    public function index(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('home')->with('error', 'السلة فارغة');
        }

        $setting = Setting::first();
        $savedAddresses = auth()->check() ? auth()->user()->addresses()->latest()->get() : collect();
        $defaultAddress = auth()->check() ? auth()->user()->addresses()->where('is_default', true)->first() : null;
        $branches = Branch::where('is_active', true)->latest()->get();

        $selectedOrderType = $request->get('order_type', 'delivery');
        if (!in_array($selectedOrderType, ['delivery', 'pickup'])) {
            $selectedOrderType = 'delivery';
        }

        $couponCode = $request->old('coupon_code', session('checkout_coupon_code'));
        $subtotal = (float) collect($cart)->sum('total');
        $couponPreview = $this->resolveCouponData($couponCode, $subtotal);

        return view('front.checkout', compact(
            'cart',
            'setting',
            'savedAddresses',
            'defaultAddress',
            'branches',
            'selectedOrderType',
            'couponPreview'
        ));
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:40',
        ], ContactValidation::messages());

        $cart = session()->get('cart', []);
        $subtotal = (float) collect($cart)->sum('total');
        $couponData = $this->resolveCouponData($request->coupon_code, $subtotal);

        if (!$couponData['coupon']) {
            return back()->with('error', $couponData['message'] ?? 'كوبون الخصم غير صالح')->withInput();
        }

        session(['checkout_coupon_code' => $couponData['coupon']->code]);

        return back()->with('success', 'تم تطبيق الكوبون بنجاح');
    }

    public function store(Request $request)
    {
        $setting = Setting::first();

        if ($setting && !$setting->is_open) {
            return redirect()->route('home')->with('error', 'المطعم مغلق حاليًا ولا يمكن استقبال طلبات جديدة');
        }

        $request->validate([
            'order_type' => 'required|in:delivery,pickup',
            'branch_id' => 'nullable|exists:branches,id',

            'customer_name'  => 'required|string|max:255',
            'customer_phone' => ContactValidation::egyptianMobileRules(),

            'address_line'   => 'nullable|string|max:255',
            'area'           => 'nullable|string|max:255',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
            'notes'          => 'nullable|string',

            'save_address'   => 'nullable|boolean',
            'address_label'  => 'nullable|string|max:255',
            'make_default'   => 'nullable|boolean',
            'coupon_code'    => 'nullable|string|max:40',
        ], ContactValidation::messages());

        $normalizedPhone = app(WapilotService::class)->normalizePhone((string) $request->customer_phone);
        if ($this->isOtpFeatureEnabled() && !$this->isOtpVerifiedForPhone($request, $normalizedPhone)) {
            session(['checkout_pending_payload' => $request->only([
                'order_type',
                'branch_id',
                'customer_name',
                'customer_phone',
                'address_line',
                'area',
                'latitude',
                'longitude',
                'notes',
                'save_address',
                'address_label',
                'make_default',
                'coupon_code',
            ])]);

            if (!$this->issueOtpIfNeeded($request, $normalizedPhone)) {
                return redirect()->route('checkout.index', ['order_type' => $request->order_type])
                    ->withInput()
                    ->with('error', 'تعذر إرسال كود التحقق الآن. حاول مرة أخرى بعد قليل.');
            }

            return redirect()->route('checkout.otp.page')
                ->with('info', 'هنبعتلك كود بس علشان نتأكد إنك جعان 😄');
        }

        if ($request->order_type === 'delivery' && empty($request->address_line)) {
            return redirect()->back()->with('error', 'يرجى إدخال عنوان التوصيل');
        }

        if ($request->order_type === 'pickup' && empty($request->branch_id)) {
            return redirect()->back()->with('error', 'يرجى اختيار الفرع للاستلام');
        }

        $selectedBranch = null;

        if ($request->order_type === 'pickup') {
            $selectedBranch = Branch::find($request->branch_id);

            if (!$selectedBranch) {
                return redirect()->back()->with('error', 'الفرع المحدد غير موجود');
            }
        }

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('home')->with('error', 'السلة فارغة');
        }

        $subtotal = collect($cart)->sum('total');
        $couponData = $this->resolveCouponData($request->coupon_code ?: session('checkout_coupon_code'), (float) $subtotal);
        if (($request->coupon_code || session('checkout_coupon_code')) && !$couponData['coupon']) {
            return redirect()->back()->with('error', $couponData['message'] ?? 'كوبون الخصم غير صالح')->withInput();
        }

        $deliveryFee = $request->order_type === 'delivery'
            ? ($setting?->delivery_fee ?? 25)
            : 0;

        $discountAmount = (float) ($couponData['discount'] ?? 0);
        $total = max(0, $subtotal + $deliveryFee - $discountAmount);
        $etaMinutes = $request->order_type === 'delivery' ? 45 : 20;
        $guestToken = auth()->check() ? null : Str::random(40);

        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id'                    => auth()->id(),
                'guest_token'                => $guestToken,
                'order_type'                 => $request->order_type,
                'branch_id'                  => $request->order_type === 'pickup' ? $request->branch_id : null,

                'customer_name'              => $request->customer_name,
                'customer_phone'             => $normalizedPhone,

                'address_line'               => $request->order_type === 'delivery'
                    ? $request->address_line
                    : ($selectedBranch?->address ?? 'استلام من المطعم'),

                'area'                       => $request->order_type === 'delivery'
                    ? $request->area
                    : ($selectedBranch?->name ?? 'الفرع'),

                'latitude'                   => $request->order_type === 'delivery'
                    ? $request->latitude
                    : $selectedBranch?->latitude,

                'longitude'                  => $request->order_type === 'delivery'
                    ? $request->longitude
                    : $selectedBranch?->longitude,

                'notes'                      => $request->notes,
                'coupon_id'                  => $couponData['coupon']?->id,
                'coupon_code'                => $couponData['coupon']?->code,
                'subtotal'                   => $subtotal,
                'discount_amount'            => $discountAmount,
                'delivery_fee'               => $deliveryFee,
                'total'                      => $total,
                'payment_method'             => 'cash',
                'status'                     => 'pending',
                'estimated_delivery_minutes' => $etaMinutes,
                'estimated_delivery_at'      => now()->addMinutes($etaMinutes),
                'is_seen_by_admin'           => false,
            ]);

            foreach ($cart as $item) {
                OrderItem::create([
                    'order_id'         => $order->id,
                    'product_id'       => $item['product_id'],
                    'product_name'     => $item['name'],
                    'price'            => $item['price'],
                    'quantity'         => $item['quantity'],
                    'total'            => $item['total'],
                    'selected_options' => $item['selected_options'] ?? [],
                ]);
            }

            if ($couponData['coupon']) {
                $couponData['coupon']->increment('used_count');
            }

            if (
                auth()->check() &&
                $request->order_type === 'delivery' &&
                $request->boolean('save_address')
            ) {
                if ($request->boolean('make_default')) {
                    auth()->user()->addresses()->update(['is_default' => false]);
                }

                UserAddress::create([
                    'user_id'      => auth()->id(),
                    'label'        => $request->address_label ?: 'عنوان محفوظ',
                    'address_line' => $request->address_line,
                    'area'         => $request->area,
                    'latitude'     => $request->latitude,
                    'longitude'    => $request->longitude,
                    'is_default'   => $request->boolean('make_default'),
                ]);
            }

            DB::commit();

            session()->forget('cart');
            session()->forget('checkout_coupon_code');
            $this->clearOtpSession($request);

            if ($guestToken) {
                return redirect()->route('order.success', [$order->id, $guestToken])
                    ->with('success', 'تم تأكيد الطلب بنجاح');
            }

            return redirect()->route('order.success', $order->id)
                ->with('success', 'تم تأكيد الطلب بنجاح');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('checkout.store.failed', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
                'order_type' => $request->order_type,
                'ip' => $request->ip(),
                'trace_id' => (string) Str::uuid(),
            ]);

            return redirect()->back()->with('error', 'حدث خطأ أثناء إنشاء الطلب، حاول مرة أخرى خلال دقائق');
        }
    }

    public function showOtpVerificationPage(Request $request): RedirectResponse|View
    {
        $pending = session('checkout_pending_payload');
        if (!$pending || empty($pending['customer_phone'])) {
            return redirect()->route('checkout.index')->with('error', 'لا يوجد طلب بانتظار التحقق.');
        }

        return view('front.checkout-otp', [
            'phone' => $pending['customer_phone'],
        ]);
    }

    public function verifyOtpAndContinue(Request $request, WapilotService $otpService)
    {
        $data = $request->validate([
            'otp_code' => ['required', 'digits:6'],
        ]);

        $pending = session('checkout_pending_payload');
        if (!$pending || empty($pending['customer_phone'])) {
            return redirect()->route('checkout.index')->with('error', 'لا يوجد طلب بانتظار التحقق.');
        }

        $normalizedPhone = $otpService->normalizePhone((string) $pending['customer_phone']);
        $cacheKey = $this->otpCacheKey($request);
        $payload = Cache::get($cacheKey);

        if (!$payload) {
            return back()->with('error', 'الكود غير موجود أو منتهي. اطلب كود جديد.');
        }

        if (($payload['phone'] ?? '') !== $normalizedPhone) {
            return back()->with('error', 'رقم الهاتف تغيّر. من فضلك اطلب كود جديد.');
        }

        if ((int) ($payload['expires_at'] ?? 0) < now()->timestamp) {
            return back()->with('error', 'انتهت صلاحية الكود. اطلب كود جديد.');
        }

        $result = $otpService->verifyOtp($normalizedPhone, (string) $data['otp_code']);
        if (!(bool) ($result['success'] ?? false)) {
            return back()->with('error', (string) ($result['message'] ?? 'كود التحقق غير صحيح أو منتهي.'));
        }

        $payload['verified'] = true;
        $payload['verified_at'] = now()->timestamp;
        Cache::put($cacheKey, $payload, now()->addMinutes($this->otpTtlMinutes));
        session(['checkout_phone_verified' => $normalizedPhone]);
        $request->merge($pending);

        return $this->store($request);
    }

    public function resendOtp(Request $request, WapilotService $otpService): RedirectResponse
    {
        $pending = session('checkout_pending_payload');
        if (!$pending || empty($pending['customer_phone'])) {
            return redirect()->route('checkout.index')->with('error', 'لا يوجد طلب بانتظار التحقق.');
        }

        $normalizedPhone = $otpService->normalizePhone((string) $pending['customer_phone']);
        if (!$this->issueOtp($request, $normalizedPhone, $otpService)) {
            return back()->with('error', 'تعذر إرسال كود التحقق الآن. حاول مرة أخرى بعد قليل.');
        }

        return back()->with('success', 'تم إرسال كود جديد على واتساب.');
    }

    public function success(Order $order, ?string $token = null)
    {
        if ($order->user_id) {
            if (!auth()->check() || $order->user_id !== auth()->id()) {
                abort(403);
            }
        } else {
            if (!$token || $token !== $order->guest_token) {
                abort(403);
            }
        }

        return view('front.success', compact('order'));
    }

    protected function resolveCouponData(?string $couponCode, float $subtotal): array
    {
        $code = strtoupper(trim((string) $couponCode));
        if ($code === '') {
            return ['coupon' => null, 'discount' => 0, 'message' => null];
        }

        $coupon = Coupon::query()
            ->whereRaw('UPPER(code) = ?', [$code])
            ->first();

        if (!$coupon) {
            return ['coupon' => null, 'discount' => 0, 'message' => 'كوبون الخصم غير موجود'];
        }

        if (!$coupon->isUsable($subtotal)) {
            return ['coupon' => null, 'discount' => 0, 'message' => 'الكوبون غير متاح حاليًا أو لا يطابق شروط الطلب'];
        }

        return [
            'coupon' => $coupon,
            'discount' => $coupon->calculateDiscount($subtotal),
            'message' => null,
        ];
    }

    protected function otpCacheKey(Request $request): string
    {
        return 'checkout_otp:' . sha1((string) $request->session()->getId());
    }

    protected function isOtpVerifiedForPhone(Request $request, string $phone): bool
    {
        if (!$this->isOtpFeatureEnabled()) {
            return true;
        }

        $payload = Cache::get($this->otpCacheKey($request));
        $sessionVerified = (string) session('checkout_phone_verified');

        if (!$payload || $sessionVerified === '') {
            return false;
        }

        if (($payload['expires_at'] ?? 0) < now()->timestamp) {
            return false;
        }

        return (bool) ($payload['verified'] ?? false)
            && ($payload['phone'] ?? null) === $phone
            && $sessionVerified === $phone;
    }

    protected function clearOtpSession(Request $request): void
    {
        Cache::forget($this->otpCacheKey($request));
        session()->forget('checkout_phone_verified');
        session()->forget('checkout_pending_payload');
    }

    protected function issueOtpIfNeeded(Request $request, string $normalizedPhone): bool
    {
        $payload = Cache::get($this->otpCacheKey($request));

        if (
            !$payload
            || ($payload['phone'] ?? null) !== $normalizedPhone
            || (int) ($payload['expires_at'] ?? 0) < now()->timestamp
        ) {
            return $this->issueOtp($request, $normalizedPhone, app(WapilotService::class));
        }

        return true;
    }

    protected function issueOtp(Request $request, string $normalizedPhone, WapilotService $otpService): bool
    {
        $result = $otpService->sendOtp(
            $normalizedPhone,
            "كود تأكيد الطلب: {OTP}\nالكود صالح لمدة {$this->otpTtlMinutes} دقائق.",
            (string) config('services.wapilot.session_id', '')
        );

        if (!(bool) ($result['success'] ?? false)) {
            return false;
        }

        Cache::put($this->otpCacheKey($request), [
            'phone' => $normalizedPhone,
            'expires_at' => now()->addMinutes($this->otpTtlMinutes)->timestamp,
            'verified' => false,
            'verified_at' => null,
        ], now()->addMinutes($this->otpTtlMinutes));

        session()->forget('checkout_phone_verified');

        return true;
    }

    protected function isOtpFeatureEnabled(): bool
    {
        return (bool) config('services.wapilot.enabled', true);
    }
}
