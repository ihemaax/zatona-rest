@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 mb-1">إدارة نظام الكاشير</h1>
            <p class="text-muted mb-0">تحكم في منتجات وأسعار شاشة الكاشير لكل فرع + رابط مباشر لشاشة الكاشير.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">الفرع</label>
                    <select name="branch_id" class="form-select" onchange="this.form.submit()">
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($selectedBranch && $selectedBranch->id === $branch->id)>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($selectedBranch)
                    <div class="col-md-6">
                        <label class="form-label">رابط شاشة كاشير الفرع</label>
                        <div class="input-group">
                            <input type="text" class="form-control" readonly value="{{ route('admin.cashier.pos', $selectedBranch) }}">
                            <a href="{{ route('admin.cashier.pos', $selectedBranch) }}" target="_blank" class="btn btn-outline-primary">فتح</a>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>

    @if($selectedBranch)
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 mb-3">إضافة منتج لمنيو الكاشير</h2>
                    <form method="POST" action="{{ route('admin.cashier.menu-items.store') }}" class="row g-3">
                        @csrf
                        <input type="hidden" name="branch_id" value="{{ $selectedBranch->id }}">

                        <div class="col-12">
                            <label class="form-label">المنتج</label>
                            <select name="product_id" class="form-select" required>
                                <option value="">اختر منتج</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ number_format($product->price, 2) }} ج.م)</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">سعر خاص (اختياري)</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="price" placeholder="سعر المنتج">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">الترتيب</label>
                            <input type="number" class="form-control" name="sort_order" value="0" min="0">
                        </div>

                        <div class="col-12 form-check ms-1">
                            <input type="checkbox" class="form-check-input" checked name="is_active" value="1" id="is_active_add">
                            <label class="form-check-label" for="is_active_add">متاح في شاشة الكاشير</label>
                        </div>

                        <div class="col-12">
                            <button class="btn btn-primary w-100">حفظ</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 mb-3">منتجات منيو الكاشير - {{ $selectedBranch->name }}</h2>

                    @if($menuItems->isEmpty())
                        <div class="alert alert-warning mb-0">لا توجد منتجات مضافة بعد.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>المنتج</th>
                                        <th>السعر</th>
                                        <th>الحالة</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($menuItems as $item)
                                        <tr>
                                            <td>{{ $item->product->name }}</td>
                                            <td>{{ number_format($item->price ?? $item->product->price, 2) }} ج.م</td>
                                            <td>
                                                <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $item->is_active ? 'مفعل' : 'غير مفعل' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <form method="POST" action="{{ route('admin.cashier.menu-items.update', $item) }}" class="d-flex gap-1 align-items-center">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="number" step="0.01" name="price" class="form-control form-control-sm" style="width: 100px" value="{{ $item->price }}" placeholder="سعر">
                                                        <input type="number" name="sort_order" class="form-control form-control-sm" style="width: 80px" value="{{ $item->sort_order }}" min="0">
                                                        <select name="is_active" class="form-select form-select-sm" style="width: 95px">
                                                            <option value="1" @selected($item->is_active)>مفعل</option>
                                                            <option value="0" @selected(!$item->is_active)>متوقف</option>
                                                        </select>
                                                        <button class="btn btn-sm btn-outline-primary">تحديث</button>
                                                    </form>

                                                    <form method="POST" action="{{ route('admin.cashier.menu-items.destroy', $item) }}" onsubmit="return confirm('حذف الصنف من منيو الكاشير؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger">حذف</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
