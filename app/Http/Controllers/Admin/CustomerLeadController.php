<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerLeadController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAccess($request->user());

        $customers = User::query()
            ->where('user_type', User::TYPE_CUSTOMER)
            ->select(['id', 'name', 'email', 'phone'])
            ->latest('id')
            ->paginate(30);

        return view('admin.customer-leads.index', [
            'customers' => $customers,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeAccess($request->user());

        $customers = User::query()
            ->where('user_type', User::TYPE_CUSTOMER)
            ->select(['name', 'email', 'phone'])
            ->latest('id')
            ->cursor();

        $fileName = 'customer_leads_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($customers): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            // BOM for UTF-8 so Arabic appears correctly in Excel.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['الاسم', 'الإيميل', 'رقم الهاتف']);

            foreach ($customers as $customer) {
                fputcsv($handle, [
                    $customer->name,
                    $customer->email,
                    $customer->phone,
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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
