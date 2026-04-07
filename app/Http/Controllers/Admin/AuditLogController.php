<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::query()
            ->when($request->filled('event'), fn ($q) => $q->where('event', $request->string('event')))
            ->when($request->filled('method'), fn ($q) => $q->where('method', strtoupper((string) $request->string('method'))))
            ->when($request->filled('user_email'), fn ($q) => $q->where('user_email', 'like', '%' . $request->string('user_email') . '%'))
            ->when($request->filled('path'), fn ($q) => $q->where('path', 'like', '%' . $request->string('path') . '%'))
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
        ]);
    }
}
