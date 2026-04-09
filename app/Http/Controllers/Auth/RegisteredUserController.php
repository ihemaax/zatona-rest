<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WapilotService;
use App\Support\ContactValidation;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    protected int $otpTtlMinutes = 10;

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
            'email' => [...ContactValidation::emailRules(), 'unique:' . User::class],
            'phone' => [
                ...ContactValidation::egyptianMobileRules(),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $normalized = ContactValidation::normalizeEgyptianMobile((string) $value);
                    if ($normalized !== '' && DB::table('users')->where('phone', $normalized)->exists()) {
                        $fail('رقم الموبايل مستخدم بالفعل. جرّب تسجيل الدخول أو استخدم رقمًا آخر.');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], array_merge(ContactValidation::messages(), [
            'name.required' => 'من فضلك اكتب الاسم بالكامل.',
            'name.max' => 'الاسم طويل جدًا. الحد الأقصى 255 حرف.',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل. جرّب تسجيل الدخول أو استخدم بريدًا آخر.',
            'password.required' => 'من فضلك اكتب كلمة المرور.',
            'password.min' => 'كلمة المرور لازم تكون 6 أحرف على الأقل.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
        ]));

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => ContactValidation::normalizeEgyptianMobile((string) $request->phone),
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
        if (!$this->issueRegistrationOtp($user, app(WapilotService::class))) {
            $user->delete();

            return back()->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'تعذر إرسال كود التحقق الآن. حاول مرة أخرى بعد قليل.');
        }

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
        if (!$payload || (int) ($payload['expires_at'] ?? 0) < now()->timestamp) {
            return back()->with('error', 'الكود غير صحيح أو منتهي.');
        }

        if (!$this->isOtpCodeValid((string) ($payload['otp_hash'] ?? ''), (string) $request->otp_code)) {
            return back()->with('error', 'الكود غير صحيح أو منتهي.');
        }

        session()->forget('registration_pending_user');
        Cache::forget($this->registrationOtpCacheKey($user->id));

        $user->update(['phone_verified_at' => now()]);
        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('home')->with('success', 'تم تفعيل رقم واتساب بنجاح.');
    }

    public function resendPhoneOtp(WapilotService $otpService): RedirectResponse
    {
        $user = $this->getPendingRegistrationUser();
        if (!$user) {
            return redirect()->route('register')->with('error', 'انتهت جلسة التحقق. سجل من جديد.');
        }

        if (!$this->issueRegistrationOtp($user, $otpService)) {
            return back()->with('error', 'تعذر إرسال كود التحقق الآن. حاول مرة أخرى بعد قليل.');
        }

        return back()->with('success', 'تم إرسال كود جديد على واتساب.');
    }

    protected function issueRegistrationOtp(User $user, WapilotService $otpService): bool
    {
        $otpCode = $this->generateOtpCode();
        $result = $otpService->sendOtp(
            (string) $user->phone,
            $otpCode,
            "كود تفعيل الحساب: {OTP}\nالكود صالح لمدة {$this->otpTtlMinutes} دقائق.",
        );

        if (!(bool) ($result['ok'] ?? false)) {
            return false;
        }

        Cache::put($this->registrationOtpCacheKey($user->id), [
            'otp_hash' => $this->hashOtpCode($otpCode),
            'expires_at' => now()->addMinutes($this->otpTtlMinutes)->timestamp,
        ], now()->addMinutes($this->otpTtlMinutes));

        return true;
    }

    protected function getPendingRegistrationUser(): ?User
    {
        $id = (int) session('registration_pending_user');
        if ($id <= 0) {
            return null;
        }

        return User::find($id);
    }

    protected function registrationOtpCacheKey(int $userId): string
    {
        return 'registration_otp:' . $userId;
    }

    protected function generateOtpCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    protected function hashOtpCode(string $otpCode): string
    {
        return hash('sha256', trim($otpCode) . '|' . config('app.key'));
    }

    protected function isOtpCodeValid(string $storedHash, string $candidateCode): bool
    {
        if ($storedHash === '') {
            return false;
        }

        return hash_equals($storedHash, $this->hashOtpCode($candidateCode));
    }
}
