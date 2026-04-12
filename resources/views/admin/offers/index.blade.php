@extends('layouts.admin')

@php
    $editingOffer = $offer ?? null;
    $pageTitle = 'إدارة العروض';
    $pageSubtitle = 'إضافة وتعديل عروض الصفحة الرئيسية والتحكم في تفعيلها وترتيب ظهورها';
@endphp

@section('content')
<div class="row g-4">
    <div class="col-12 col-xl-4">
        <div class="admin-card p-4">
            <div class="section-title">{{ $editingOffer ? 'تعديل العرض' : 'إضافة عرض جديد' }}</div>
            <div class="section-subtitle">قم بإدخال بيانات العرض كما ستظهر في قسم العروض بالواجهة.</div>

            <form action="{{ $editingOffer ? route('admin.offers.update', $editingOffer) : route('admin.offers.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                @csrf
                @if($editingOffer)
                    @method('PUT')
                @endif

                <div class="col-12">
                    <label class="form-label fw-bold">اسم العرض</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $editingOffer?->name) }}" required>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">وصف قصير</label>
                    <textarea name="short_description" class="form-control" rows="3">{{ old('short_description', $editingOffer?->short_description) }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">صورة العرض</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    @if($editingOffer?->image)
                        <img src="{{ \App\Support\MediaUrl::fromPath($editingOffer->image) }}" alt="offer" class="img-fluid rounded mt-2" style="max-height:120px;object-fit:cover;">
                    @endif
                </div>

                <div class="col-6">
                    <label class="form-label fw-bold">السعر القديم</label>
                    <input type="number" name="old_price" step="0.01" min="0" class="form-control" value="{{ old('old_price', $editingOffer?->old_price) }}">
                </div>

                <div class="col-6">
                    <label class="form-label fw-bold">السعر الجديد</label>
                    <input type="number" name="new_price" step="0.01" min="0" class="form-control" value="{{ old('new_price', $editingOffer?->new_price) }}" required>
                </div>

                <div class="col-6">
                    <label class="form-label fw-bold">ترتيب الظهور</label>
                    <input type="number" name="sort_order" min="0" class="form-control" value="{{ old('sort_order', $editingOffer?->sort_order) }}">
                </div>

                <div class="col-6">
                    <label class="form-label fw-bold">الحالة</label>
                    <select name="is_active" class="form-select">
                        <option value="1" @selected(old('is_active', (int) ($editingOffer?->is_active ?? 1)) == 1)>فعال</option>
                        <option value="0" @selected(old('is_active', (int) ($editingOffer?->is_active ?? 1)) == 0)>غير فعال</option>
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label fw-bold">تاريخ البداية</label>
                    <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', optional($editingOffer?->starts_at)->format('Y-m-d\\TH:i')) }}">
                </div>

                <div class="col-6">
                    <label class="form-label fw-bold">تاريخ النهاية</label>
                    <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', optional($editingOffer?->ends_at)->format('Y-m-d\\TH:i')) }}">
                </div>

                <div class="col-12 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn-admin">{{ $editingOffer ? 'حفظ التعديل' : 'إضافة العرض' }}</button>
                    @if($editingOffer)
                        <a href="{{ route('admin.offers.index') }}" class="btn-admin-soft">إلغاء التعديل</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="col-12 col-xl-8">
        <div class="admin-card p-4">
            <div class="section-title">قائمة العروض</div>
            <div class="section-subtitle">العروض النشطة فقط تظهر في الصفحة الرئيسية حسب الترتيب والتواريخ.</div>

            <div class="admin-table-wrap mt-3">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الصورة</th>
                            <th>العرض</th>
                            <th>السعر</th>
                            <th>الحالة</th>
                            <th>الترتيب</th>
                            <th>المدة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($offers as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>
                                @if($item->image)
                                    <img src="{{ \App\Support\MediaUrl::fromPath($item->image) }}" alt="{{ $item->name }}" style="width:54px;height:54px;border-radius:10px;object-fit:cover;">
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold">{{ $item->name }}</div>
                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($item->short_description, 50) ?: '-' }}</div>
                            </td>
                            <td>
                                @if($item->old_price)
                                    <div class="text-muted text-decoration-line-through small">{{ number_format($item->old_price, 2) }}</div>
                                @endif
                                <div class="fw-bold text-success">{{ number_format($item->new_price, 2) }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $item->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $item->is_active ? 'فعال' : 'غير فعال' }}</span>
                            </td>
                            <td>{{ $item->sort_order ?? '-' }}</td>
                            <td class="small">
                                <div>من: {{ optional($item->starts_at)->format('Y-m-d H:i') ?: '-' }}</div>
                                <div>إلى: {{ optional($item->ends_at)->format('Y-m-d H:i') ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-2">
                                    <a href="{{ route('admin.offers.edit', $item) }}" class="btn btn-sm btn-outline-secondary">تعديل</a>
                                    <form method="POST" action="{{ route('admin.offers.destroy', $item) }}" onsubmit="return confirm('هل تريد حذف العرض؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">لا توجد عروض حالياً.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $offers->links() }}</div>
        </div>
    </div>
</div>
@endsection
