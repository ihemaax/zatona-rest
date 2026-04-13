<form method="POST" action="{{ $action }}" class="accountx-form-grid">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="accountx-form-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));">
        <input class="accountx-input" name="label" value="{{ old('label', $address->label ?? '') }}" placeholder="اسم العنوان (المنزل/العمل)" required>
        <input class="accountx-input" name="recipient_name" value="{{ old('recipient_name', $address->recipient_name ?? '') }}" placeholder="اسم المستلم">
        <input class="accountx-input" name="phone" value="{{ old('phone', $address->phone ?? '') }}" placeholder="رقم الهاتف">
    </div>

    <input class="accountx-input" name="address_line" value="{{ old('address_line', $address->address_line ?? '') }}" placeholder="العنوان بالتفصيل" required>

    <div class="accountx-form-grid" style="grid-template-columns:repeat(auto-fit,minmax(140px,1fr));">
        <input class="accountx-input" name="area" value="{{ old('area', $address->area ?? '') }}" placeholder="المنطقة">
        <input class="accountx-input" name="district" value="{{ old('district', $address->district ?? '') }}" placeholder="الحي">
        <input class="accountx-input" name="street" value="{{ old('street', $address->street ?? '') }}" placeholder="الشارع">
        <input class="accountx-input" name="building" value="{{ old('building', $address->building ?? '') }}" placeholder="المبنى">
        <input class="accountx-input" name="floor" value="{{ old('floor', $address->floor ?? '') }}" placeholder="الدور">
        <input class="accountx-input" name="apartment" value="{{ old('apartment', $address->apartment ?? '') }}" placeholder="الشقة">
    </div>

    <input class="accountx-input" name="landmark" value="{{ old('landmark', $address->landmark ?? '') }}" placeholder="علامة مميزة">
    <textarea class="accountx-textarea" name="notes" placeholder="ملاحظات إضافية">{{ old('notes', $address->notes ?? '') }}</textarea>

    <div class="d-flex align-items-center gap-2">
        <input type="checkbox" value="1" id="default_{{ $address->id ?? 'new' }}" name="is_default" @checked(old('is_default', $address->is_default ?? false))>
        <label for="default_{{ $address->id ?? 'new' }}" class="small">تعيين كعنوان افتراضي</label>
    </div>

    <input type="hidden" name="latitude" value="{{ old('latitude', $address->latitude ?? '') }}">
    <input type="hidden" name="longitude" value="{{ old('longitude', $address->longitude ?? '') }}">

    <button class="accountx-btn accountx-btn-primary" type="submit">حفظ العنوان</button>
</form>
