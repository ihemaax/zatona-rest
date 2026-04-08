<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use App\Support\ContactValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        abort_unless($user->canManageStaff(), 403);

        $staff = User::with('branch')
            ->where('user_type', User::TYPE_STAFF)
            ->latest()
            ->paginate(20);

        return view('admin.staff.index', [
            'staff' => $staff,
            'roles' => User::availableRoles(),
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        abort_unless($user->canManageStaff(), 403);

        return view('admin.staff.create', [
            'branches' => Branch::orderBy('name')->get(),
            'roles' => User::availableRoles(),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        abort_unless($user->canManageStaff(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [...ContactValidation::emailRules(), 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(array_keys(User::availableRoles()))],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'is_active' => ['nullable', 'boolean'],
        ], ContactValidation::messages());

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => User::TYPE_STAFF,
            'role' => $validated['role'],
            'branch_id' => $validated['branch_id'] ?? null,
            'permissions' => User::defaultPermissionsByRole($validated['role']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.staff.index')->with('success', 'تم إضافة الموظف بنجاح.');
    }

    public function edit(User $staff)
    {
        $user = auth()->user();

        abort_unless($user->canManageStaff(), 403);
        abort_if(!$staff->isStaff(), 403);
        abort_if($staff->isSuperAdmin(), 403);

        return view('admin.staff.edit', [
            'staff' => $staff,
            'branches' => Branch::orderBy('name')->get(),
            'roles' => User::availableRoles(),
        ]);
    }

    public function update(Request $request, User $staff)
    {
        $user = auth()->user();

        abort_unless($user->canManageStaff(), 403);
        abort_if(!$staff->isStaff(), 403);
        abort_if($staff->isSuperAdmin(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [...ContactValidation::emailRules(), Rule::unique('users', 'email')->ignore($staff->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::in(array_keys(User::availableRoles()))],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'is_active' => ['nullable', 'boolean'],
        ], ContactValidation::messages());

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'user_type' => User::TYPE_STAFF,
            'role' => $validated['role'],
            'branch_id' => $validated['branch_id'] ?? null,
            'permissions' => User::defaultPermissionsByRole($validated['role']),
            'is_active' => $request->boolean('is_active', true),
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $staff->update($data);

        return redirect()->route('admin.staff.index')->with('success', 'تم تحديث بيانات الموظف بنجاح.');
    }

    public function destroy(User $staff)
    {
        $user = auth()->user();

        abort_unless($user->canManageStaff(), 403);
        abort_if(!$staff->isStaff(), 403);
        abort_if($staff->isSuperAdmin(), 403);
        abort_if($staff->id === $user->id, 403, 'لا يمكنك حذف حسابك الحالي.');

        $staff->delete();

        return redirect()->route('admin.staff.index')->with('success', 'تم حذف الموظف بنجاح.');
    }
}
