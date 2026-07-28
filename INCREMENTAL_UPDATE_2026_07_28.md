# Kalasha CRM — Incremental Update (28 July 2026)

This package upgrades an existing Kalasha CRM installation without replacing the database, `.env`, uploaded files, or application key.

## Before updating

From the existing project directory:

```bash
php artisan down
```

Create backups of:

- `.env`
- The complete MySQL database
- `storage/app/public`
- The existing project files

Do not delete the existing installation.

## Upload

Extract the incremental ZIP on your computer. It contains a `kalasha-crm-update` folder.

Upload the **contents inside** that folder into the existing Laravel project root, for example:

```text
/home/u897223014/domains/webignitors.in/public_html/kalasha-crm/
```

Allow the file manager to merge folders and overwrite matching application files. The package does not contain `.env`, database exports, user uploads, or Node.js source dependencies.

## Apply the update

Run these commands from the Laravel project root:

```bash
php artisan optimize:clear
php artisan migrate --force
php artisan storage:link
php artisan optimize
php artisan up
```

The migrations preserve existing records and add:

- Reward points and notification preferences
- Customer category override support
- Task reward fields
- Customer category rules
- Reward catalog and redemption tables
- Application notifications
- Management and Front Office CRE roles/permissions
- Simplified custom-order statuses
- Coupon-free offers

Existing custom orders are mapped automatically:

- New, Designing, Approved and In Production → Processing
- Ready and Delivered → Order Ready
- Cancelled remains Cancelled

## Scheduler

Ensure this cron job exists:

```cron
* * * * * cd /home/u897223014/domains/webignitors.in/public_html/kalasha-crm && php artisan schedule:run >> /dev/null 2>&1
```

Test daily notifications manually:

```bash
php artisan crm:daily-notifications
```

## Verify

```bash
php artisan migrate:status
php artisan about
```

Then verify:

1. Login and dashboard
2. Customers → Excel template/import
3. Customer Categories
4. Notifications
5. My Rewards
6. Custom Orders show only Processing, Order Ready and Cancelled
7. Offers require start and end dates and no longer show coupon codes
8. Front Office CRE can manage customers but cannot access sales

Perform a hard refresh after deployment:

- macOS: `Cmd + Shift + R`
- Windows: `Ctrl + F5`

## Recovery

If migration fails, keep the application in maintenance mode, save the exact terminal error, and inspect:

```bash
tail -n 100 storage/logs/laravel.log
```

Restore the file and database backups if a rollback is required. Do not run migration rollback on production without reviewing its impact.
