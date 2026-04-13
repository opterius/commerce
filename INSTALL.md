# Installing Opterius Commerce

Two installation methods are available: the **web installer** (recommended) and the **CLI method** for those comfortable with the terminal.

---

## Requirements

| Requirement | Minimum |
|---|---|
| PHP | 8.3+ |
| MySQL | 8.0+ |
| Node.js | 20+ (for asset builds) |
| Web server | Apache 2.4+ or Nginx |

**Required PHP extensions:** `pdo`, `pdo_mysql`, `openssl`, `mbstring`, `xml`, `curl`, `fileinfo`, `json`, `tokenizer`, `ctype`, `intl`

---

## Method 1 — Web Installer (Recommended)

### 1. Upload files

Clone or download the repository and upload all files to your web server's document root (or a subdirectory):

```bash
git clone https://github.com/opterius/commerce /var/www/commerce
cd /var/www/commerce
```

### 2. Install PHP dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### 3. Build frontend assets

Run this on your local machine or a build server — **not** on the live server:

```bash
npm install
npm run build
```

Copy the compiled `public/build` directory to the server.

### 4. Set directory permissions

```bash
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs storage/framework
```

### 5. Point your web server to `public/`

**Apache** — set `DocumentRoot` to the `public/` directory, or use an `.htaccess` alias.

**Nginx** example:

```nginx
server {
    listen 80;
    server_name yourstore.com;
    root /var/www/commerce/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 6. Run the installer

Open your browser and navigate to:

```
https://yourstore.com/install/
```

The installer will guide you through six steps:

| Step | Description |
|---|---|
| **1 — Pre-flight** | Checks PHP version, required extensions, and that all necessary directories are writable. All checks must pass before continuing. |
| **2 — Database** | Enter your MySQL host, port, database name, username, and password. The installer tests the connection before proceeding. The database must already exist. |
| **3 — Migrate** | Runs `php artisan migrate --seed` to create all tables and seed default data (currencies, settings). Terminal output is shown on screen. |
| **4 — Admin Account** | Create your administrator account with name, email, and password. This replaces the seeder's placeholder credentials. |
| **5 — Settings** | Set your company name, support email, and the application URL. |
| **6 — Finish** | Clicking **Go to Admin Panel** deletes the `/install` directory and redirects you to the admin login. |

> **Note:** The installer requires `exec()` to be available in PHP so it can run `php artisan migrate`. If your host disables `exec()`, use the CLI method below.

---

## Method 2 — CLI Installation

Use this method if you prefer the terminal or if `exec()` is disabled on your server.

### 1. Clone and install dependencies

```bash
git clone https://github.com/opterius/commerce /var/www/commerce
cd /var/www/commerce
composer install --no-dev --optimize-autoloader
```

### 2. Build frontend assets

Run locally, then copy `public/build` to the server:

```bash
npm install && npm run build
```

### 3. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database credentials and application URL:

```env
APP_NAME="Opterius Commerce"
APP_URL=https://yourstore.com
APP_INSTALLED=true

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password

QUEUE_CONNECTION=database
SESSION_DRIVER=database
```

### 4. Set permissions

```bash
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs storage/framework
```

### 5. Run migrations and seed

```bash
php artisan migrate --seed --force
```

### 6. Create your admin account

```bash
php artisan tinker
```

```php
App\Models\Staff::create([
    'name'     => 'Your Name',
    'email'    => 'admin@yourcompany.com',
    'password' => 'your-password',
    'role'     => 'super_admin',
]);
```

### 7. Configure the web server

Point your web server's document root to the `public/` directory (see Nginx example in Method 1, Step 5).

### 8. Delete the installer

```bash
rm -rf install/
```

---

## Post-Installation

### Queue worker

Commerce uses Laravel queues for domain provisioning, email, and background jobs. Set up a queue worker to process jobs:

```bash
php artisan queue:work --sleep=3 --tries=3
```

For production, run the worker as a supervised process. Example with Supervisor:

```ini
[program:commerce-queue]
command=php /var/www/commerce/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/commerce-queue.log
```

### Scheduler

Add a cron entry to run the Laravel scheduler every minute:

```bash
* * * * * cd /var/www/commerce && php artisan schedule:run >> /dev/null 2>&1
```

This enables:
- Daily expiring domain checks (04:00)
- Daily domain status sync from registrar (05:00)
- Any future scheduled tasks

### Stripe payments

Add your Stripe keys to `.env`:

```env
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
```

### Mail

Configure an SMTP provider or transactional email service in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourprovider.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourcompany.com
MAIL_FROM_NAME="Your Company"
```

### Domain registrar

Configure your registrar in the admin panel under **Settings → Registrar**. Five registrars are supported:

| Registrar | Credentials needed |
|---|---|
| ResellerClub | Auth User ID, API Key |
| Enom | Username (UID), Password |
| OpenSRS | Username, Private Key |
| Namecheap | API Username, API Key, Server IP (must be whitelisted) |
| CentralNic Reseller | Login, Password |

Enable sandbox mode on each registrar during testing.

---

## Upgrading

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

Rebuild frontend assets locally and copy `public/build` to the server if the upgrade includes UI changes.

---

## Troubleshooting

**500 error after installation**
Run `php artisan config:clear && php artisan cache:clear` and check `storage/logs/laravel.log`.

**Blank page / missing styles**
The `public/build` directory may be missing. Run `npm run build` locally and upload the result.

**Queue jobs not processing**
Ensure the queue worker is running. Check `storage/logs/laravel.log` for failed job details.

**Installer won't delete itself**
Manually delete the `/install` directory: `rm -rf /var/www/commerce/install`

**Permission errors**
```bash
chown -R www-data:www-data /var/www/commerce
chmod -R 755 storage bootstrap/cache
```
