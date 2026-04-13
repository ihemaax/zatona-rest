@extends('layouts.app')

@section('title', 'حسابي')

@section('content')
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
