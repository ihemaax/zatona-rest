<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CustomerLeadsExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CustomerLeadController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAccess($request->user());

        $customers = User::query()
            ->where('user_type', User::TYPE_CUSTOMER)
            ->select(['id', 'name', 'email', 'phone', 'created_at'])
            ->latest('id')
            ->paginate(30);

        return view('admin.customer-leads.index', [
            'customers' => $customers,
        ]);
    }

    public function export(Request $request)
    {
        $this->authorizeAccess($request->user());

        $customers = User::query()
            ->where('user_type', User::TYPE_CUSTOMER)
            ->select(['name', 'email', 'phone', 'created_at'])
            ->latest('id')
            ->get();

        $fileName = 'customer_leads_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new CustomerLeadsExport($customers), $fileName);
    }

    protected function authorizeAccess(?User $user): void
    {
        abort_unless(
            $user
            && $user->is_active
            && ($user->isSuperAdmin() || $user->role === User::ROLE_MANAGER),
            403,
            'غير مسموح لك بالوصول إلى هذه الصفحة.'
        );
    }
}
