@extends('layouts.admin')

@php
    $pageTitle = 'إدارة المنتجات';
    $pageSubtitle = 'متابعة المنتجات بشكل منظم حسب الأقسام';

    $productsCollection = $products instanceof \Illuminate\Pagination\AbstractPaginator ? $products->getCollection() : collect($products);
    $groupedProducts = $productsCollection->groupBy(fn($product) => $product->category->name ?? 'بدون قسم');
@endphp

@section('content')
<style>
    .desktop-product-groups{
        display:block;
    }

    .mobile-product-groups{
        display:none;
    }

    .product-group-card{
        padding:22px;
    }

    .product-mobile-card{
        background:#fff;
        border:1px solid #dbe5f0;
        border-radius:18px;
        padding:14px;
        box-shadow:0 8px 18px rgba(15,23,42,.05);
    }

    .product-mobile-card + .product-mobile-card{
        margin-top:12px;
    }

    .product-mobile-head{
        display:flex;
        gap:12px;
        align-items:flex-start;
        margin-bottom:12px;
    }

    .product-mobile-image{
        width:62px;
        height:62px;
        border-radius:14px;
        object-fit:cover;
        flex-shrink:0;
        border:1px solid #e5e7eb;
    }

    .product-mobile-placeholder{
        width:62px;
        height:62px;
        border-radius:14px;
        background:#eef2f7;
        border:1px solid #e5e7eb;
        flex-shrink:0;
    }

    .product-mobile-actions{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin-top:10px;
    }

    .product-mobile-actions .btn,
    .product-mobile-actions form{
        flex:1 1 120px;
    }

    .product-mobile-actions form button{
        width:100%;
    }

    @media (max-width: 767.98px){
        .desktop-product-groups{
            display:none;
        }

        .mobile-product-groups{
            display:block;
        }

        .product-group-card{
            padding:16px;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <div class="section-title mb-1">قائمة المنتجات</div>
        <div class="section-subtitle mb-0">تم تقسيم المنتجات حسب الأقسام لتسهيل المتابعة والإدارة</div>
    </div>

    <a href="{{ route('admin.products.create') }}" class="btn-admin">إضافة منتج</a>
</div>

<div class="desktop-product-groups">
    @forelse($groupedProducts as $categoryName => $categoryProducts)
        <div class="admin-card product-group-card mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <div class="section-title mb-1">{{ $categoryName }}</div>
                    <div class="section-subtitle mb-0">{{ $categoryProducts->count() }} منتج داخل هذا القسم</div>
                </div>

                <span class="filter-pill active">{{ $categoryName }}</span>
            </div>

            <div class="admin-table-wrap">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الصورة</th>
                            <th>اسم المنتج</th>
                            <th>الوصف</th>
                            <th>السعر</th>
                            <th>الحالة</th>
                            <th style="min-width:220px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categoryProducts as $product)
                            <tr>
                                <td>{{ $product->id }}</td>

                                <td>
                                    @if($product->image)
                                        <img
                                            src="{{ \App\Support\MediaUrl::fromPath( $product->image) }}"
                                            alt="{{ $product->name }}"
                                            style="width:58px;height:58px;object-fit:cover;border-radius:14px;border:1px solid #e5e7eb;"
                                        >
                                    @else
                                        <div style="width:58px;height:58px;border-radius:14px;background:#f1f5f9;border:1px solid #e5e7eb;"></div>
                                    @endif
                                </td>

                                <td><div class="fw-bold">{{ $product->name }}</div></td>

                                <td style="max-width:340px;">
                                    <div class="text-muted small">{{ \Illuminate\Support\Str::limit($product->description, 90) ?: 'لا يوجد وصف' }}</div>
                                </td>

                                <td class="fw-bold">{{ number_format($product->price, 2) }} ج.م</td>

                                <td>
                                    @if($product->is_available)
                                        <span class="order-type-chip order-type-pickup">متاح</span>
                                    @else
                                        <span class="status-chip status-cancelled">غير متاح</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('admin.products.options.index', $product->id) }}" class="btn btn-sm btn-outline-primary">الاختيارات</a>
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-secondary">تعديل</a>
                                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف المنتج؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="admin-card p-5 text-center">
            <div class="section-title">لا توجد منتجات</div>
            <div class="section-subtitle mb-3">ابدأ بإضافة أول منتج من لوحة الإدارة</div>
            <a href="{{ route('admin.products.create') }}" class="btn-admin">إضافة منتج</a>
        </div>
    @endforelse
</div>

<div class="mobile-product-groups">
    @forelse($groupedProducts as $categoryName => $categoryProducts)
        <div class="admin-card product-group-card mb-4">
            <div class="section-title mb-1">{{ $categoryName }}</div>
            <div class="section-subtitle">{{ $categoryProducts->count() }} منتج داخل هذا القسم</div>

            @foreach($categoryProducts as $product)
                <div class="product-mobile-card">
                    <div class="product-mobile-head">
                        @if($product->image)
                            <img src="{{ \App\Support\MediaUrl::fromPath( $product->image) }}" alt="{{ $product->name }}" class="product-mobile-image">
                        @else
                            <div class="product-mobile-placeholder"></div>
                        @endif

                        <div class="flex-grow-1">
                            <div class="fw-bold mb-1">{{ $product->name }}</div>
                            <div class="small text-muted mb-2">{{ \Illuminate\Support\Str::limit($product->description, 85) ?: 'لا يوجد وصف' }}</div>
                            <div class="fw-bold">{{ number_format($product->price, 2) }} ج.م</div>
                        </div>
                    </div>

                    <div class="mb-2">
                        @if($product->is_available)
                            <span class="order-type-chip order-type-pickup">متاح</span>
                        @else
                            <span class="status-chip status-cancelled">غير متاح</span>
                        @endif
                    </div>

                    <div class="product-mobile-actions">
                        <a href="{{ route('admin.products.options.index', $product->id) }}" class="btn btn-outline-primary">الاختيارات</a>
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-outline-secondary">تعديل</a>
                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف المنتج؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">حذف</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div class="admin-card p-4 text-center">
            <div class="section-title">لا توجد منتجات</div>
            <div class="section-subtitle mb-3">ابدأ بإضافة أول منتج</div>
            <a href="{{ route('admin.products.create') }}" class="btn-admin">إضافة منتج</a>
        </div>
    @endforelse
</div>

@if($products instanceof \Illuminate\Pagination\AbstractPaginator)
    <div class="d-flex justify-content-center mt-4">
        {{ $products->links() }}
    </div>
@endif
@endsection