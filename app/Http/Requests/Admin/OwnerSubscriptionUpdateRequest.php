<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OwnerSubscriptionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $plans = array_keys((array) config('subscription.plans', []));

        return [
            'plan_slug' => ['required', 'string', Rule::in($plans)],
            'subscription_status' => ['required', 'string', Rule::in(['active', 'pending', 'paused', 'expired', 'cancelled'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
            'quick_action' => ['nullable', 'string', Rule::in([
                'activate_now',
                'pause',
                'end_subscription',
                'cancel_subscription',
                'reactivate',
                'renew_30',
                'renew_90',
                'renew_365',
                'extend_7',
                'extend_15',
                'extend_30',
            ])],
        ];
    }

    public function messages(): array
    {
        return [
            'plan_slug.required' => 'يرجى اختيار الباقة قبل الحفظ.',
            'plan_slug.in' => 'الباقة المختارة غير صالحة.',
            'subscription_status.required' => 'يرجى تحديد حالة الاشتراك.',
            'subscription_status.in' => 'الحالة المختارة غير مدعومة.',
            'starts_at.date' => 'تاريخ البداية غير صحيح.',
            'ends_at.date' => 'تاريخ النهاية غير صحيح.',
            'ends_at.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية أو مساويًا له.',
            'admin_note.max' => 'الملاحظة الإدارية يجب ألا تتجاوز 2000 حرف.',
            'quick_action.in' => 'الإجراء السريع غير متاح.',
        ];
    }
}
