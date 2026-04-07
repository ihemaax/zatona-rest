<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use App\Services\WapilotService;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    protected int $otpTtlMinutes = 10;
    protected int $otpMaxAttempts = 5;

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * كل تسجيل من الواجهة العادية = Customer فقط
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'من فضلك اكتب الاسم بالكامل.',
            'name.max' => 'الاسم طويل جدًا. الحد الأقصى 255 حرف.',

            'email.required' => 'من فضلك اكتب البريد الإلكتروني.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة. مثال صحيح: name@example.com',
            'email.max' => 'البريد الإلكتروني طويل جدًا.',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل. جرّب تسجيل الدخول أو استخدم بريدًا آخر.',

            'phone.required' => 'من فضلك اكتب رقم واتساب.',
            'phone.max' => 'رقم الهاتف طويل جدًا.',
            'phone.unique' => 'رقم واتساب مستخدم بالفعل. جرّب تسجيل الدخول أو استخدم رقمًا آخر.',

            'password.required' => 'من فضلك اكتب كلمة المرور.',
            'password.min' => 'كلمة المرور لازم تكون 6 أحرف على الأقل.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $this->normalizeEgyptianPhone((string) $request->phone),
            'password' => Hash::make($request->password),

            // فصل العميل عن الموظفين
            'user_type' => User::TYPE_CUSTOMER,

            // العميل ليس موظفًا
            'role' => null,
            'branch_id' => null,
            'permissions' => [],
            'is_active' => true,
            'phone_verified_at' => null,
        ]);

        session(['registration_pending_user' => $user->id]);
        $this->issueRegistrationOtp($user, app(WapilotService::class));

        return redirect()->route('register.phone.verify.notice');
    }

    public function showPhoneVerificationNotice(): View|RedirectResponse
    {
        $user = $this->getPendingRegistrationUser();
        if (!$user) {
            return redirect()->route('register');
        }

        return view('auth.verify-phone', [
            'phone' => $user->phone,
        ]);
    }

    public function verifyPhone(Request $request): RedirectResponse
    {
        $request->validate([
            'otp_code' => ['required', 'digits:6'],
        ]);

        $user = $this->getPendingRegistrationUser();
        if (!$user) {
            return redirect()->route('register')->with('error', 'انتهت جلسة التحقق. سجل من جديد.');
        }

        $payload = Cache::get($this->registrationOtpCacheKey($user->id));
        if (!$payload) {
            return back()->with('error', 'الكود منتهي. اطلب كود جديد.');
        }

        if (($payload['expires_at'] ?? 0) < now()->timestamp) {
            return back()->with('error', 'انتهت صلاحية الكود. اطلب كود جديد.');
        }

        if ((int) ($payload['attempts'] ?? 0) >= $this->otpMaxAttempts) {
            return back()->with('error', 'تم تجاوز عدد المحاولات. اطلب كود جديد.');
        }

        $payload['attempts'] = (int) ($payload['attempts'] ?? 0) + 1;

        if (!Hash::check((string) $request->otp_code, (string) ($payload['code_hash'] ?? ''))) {
            Cache::put($this->registrationOtpCacheKey($user->id), $payload, now()->addMinutes($this->otpTtlMinutes));

            return back()->with('error', 'الكود غير صحيح.');
        }

        Cache::forget($this->registrationOtpCacheKey($user->id));
        session()->forget('registration_pending_user');

        $user->update(['phone_verified_at' => now()]);
        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('home')->with('success', 'تم تفعيل رقم واتساب بنجاح.');
    }

    public function resendPhoneOtp(WapilotService $wapilot): RedirectResponse
    {
        $user = $this->getPendingRegistrationUser();
        if (!$user) {
            return redirect()->route('register')->with('error', 'انتهت جلسة التحقق. سجل من جديد.');
        }

        $this->issueRegistrationOtp($user, $wapilot);

        return back()->with('success', 'تم إرسال كود جديد على واتساب.');
    }

    protected function issueRegistrationOtp(User $user, WapilotService $wapilot): void
    {
        $code = (string) random_int(100000, 999999);

        Cache::put($this->registrationOtpCacheKey($user->id), [
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes($this->otpTtlMinutes)->timestamp,
        ], now()->addMinutes($this->otpTtlMinutes));

        $wapilot->sendTextToPhone(
            (string) $user->phone,
            "كود تفعيل الحساب: {$code}\nالكود صالح لمدة {$this->otpTtlMinutes} دقائق."
        );
    }

    protected function registrationOtpCacheKey(int $userId): string
    {
        return 'register_otp:' . $userId;
    }

    protected function getPendingRegistrationUser(): ?User
    {
        $id = (int) session('registration_pending_user');
        if ($id <= 0) {
            return null;
        }

        return User::find($id);
    }

    protected function normalizeEgyptianPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            $digits = '2' . $digits;
        }

        if (!str_starts_with($digits, '2')) {
            $digits = '2' . $digits;
        }

        return $digits;
    }
}
