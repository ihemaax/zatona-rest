<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use App\Support\ContactValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $addresses = $user->addresses()->latest()->get();

        return view('front.account', [
            'user' => $user,
            'addresses' => $addresses,
            'defaultAddressId' => optional($addresses->firstWhere('is_default', true))->id,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ContactValidation::egyptianMobileRules(),
        ], ContactValidation::messages());

        $request->user()->update($data);

        return back()->with('success', 'تم تحديث بيانات الحساب بنجاح.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح.');
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $validated = $this->validateAddress($request);
        $user = $request->user();

        if (($validated['is_default'] ?? false) || !$user->addresses()->exists()) {
            $user->addresses()->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        $user->addresses()->create($validated);

        return back()->with('success', 'تمت إضافة العنوان بنجاح.');
    }

    public function updateAddress(Request $request, UserAddress $address): RedirectResponse
    {
        abort_if($address->user_id !== $request->user()->id, 403);

        $validated = $this->validateAddress($request);

        if (($validated['is_default'] ?? false) === true) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address->update($validated);

        return back()->with('success', 'تم تحديث العنوان بنجاح.');
    }

    public function destroyAddress(Request $request, UserAddress $address): RedirectResponse
    {
        abort_if($address->user_id !== $request->user()->id, 403);

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $next = $request->user()->addresses()->latest()->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return back()->with('success', 'تم حذف العنوان بنجاح.');
    }

    public function setDefaultAddress(Request $request, UserAddress $address): RedirectResponse
    {
        abort_if($address->user_id !== $request->user()->id, 403);

        $request->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', 'تم تعيين العنوان الافتراضي.');
    }

    protected function validateAddress(Request $request): array
    {
        return $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', ...ContactValidation::egyptianMobileRules()],
            'area' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:120'],
            'floor' => ['nullable', 'string', 'max:120'],
            'apartment' => ['nullable', 'string', 'max:120'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'address_line' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_default' => ['nullable', 'boolean'],
        ], ContactValidation::messages());
    }
}
