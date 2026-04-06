<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        User::query()
            ->where('user_type', User::TYPE_STAFF)
            ->whereNotNull('role')
            ->each(function (User $user): void {
                $user->forceFill([
                    'permissions' => User::defaultPermissionsByRole($user->role),
                ])->saveQuietly();
            });
    }

    public function down(): void
    {
        // لا يمكن الرجوع بدقة لأن الصلاحيات كانت يدوية قبل التوحيد.
    }
};
