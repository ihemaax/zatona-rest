@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">Audit Logs</h1>
            <p class="text-muted mb-0">سجل كامل لكل العمليات والطلبات داخل النظام.</p>
        </div>
    </div>

    <form method="GET" class="card card-body border-0 shadow-sm mb-3">
        <div class="row g-2">
            <div class="col-md-3">
                <input type="text" name="user_email" value="{{ request('user_email') }}" class="form-control" placeholder="إيميل المستخدم">
            </div>
            <div class="col-md-2">
                <input type="text" name="event" value="{{ request('event') }}" class="form-control" placeholder="الحدث">
            </div>
            <div class="col-md-2">
                <input type="text" name="method" value="{{ request('method') }}" class="form-control" placeholder="METHOD">
            </div>
            <div class="col-md-3">
                <input type="text" name="path" value="{{ request('path') }}" class="form-control" placeholder="المسار">
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-primary">بحث</button>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>الوقت</th>
                        <th>المستخدم</th>
                        <th>الدور</th>
                        <th>الحدث</th>
                        <th>الطريقة</th>
                        <th>المسار</th>
                        <th>الحالة</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $log->user_email ?? 'Guest' }}</td>
                            <td>{{ $log->user_role ?? '-' }}</td>
                            <td>{{ $log->event }}</td>
                            <td>{{ $log->method }}</td>
                            <td><code>{{ $log->path }}</code></td>
                            <td>{{ $log->status_code }}</td>
                            <td>{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">لا يوجد سجلات حتى الآن.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $logs->links() }}
    </div>
</div>
@endsection
