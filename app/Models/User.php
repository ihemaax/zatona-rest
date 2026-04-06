<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const TYPE_CUSTOMER = 'customer';
    public const TYPE_STAFF = 'staff';

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_OWNER = 'owner';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_BRANCH_STAFF = 'branch_staff';
    public const ROLE_CASHIER = 'cashier';
    public const ROLE_KITCHEN = 'kitchen';
    public const ROLE_DELIVERY = 'delivery';

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'role',
        'branch_id',
        'permissions',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $user): void {
            if ($user->user_type !== self::TYPE_STAFF || empty($user->role)) {
                $user->permissions = [];
                return;
            }

            $user->permissions = self::defaultPermissionsByRole($user->role);
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public static function availableRoles(): array
    {
        return [
            self::ROLE_SUPER_ADMIN => 'سوبر أدمن',
            self::ROLE_OWNER => 'المالك',
            self::ROLE_MANAGER => 'مدير',
            self::ROLE_BRANCH_STAFF => 'موظف فرع',
            self::ROLE_CASHIER => 'كاشير',
            self::ROLE_KITCHEN => 'مطبخ',
            self::ROLE_DELIVERY => 'دليفري',
        ];
    }

    public static function permissionLabels(): array
    {
        return [
            'view_orders' => 'عرض الطلبات',
            'update_order_status' => 'تحديث حالة الطلب',
            'view_all_branches_orders' => 'عرض طلبات كل الفروع',
            'manage_products' => 'إدارة المنتجات',
            'manage_categories' => 'إدارة الأقسام',
            'manage_branches' => 'إدارة الفروع',
            'manage_settings' => 'إدارة الإعدادات',
            'manage_digital_menu' => 'إدارة المنيو الإلكتروني',
            'manage_staff' => 'إدارة الموظفين',
            'manage_delivery' => 'متابعة وإدارة الدليفري',
            'view_reports' => 'عرض التقارير',
        ];
    }

    public static function defaultPermissionsByRole(string $role): array
    {
        return match ($role) {
            self::ROLE_SUPER_ADMIN => array_keys(self::permissionLabels()),

            self::ROLE_OWNER => [
                'view_orders',
                'update_order_status',
                'view_all_branches_orders',
                'manage_products',
                'manage_categories',
                'manage_branches',
                'manage_settings',
                'manage_digital_menu',
                'manage_staff',
                'manage_delivery',
                'view_reports',
            ],

            self::ROLE_MANAGER => [
                'view_orders',
                'update_order_status',
                'view_all_branches_orders',
                'manage_products',
                'manage_categories',
                'manage_digital_menu',
                'manage_delivery',
                'view_reports',
            ],

            self::ROLE_BRANCH_STAFF => [
                'view_orders',
                'update_order_status',
            ],

            self::ROLE_CASHIER => [
                'view_orders',
                'update_order_status',
            ],

            self::ROLE_KITCHEN => [
                'view_orders',
                'update_order_status',
            ],

            self::ROLE_DELIVERY => [
                'view_orders',
                'update_order_status',
            ],

            default => [],
        };
    }

    public function isCustomer(): bool
    {
        return $this->user_type === self::TYPE_CUSTOMER;
    }

    public function isStaff(): bool
    {
        return $this->user_type === self::TYPE_STAFF;
    }

    public function isSuperAdmin(): bool
    {
        return $this->isStaff() && $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isOwner(): bool
    {
        return $this->isStaff() && $this->role === self::ROLE_OWNER;
    }

    public function canAccessAdminPanel(): bool
    {
        return $this->isStaff()
            && $this->is_active
            && in_array($this->role, [
                self::ROLE_SUPER_ADMIN,
                self::ROLE_OWNER,
                self::ROLE_MANAGER,
                self::ROLE_BRANCH_STAFF,
                self::ROLE_CASHIER,
                self::ROLE_KITCHEN,
                self::ROLE_DELIVERY,
            ], true);
    }

    public function hasPermission(string $permission): bool
    {
        if (!$this->isStaff()) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        $permissions = $this->permissions ?? [];

        return in_array($permission, $permissions, true);
    }

    public function canManageStaff(): bool
    {
        return $this->isSuperAdmin() || $this->hasPermission('manage_staff');
    }

    public function addresses()
    {
        return $this->hasMany(\App\Models\Address::class);
    }
}
