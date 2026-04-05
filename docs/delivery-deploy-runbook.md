# Delivery Flow Deploy Runbook

## 1) Before deploy
- Pull latest code.
- Backup database.

## 2) Install deps
```bash
composer install --no-dev --optimize-autoloader
```

## 3) Database changes
```bash
php artisan migrate --force
```

## 4) Clear caches
```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 5) Smoke checks
1. Login as admin.
2. Open `/admin/orders/{id}` for a delivery order.
3. Assign delivery user from "إدارة الدليفري".
4. Verify order has `delivery_user_id` and status moved to `out_for_delivery` (if pending/confirmed/preparing).
5. Login as delivery user and open `/admin/delivery-dashboard`.
6. Verify assigned order appears in current orders list.
7. Open order details as delivery user and verify no 403 for assigned order.
8. Open another delivery user's order and verify 403.
9. Open `/admin/delivery-management` as manager with `manage_delivery` permission.
10. Open `/admin/delivery-management` as manager without `manage_delivery` and verify 403.

## 6) Post deploy
- Monitor logs for 15 minutes.
- Confirm no `RouteNotFoundException` / `ParseError` entries.
