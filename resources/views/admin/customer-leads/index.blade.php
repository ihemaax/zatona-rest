@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">بيانات العملاء</h1>
            <p class="text-muted mb-0">الاسم + الإيميل + رقم الهاتف للعملاء المسجلين تلقائيًا.</p>
        </div>

        <a href="{{ route('admin.customer-leads.export.excel') }}" class="btn btn-success">
            سحب البيانات Excel
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3 py-3">الاسم</th>
                            <th class="px-3 py-3">الإيميل</th>
                            <th class="px-3 py-3">رقم الهاتف</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td class="px-3 py-3">{{ $customer->name }}</td>
                                <td class="px-3 py-3">{{ $customer->email }}</td>
                                <td class="px-3 py-3">{{ $customer->phone }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">لا يوجد عملاء مسجلون حتى الآن.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $customers->links() }}
    </div>
</div>
@endsection
