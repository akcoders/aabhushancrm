# Kalasha Fine Jewels CRM — Shared Hosting Deployment

This deployment package does not require Node.js, npm, Vite, or React tooling on the hosting server.

## Omnichannel setup

Copy the Interakt, Meta and Jitsi variables from `.env.production.example` into the private server `.env`.
Set the Interakt HTTPS webhook to `/api/webhooks/interakt` and the Meta webhook to `/api/webhooks/meta`.
Run `php artisan schedule:run` every minute from hosting cron so scheduled campaigns are dispatched.
Interakt requires an approved WhatsApp template and an API/webhook-enabled plan. Instagram messaging and
ad publishing require a Meta app, a Page-linked professional Instagram account, the relevant permissions,
and an appropriate Page/ad-account access token.

The React application is already compiled into static HTML, CSS, JavaScript, logo, and favicon files inside:

```text
public/crm/
```

The package also includes production-only Composer dependencies in `vendor/`.

## Server requirements

- PHP 8.2 or newer
- MySQL or MariaDB
- Apache with `mod_rewrite`, or equivalent Nginx routing
- PHP extensions: PDO MySQL, OpenSSL, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath, Fileinfo, ZIP and GD
- Ability to point the domain document root to the Laravel `public/` directory

Node.js is not required.

## Package structure

```text
app/                    Laravel application code
bootstrap/              Laravel bootstrap/cache
config/                 Application configuration
database/               Database migrations
public/                 Domain document root
public/crm/             Compiled React application
resources/              Laravel resources
routes/                 Web and API routes
storage/                Logs, cache, sessions, uploads
vendor/                 Production PHP dependencies
.env.production.example Production environment template
database-import/        MySQL data import
```

## Recommended deployment

### 1. Upload and extract

Upload the ZIP outside `public_html` when possible:

```text
/home/account/kalasha-crm/
```

Set the domain or subdomain document root to:

```text
/home/account/kalasha-crm/public
```

This is the safest Laravel hosting layout.

### 2. Create the MySQL database

From the hosting control panel:

1. Create a MySQL database.
2. Create a database user.
3. Grant the user all permissions on that database.
4. Open phpMyAdmin.
5. Import:

```text
database-import/jewellery_crm_pro.sql
```

The SQL file contains the current CRM records.

### 3. Create `.env`

Copy:

```text
.env.production.example
```

to:

```text
.env
```

Update:

```env
APP_URL=https://crm.your-domain.com

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

Do not upload the local `.env` file.

### 4. Generate the application key

If SSH or hosting terminal access is available:

```bash
php artisan key:generate
php artisan optimize
```

If terminal access is unavailable, generate a Laravel key locally and paste it into:

```env
APP_KEY=base64:...
```

Never reuse a key belonging to an unrelated application.

### 5. Set permissions

The web-server user must be able to write to:

```text
storage/
bootstrap/cache/
```

Typical shared-hosting permissions:

```bash
chmod -R 775 storage bootstrap/cache
```

Do not use `777` unless the hosting provider explicitly requires it.

### 6. Run migrations

The supplied SQL file already contains the current schema and data.

For future application updates:

```bash
php artisan migrate --force
```

### 7. Scheduler

Create this cron job to run every minute:

```cron
* * * * * cd /home/account/kalasha-crm && php artisan schedule:run >> /dev/null 2>&1
```

This enables:

- Retention scans
- Smart-task generation
- Scheduled CRM intelligence
- A duplicate-safe daily summary notification for every active user at 8:00 AM

You can verify the daily notification job manually:

```bash
php artisan crm:daily-notifications
```

### 8. Queue worker

The current application uses the database queue configuration. If background queue jobs are added, configure a persistent worker or a recurring cron command:

```cron
* * * * * cd /home/account/kalasha-crm && php artisan queue:work --stop-when-empty --tries=3 >> /dev/null 2>&1
```

## Hosting without configurable document root

The recommended solution is to use a subdomain whose document root can point to `public/`.

If the provider forces `public_html`, contact the provider and request a custom document root. Moving Laravel core files into a web-accessible folder is less secure and is not recommended.

## Post-deployment checks

Verify:

```text
https://your-domain.example/
https://your-domain.example/login
https://your-domain.example/privilege-cards
https://your-domain.example/up
```

Then test:

1. Administrator login
2. Dashboard metrics
3. Lead list and salesperson assignment
4. Customer profile
5. Sales and custom orders
6. Loyalty and gift cards
7. Privilege Card grid
8. Retention messages
9. Smart tasks
10. Customer Excel template download, import, and duplicate-mobile update
11. Customer Categories and HNI recalculation
12. Staff task completion, reward points, and reward redemption
13. Notifications and installable-app prompt
14. Login with a Front Office CRE account and confirm that Sales is hidden/blocked

## Excel import requirements

Customer import accepts real `.xlsx` and `.xls` files and is processed by PHP; Microsoft Excel and Node.js are not needed on the server. Download the current template from **Customers → Download template**. `Name` and `Mobile` are required. A matching mobile number updates the existing customer; otherwise a new customer is created. Each upload is limited to 5,000 rows.

The ZIP and XML PHP extensions are required by the spreadsheet reader. If import fails, enable `zip`, `xml`, `xmlreader`, `xmlwriter` and `gd` from the hosting PHP extensions screen.

## Installable app and daily alerts

The compiled frontend includes a web-app manifest and service worker, so supported browsers can install the CRM on a tablet, phone, or desktop. Users can enable browser notifications from the **Notifications** screen.

The daily scheduler creates alerts inside the CRM. Browser notifications are shown while the installed web app is running. Delivery while the browser/app is fully closed would require a separate Web Push service and VAPID configuration, which is not included.

## WhatsApp scope

Interakt and other third-party WhatsApp APIs are intentionally not included in this build. Management-critical updates are delivered through the CRM Notifications module. Fully automatic WhatsApp delivery requires an approved WhatsApp Business API provider and credentials.

## Security checklist

- `APP_ENV=production`
- `APP_DEBUG=false`
- Use HTTPS
- Use a strong MySQL password
- Keep `.env` outside public access
- Point document root only to `public/`
- Remove the deployment ZIP after extraction
- Restrict access to database backups
- Configure regular MySQL and file backups

## Rebuilding the React frontend in the future

Node.js is needed only on a development machine:

```bash
cd frontend
npm install
npm run build
```

The generated `public/crm/` directory can then be uploaded to replace the old compiled frontend.
