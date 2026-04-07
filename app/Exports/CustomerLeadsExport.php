<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomerLeadsExport implements FromArray, WithHeadings
{
    public function __construct(protected Collection $customers)
    {
    }

    public function headings(): array
    {
        return ['الاسم', 'الإيميل', 'رقم الهاتف', 'تاريخ التسجيل'];
    }

    public function array(): array
    {
        return $this->customers
            ->map(fn ($customer) => [
                $customer->name,
                $customer->email,
                $customer->phone,
                optional($customer->created_at)?->format('Y-m-d H:i'),
            ])
            ->all();
    }
}
