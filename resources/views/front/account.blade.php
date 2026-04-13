@extends('layouts.app')

@section('title', 'حسابي')

@section('content')
<style>
.accountx-wrap{max-width:1040px;margin:0 auto;display:grid;gap:14px}
.accountx-hero{background:linear-gradient(135deg,#174f42,#2e7d67);color:#fff;border-radius:24px;padding:18px;display:flex;justify-content:space-between;gap:12px;align-items:flex-start;box-shadow:0 16px 35px rgba(20,58,49,.28)}
.accountx-avatar{width:56px;height:56px;border-radius:18px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.45rem}
.accountx-name{font-size:1.2rem;font-weight:900;margin:0}
.accountx-phone{font-weight:700;opacity:.9;margin-top:3px}
.accountx-chip{background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.24);padding:6px 10px;border-radius:999px;font-size:.75rem;font-weight:800}
.accountx-grid{display:grid;gap:14px;grid-template-columns:1fr}
.accountx-card{background:#fff;border:1px solid #ebe1d3;border-radius:20px;padding:16px}
.accountx-card h3{font-size:1rem;font-weight:900;margin:0 0 12px;color:#27302d}
.accountx-sub{font-size:.82rem;color:#7f7669;margin:-4px 0 12px}
.accountx-form-grid{display:grid;grid-template-columns:1fr;gap:10px}
.accountx-btn{border:none;border-radius:12px;padding:10px 14px;font-weight:800}
.accountx-btn-primary{background:linear-gradient(120deg,#1d5f50,#2f8a70);color:#fff}
.accountx-btn-soft{background:#f3f5f6;color:#3e4643}
.accountx-address-list{display:grid;gap:10px}
.accountx-address{border:1px solid #ede4d8;border-radius:14px;padding:12px;background:#fffdfa}
.accountx-address-head{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:7px}
.accountx-badge{font-size:.68rem;font-weight:900;background:#d9f3e8;color:#136347;border-radius:999px;padding:4px 8px}
.accountx-actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px}
.accountx-input,.accountx-textarea{border:1px solid #e5dccf;background:#fcfaf7;border-radius:12px;padding:9px 11px;width:100%}
.accountx-textarea{resize:vertical;min-height:74px}
@media (min-width: 992px){.accountx-grid{grid-template-columns:1fr 1fr}.accountx-card.addresses{grid-column:1/-1}}
</style>

<div class="accountx-wrap">
    <section class="accountx-hero">
        <div class="d-flex gap-3 align-items-center">
            <div class="accountx-avatar"><i class="bi bi-person-fill"></i></div>
            <div>
                <h1 class="accountx-name">{{ $user->name }}</h1>
                <div class="accountx-phone">{{ $user->phone ?: 'بدون رقم هاتف' }}</div>
            </div>
        </div>
        <span class="accountx-chip">إعدادات الحساب</span>
    </section>

    <div class="accountx-grid">
        <section class="accountx-card">
            <h3>البيانات الشخصية</h3>
            <p class="accountx-sub">حدّث اسمك ورقم الهاتف المسجل على الحساب.</p>
            <form method="POST" action="{{ route('account.profile.update') }}" class="accountx-form-grid">
                @csrf
                @method('PATCH')
                <input class="accountx-input" name="name" value="{{ old('name', $user->name) }}" placeholder="الاسم">
                <input class="accountx-input" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="رقم الهاتف">
                <button class="accountx-btn accountx-btn-primary" type="submit">حفظ التغييرات</button>
            </form>
        </section>

        <section class="accountx-card">
            <h3>الأمان</h3>
            <p class="accountx-sub">تغيير كلمة المرور بشكل آمن بعد التحقق من الحالية.</p>
            <form method="POST" action="{{ route('account.password.update') }}" class="accountx-form-grid">
                @csrf
                @method('PATCH')
                <input class="accountx-input" type="password" name="current_password" placeholder="كلمة المرور الحالية">
                <input class="accountx-input" type="password" name="password" placeholder="كلمة المرور الجديدة">
                <input class="accountx-input" type="password" name="password_confirmation" placeholder="تأكيد كلمة المرور الجديدة">
                <button class="accountx-btn accountx-btn-primary" type="submit">تحديث كلمة المرور</button>
            </form>
        </section>

        <section class="accountx-card addresses">
            <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                <h3 class="mb-0">العناوين المحفوظة</h3>
                <button class="accountx-btn accountx-btn-soft" type="button" data-bs-toggle="collapse" data-bs-target="#newAddressForm">+ إضافة عنوان</button>
            </div>

            <div class="collapse mb-3" id="newAddressForm">
                @include('front.partials.account-address-form', ['action' => route('account.addresses.store'), 'method' => 'POST', 'address' => null])
            </div>

            <div class="accountx-address-list">
                @forelse($addresses as $address)
                    <article class="accountx-address">
                        <div class="accountx-address-head">
                            <strong>{{ $address->label }}</strong>
                            @if($address->is_default)
                                <span class="accountx-badge">العنوان الافتراضي</span>
                            @endif
                        </div>
                        <div class="small text-muted">{{ $address->recipient_name ?: $user->name }} • {{ $address->phone ?: $user->phone }}</div>
                        <div class="mt-1">{{ $address->address_line }}</div>
                        <div class="small text-muted">{{ collect([$address->area, $address->district, $address->street, $address->building])->filter()->join(' - ') }}</div>

                        <div class="accountx-actions">
                            <button class="accountx-btn accountx-btn-soft" type="button" data-bs-toggle="collapse" data-bs-target="#editAddress{{ $address->id }}">تعديل</button>
                            @if(!$address->is_default)
                                <form method="POST" action="{{ route('account.addresses.default', $address) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="accountx-btn accountx-btn-soft" type="submit">تعيين افتراضي</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" onsubmit="return confirm('حذف هذا العنوان؟')">
                                @csrf
                                @method('DELETE')
                                <button class="accountx-btn accountx-btn-soft" type="submit">حذف</button>
                            </form>
                        </div>

                        <div class="collapse mt-3" id="editAddress{{ $address->id }}">
                            @include('front.partials.account-address-form', ['action' => route('account.addresses.update', $address), 'method' => 'PATCH', 'address' => $address])
                        </div>
                    </article>
                @empty
                    <div class="text-muted">لا توجد عناوين محفوظة حتى الآن.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
