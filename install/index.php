<?php
/**
 * Opterius Commerce — Web Installer
 *
 * Visit /install/ in your browser to set up Commerce.
 * This directory is deleted automatically when installation completes.
 */

session_start();

define('BASE_PATH', realpath(__DIR__ . '/..'));
define('ENV_FILE',  BASE_PATH . '/.env');

// ── Guard: already installed ─────────────────────────────────────────────────
if (file_exists(ENV_FILE)) {
    $parsed = @parse_ini_file(ENV_FILE);
    if (! empty($parsed['APP_INSTALLED']) && $parsed['APP_INSTALLED'] === 'true') {
        page('Already Installed', function () {
            ?>
            <div class="text-center py-12">
                <div class="text-5xl mb-4">✓</div>
                <h2 class="text-xl font-semibold text-gray-800 mb-2">Commerce is already installed</h2>
                <p class="text-gray-500 mb-6">Delete the <code class="bg-gray-100 px-1 rounded">/install</code> directory to reinstall.</p>
                <a href="/" class="btn-primary">Go to application</a>
            </div>
            <?php
        }, 1, 6);
        exit;
    }
}

// ── State ────────────────────────────────────────────────────────────────────
$step   = max(1, min(6, (int) ($_GET['step'] ?? $_SESSION['install_step'] ?? 1)));
$errors = [];

// ── POST handlers ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'preflight_next') {
        $_SESSION['install_step'] = 2;
        go(2);
    }

    if ($action === 'database') {
        $db = [
            'host' => trim($_POST['db_host'] ?? '127.0.0.1'),
            'port' => trim($_POST['db_port'] ?? '3306'),
            'name' => trim($_POST['db_name'] ?? ''),
            'user' => trim($_POST['db_user'] ?? ''),
            'pass' => $_POST['db_pass'] ?? '',
        ];

        if (empty($db['name']) || empty($db['user'])) {
            $errors[] = 'Database name and username are required.';
        } else {
            try {
                $pdo = pdo($db);
                unset($pdo);
                $_SESSION['db']           = $db;
                $_SESSION['install_step'] = 3;
                go(3);
            } catch (\PDOException $e) {
                $errors[] = 'Connection failed: ' . $e->getMessage();
            }
        }
        $step = 2;
    }

    if ($action === 'migrate') {
        $db     = $_SESSION['db'] ?? [];
        $appKey = 'base64:' . base64_encode(random_bytes(32));
        $appUrl = detectUrl();

        file_put_contents(ENV_FILE, buildEnv($appKey, $appUrl, $db));

        $php    = PHP_BINARY;
        $art    = BASE_PATH . DIRECTORY_SEPARATOR . 'artisan';
        $out    = [];
        $code   = 0;

        exec('"' . $php . '" "' . $art . '" migrate --seed --force --no-interaction 2>&1', $out, $code);

        if ($code !== 0) {
            @unlink(ENV_FILE);
            $errors[] = 'Migration failed. Output:<br><pre class="text-xs mt-2 bg-red-50 p-3 rounded overflow-x-auto">'
                      . htmlspecialchars(implode("\n", $out)) . '</pre>';
            $step = 3;
        } else {
            $_SESSION['migrate_output'] = $out;
            $_SESSION['app_key']        = $appKey;
            $_SESSION['app_url']        = $appUrl;
            $_SESSION['install_step']   = 4;
            go(4);
        }
    }

    if ($action === 'account') {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $pass    = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';

        if (empty($name) || empty($email) || empty($pass)) {
            $errors[] = 'All fields are required.';
        } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        } elseif (strlen($pass) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        } elseif ($pass !== $confirm) {
            $errors[] = 'Passwords do not match.';
        } else {
            $db   = $_SESSION['db'] ?? [];
            $conn = pdo($db);
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $now  = date('Y-m-d H:i:s');

            // The seeder created a placeholder admin — update it with real credentials.
            $existing = $conn->query("SELECT COUNT(*) FROM staff")->fetchColumn();
            if ($existing > 0) {
                $conn->prepare("UPDATE staff SET name=?, email=?, password=?, role='super_admin', updated_at=? LIMIT 1")
                     ->execute([$name, $email, $hash, $now]);
            } else {
                $conn->prepare("INSERT INTO staff (name,email,password,role,is_active,created_at,updated_at) VALUES (?,?,?,'super_admin',1,?,?)")
                     ->execute([$name, $email, $hash, $now, $now]);
            }

            $_SESSION['admin']        = ['name' => $name, 'email' => $email];
            $_SESSION['install_step'] = 5;
            go(5);
        }
        $step = 4;
    }

    if ($action === 'settings') {
        $company = trim($_POST['company_name'] ?? '');
        $email   = trim($_POST['company_email'] ?? '');
        $url     = rtrim(trim($_POST['app_url'] ?? $_SESSION['app_url'] ?? ''), '/');

        if (empty($company)) {
            $errors[] = 'Company name is required.';
        } else {
            $db   = $_SESSION['db'] ?? [];
            $conn = pdo($db);
            $now  = date('Y-m-d H:i:s');

            // Upsert company settings
            $settings = [
                ['company_name',    $company, 'company'],
                ['company_email',   $email,   'company'],
                ['company_website', $url,     'company'],
            ];
            $stmt = $conn->prepare(
                "INSERT INTO settings (`key`,`value`,`group`,created_at,updated_at) VALUES (?,?,?,?,?)
                 ON DUPLICATE KEY UPDATE `value`=VALUES(`value`), updated_at=VALUES(updated_at)"
            );
            foreach ($settings as [$k, $v, $g]) {
                $stmt->execute([$k, $v, $g, $now, $now]);
            }

            // Update .env with final APP_URL and mark installed
            setEnvValue(ENV_FILE, 'APP_URL', $url);
            setEnvValue(ENV_FILE, 'APP_NAME', $company);
            setEnvValue(ENV_FILE, 'APP_INSTALLED', 'true');

            // Clear Laravel config cache so it picks up .env changes
            $php = PHP_BINARY;
            $art = BASE_PATH . DIRECTORY_SEPARATOR . 'artisan';
            exec('"' . $php . '" "' . $art . '" config:clear --quiet 2>&1');
            exec('"' . $php . '" "' . $art . '" cache:clear --quiet 2>&1');

            $_SESSION['install_step'] = 6;
            go(6);
        }
        $step = 5;
    }

    if ($action === 'finish') {
        $url = $_SESSION['app_url'] ?? '/';
        deleteDir(__DIR__);
        header('Location: ' . $url . '/admin');
        exit;
    }
}

// ── Pre-flight ────────────────────────────────────────────────────────────────
$checks = [
    [
        'label' => 'PHP version ≥ 8.3',
        'pass'  => version_compare(PHP_VERSION, '8.3.0', '>='),
        'value' => PHP_VERSION,
        'hard'  => true,
    ],
];

foreach (['pdo', 'pdo_mysql', 'openssl', 'mbstring', 'xml', 'curl', 'fileinfo', 'json', 'tokenizer', 'ctype', 'intl'] as $ext) {
    $checks[] = ['label' => "Extension: {$ext}", 'pass' => extension_loaded($ext), 'value' => extension_loaded($ext) ? 'Loaded' : 'Missing', 'hard' => true];
}

foreach (['storage', 'storage/logs', 'storage/framework/cache', 'storage/framework/sessions', 'storage/framework/views', 'bootstrap/cache'] as $path) {
    $full     = BASE_PATH . '/' . $path;
    $writable = is_writable($full);
    $checks[] = ['label' => "Writable: {$path}/", 'pass' => $writable, 'value' => $writable ? 'OK' : 'Not writable', 'hard' => true];
}

$envWritable = file_exists(ENV_FILE) ? is_writable(ENV_FILE) : is_writable(BASE_PATH);
$checks[] = ['label' => 'Writable: .env', 'pass' => $envWritable, 'value' => $envWritable ? 'OK' : 'Not writable', 'hard' => true];

$execEnabled = function_exists('exec') && ! in_array('exec', array_map('trim', explode(',', ini_get('disable_functions') ?: '')));
$checks[] = ['label' => 'exec() available', 'pass' => $execEnabled, 'value' => $execEnabled ? 'Available' : 'Disabled — cannot run migrations', 'hard' => true];

$hardFails = array_filter($checks, fn($c) => ! $c['pass'] && ($c['hard'] ?? false));

// ── Helpers ───────────────────────────────────────────────────────────────────
function go(int $step): void
{
    header("Location: ?step={$step}");
    exit;
}

function pdo(array $db): PDO
{
    return new PDO(
        "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4",
        $db['user'],
        $db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 10]
    );
}

function detectUrl(): string
{
    $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

function buildEnv(string $appKey, string $appUrl, array $db): string
{
    $dbPass = str_replace('"', '\\"', $db['pass'] ?? '');

    return <<<ENV
APP_NAME="Opterius Commerce"
APP_ENV=production
APP_KEY={$appKey}
APP_DEBUG=false
APP_URL={$appUrl}
APP_INSTALLED=false

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST={$db['host']}
DB_PORT={$db['port']}
DB_DATABASE={$db['name']}
DB_USERNAME={$db['user']}
DB_PASSWORD="{$dbPass}"

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database

MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="Opterius Commerce"

STRIPE_KEY=
STRIPE_SECRET=
ENV;
}

function setEnvValue(string $file, string $key, string $value): void
{
    $contents = file_get_contents($file);
    $safe     = str_contains($value, ' ') ? '"' . $value . '"' : $value;

    if (preg_match("/^{$key}=.*/m", $contents)) {
        $contents = preg_replace("/^{$key}=.*/m", "{$key}={$safe}", $contents);
    } else {
        $contents .= "\n{$key}={$safe}";
    }

    file_put_contents($file, $contents);
}

function deleteDir(string $dir): void
{
    if (! is_dir($dir)) return;
    foreach (array_diff(scandir($dir), ['.', '..']) as $item) {
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? deleteDir($path) : unlink($path);
    }
    rmdir($dir);
}

function page(string $title, callable $content, int $step, int $total): void
{
    // Renders the full HTML page — called at the bottom of this file.
}

$stepLabels = ['Pre-flight', 'Database', 'Migrate', 'Admin Account', 'Settings', 'Finish'];

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opterius Commerce — Installer</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 16px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,.08), 0 4px 20px rgba(0,0,0,.06);
            width: 100%;
            max-width: 620px;
        }

        .card-header {
            padding: 28px 32px 20px;
            border-bottom: 1px solid #f1f5f9;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .brand-icon {
            width: 36px; height: 36px;
            background: #4f46e5;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 16px;
        }

        .brand-name { font-size: 17px; font-weight: 700; color: #1e293b; }
        .brand-sub  { font-size: 12px; color: #94a3b8; margin-top: 1px; }

        /* Progress steps */
        .steps {
            display: flex;
            gap: 0;
        }

        .step-item {
            flex: 1;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            position: relative;
        }

        .step-item::before {
            content: '';
            display: block;
            height: 3px;
            background: #e2e8f0;
            margin-bottom: 6px;
        }

        .step-item.done::before  { background: #4f46e5; }
        .step-item.active::before { background: linear-gradient(90deg, #4f46e5 50%, #e2e8f0 50%); }

        .step-item.active { color: #4f46e5; font-weight: 600; }
        .step-item.done   { color: #4f46e5; }

        .card-body  { padding: 28px 32px; }
        .card-footer { padding: 16px 32px; background: #f8fafc; border-top: 1px solid #f1f5f9; border-radius: 0 0 12px 12px; display: flex; justify-content: flex-end; gap: 10px; }

        h2 { font-size: 17px; font-weight: 700; margin-bottom: 4px; }
        .subtitle { font-size: 13px; color: #64748b; margin-bottom: 20px; }

        /* Form */
        .field { margin-bottom: 16px; }
        label { display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 5px; }
        input[type=text], input[type=email], input[type=password], input[type=number] {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: border-color .15s;
        }
        input:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.1); }
        .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .hint { font-size: 11px; color: #94a3b8; margin-top: 4px; }

        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 13px; font-weight: 500;
            border: none; cursor: pointer;
            text-decoration: none;
            transition: background .15s;
        }
        .btn-primary { background: #4f46e5; color: #fff; }
        .btn-primary:hover { background: #4338ca; }
        .btn-secondary { background: #f1f5f9; color: #374151; }
        .btn-secondary:hover { background: #e2e8f0; }
        .btn-success { background: #059669; color: #fff; }
        .btn-success:hover { background: #047857; }

        /* Check list */
        .check-list { display: flex; flex-direction: column; gap: 6px; }
        .check-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
        }
        .check-item.pass { background: #f0fdf4; }
        .check-item.fail { background: #fef2f2; }
        .check-item-label { display: flex; align-items: center; gap: 8px; color: #374151; }
        .check-icon { font-size: 14px; }
        .check-value { font-size: 11px; font-weight: 500; }
        .check-item.pass .check-value { color: #059669; }
        .check-item.fail .check-value { color: #dc2626; }

        /* Errors */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 13px;
            color: #dc2626;
            margin-bottom: 16px;
        }

        /* Migration output */
        .migrate-output {
            background: #0f172a;
            color: #94a3b8;
            border-radius: 8px;
            padding: 16px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.6;
            max-height: 260px;
            overflow-y: auto;
        }

        .migrate-output .ok   { color: #34d399; }
        .migrate-output .info { color: #60a5fa; }

        /* Finish */
        .finish-box {
            text-align: center;
            padding: 20px 0;
        }
        .finish-icon { font-size: 52px; margin-bottom: 12px; }
        .finish-box h2 { font-size: 20px; margin-bottom: 6px; }
        .finish-box p  { color: #64748b; font-size: 14px; margin-bottom: 4px; }
        .cred-box {
            background: #f8fafc;
            border-radius: 8px;
            padding: 14px 20px;
            margin: 16px 0;
            text-align: left;
        }
        .cred-box p { font-size: 13px; color: #374151; margin-bottom: 4px; }
        .cred-box code { font-size: 13px; font-weight: 600; color: #4f46e5; }
        .warning-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 12px;
            color: #92400e;
            margin-top: 12px;
        }

        .divider { height: 1px; background: #f1f5f9; margin: 18px 0; }
    </style>
</head>
<body>

<div class="card">

    <!-- Header -->
    <div class="card-header">
        <div class="brand">
            <div class="brand-icon">O</div>
            <div>
                <div class="brand-name">Opterius Commerce</div>
                <div class="brand-sub">Installation Wizard</div>
            </div>
        </div>

        <!-- Step progress bar -->
        <div class="steps">
            <?php foreach ($stepLabels as $i => $label): ?>
                <?php
                    $n = $i + 1;
                    $cls = $n < $step ? 'done' : ($n === $step ? 'active' : '');
                ?>
                <div class="step-item <?= $cls ?>">
                    <?= htmlspecialchars($label) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Body -->
    <div class="card-body">

        <!-- ── Step 1: Pre-flight ─────────────────────────────────────────── -->
        <?php if ($step === 1): ?>

        <h2>Pre-flight Check</h2>
        <p class="subtitle">Verifying your server meets all requirements.</p>

        <?php if (! empty($hardFails)): ?>
            <div class="alert-error">
                <strong>Requirements not met.</strong> Resolve the failing checks before continuing.
            </div>
        <?php endif; ?>

        <div class="check-list">
            <?php foreach ($checks as $check): ?>
                <?php $cls = $check['pass'] ? 'pass' : 'fail'; ?>
                <div class="check-item <?= $cls ?>">
                    <div class="check-item-label">
                        <span class="check-icon"><?= $check['pass'] ? '✓' : '✗' ?></span>
                        <?= htmlspecialchars($check['label']) ?>
                    </div>
                    <span class="check-value"><?= htmlspecialchars($check['value']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <?php endif; ?>

        <!-- ── Step 2: Database ───────────────────────────────────────────── -->
        <?php if ($step === 2): ?>

        <h2>Database Configuration</h2>
        <p class="subtitle">Enter your MySQL database credentials. The database must already exist.</p>

        <?php errors($errors); ?>

        <form method="POST" action="?step=2">
            <input type="hidden" name="action" value="database">

            <div class="field-row">
                <div class="field">
                    <label>Host</label>
                    <input type="text" name="db_host" value="<?= h($_POST['db_host'] ?? '127.0.0.1') ?>" required>
                </div>
                <div class="field">
                    <label>Port</label>
                    <input type="number" name="db_port" value="<?= h($_POST['db_port'] ?? '3306') ?>" required>
                </div>
            </div>

            <div class="field">
                <label>Database Name</label>
                <input type="text" name="db_name" value="<?= h($_POST['db_name'] ?? '') ?>" placeholder="opterius_commerce" required>
            </div>

            <div class="field">
                <label>Username</label>
                <input type="text" name="db_user" value="<?= h($_POST['db_user'] ?? '') ?>" placeholder="root" required>
            </div>

            <div class="field">
                <label>Password</label>
                <input type="password" name="db_pass" value="">
                <p class="hint">Leave empty if your MySQL user has no password.</p>
            </div>

            <div style="display:flex;justify-content:flex-end">
                <button type="submit" class="btn btn-primary">Test & Continue →</button>
            </div>
        </form>

        <?php endif; ?>

        <!-- ── Step 3: Migrate ────────────────────────────────────────────── -->
        <?php if ($step === 3): ?>

        <h2>Database Migration</h2>
        <p class="subtitle">Create all database tables and seed default data.</p>

        <?php errors($errors); ?>

        <?php if (! empty($_SESSION['migrate_output'])): ?>

            <div class="migrate-output">
                <?php foreach ($_SESSION['migrate_output'] as $line): ?>
                    <?php
                        $cls = '';
                        if (str_contains($line, 'DONE') || str_contains($line, 'done')) $cls = 'ok';
                        if (str_contains($line, 'INFO') || str_contains($line, 'Running')) $cls = 'info';
                    ?>
                    <div class="<?= $cls ?>"><?= htmlspecialchars($line) ?></div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>

            <p style="font-size:13px;color:#64748b;margin-bottom:16px;">
                This will run <code>php artisan migrate --seed</code> against your database.
                All tables will be created and default data seeded.
            </p>

            <form method="POST" action="?step=3">
                <input type="hidden" name="action" value="migrate">
                <button type="submit" class="btn btn-primary">Run Migration →</button>
            </form>

        <?php endif; ?>

        <?php endif; ?>

        <!-- ── Step 4: Admin Account ──────────────────────────────────────── -->
        <?php if ($step === 4): ?>

        <h2>Admin Account</h2>
        <p class="subtitle">Create your administrator account. This replaces the default seeded credentials.</p>

        <?php errors($errors); ?>

        <form method="POST" action="?step=4">
            <input type="hidden" name="action" value="account">

            <div class="field">
                <label>Full Name</label>
                <input type="text" name="name" value="<?= h($_POST['name'] ?? '') ?>" placeholder="Jane Smith" required>
            </div>

            <div class="field">
                <label>Email Address</label>
                <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" placeholder="admin@yourcompany.com" required>
            </div>

            <div class="divider"></div>

            <div class="field">
                <label>Password</label>
                <input type="password" name="password" placeholder="Minimum 8 characters" required>
            </div>

            <div class="field">
                <label>Confirm Password</label>
                <input type="password" name="password_confirm" placeholder="Repeat password" required>
            </div>

            <div style="display:flex;justify-content:flex-end">
                <button type="submit" class="btn btn-primary">Continue →</button>
            </div>
        </form>

        <?php endif; ?>

        <!-- ── Step 5: Company Settings ───────────────────────────────────── -->
        <?php if ($step === 5): ?>

        <h2>Company Settings</h2>
        <p class="subtitle">Basic settings for your billing system. All of these can be updated later in Settings → Company.</p>

        <?php errors($errors); ?>

        <form method="POST" action="?step=5">
            <input type="hidden" name="action" value="settings">

            <div class="field">
                <label>Company Name</label>
                <input type="text" name="company_name" value="<?= h($_POST['company_name'] ?? '') ?>" placeholder="Acme Hosting Ltd." required>
                <p class="hint">Shown on invoices, emails, and the client portal.</p>
            </div>

            <div class="field">
                <label>Support Email</label>
                <input type="email" name="company_email" value="<?= h($_POST['company_email'] ?? '') ?>" placeholder="support@yourcompany.com">
                <p class="hint">Used as the From address for system emails.</p>
            </div>

            <div class="field">
                <label>Application URL</label>
                <input type="text" name="app_url" value="<?= h($_POST['app_url'] ?? $_SESSION['app_url'] ?? detectUrl()) ?>">
                <p class="hint">The full URL where Commerce is installed, without trailing slash.</p>
            </div>

            <div style="display:flex;justify-content:flex-end">
                <button type="submit" class="btn btn-primary">Complete Installation →</button>
            </div>
        </form>

        <?php endif; ?>

        <!-- ── Step 6: Finish ─────────────────────────────────────────────── -->
        <?php if ($step === 6): ?>

        <div class="finish-box">
            <div class="finish-icon">🎉</div>
            <h2>Installation Complete</h2>
            <p>Opterius Commerce is ready to use.</p>

            <div class="cred-box">
                <p><strong>Admin URL:</strong> <code><?= h(($_SESSION['app_url'] ?? '') . '/admin') ?></code></p>
                <p><strong>Email:</strong> <code><?= h($_SESSION['admin']['email'] ?? '') ?></code></p>
                <p><strong>Password:</strong> <code>as set during installation</code></p>
            </div>

            <div class="warning-box">
                ⚠️ Clicking <strong>Go to Admin Panel</strong> will delete the <code>/install</code> directory. Make sure your application is working before proceeding.
            </div>
        </div>

        <?php endif; ?>

    </div><!-- /card-body -->

    <!-- Footer nav -->
    <div class="card-footer">
        <?php if ($step === 1): ?>
            <form method="POST" action="?step=1">
                <input type="hidden" name="action" value="preflight_next">
                <button type="submit" class="btn btn-primary" <?= ! empty($hardFails) ? 'disabled style="opacity:.5;cursor:not-allowed"' : '' ?>>
                    Continue →
                </button>
            </form>
        <?php endif; ?>

        <?php if ($step === 3 && ! empty($_SESSION['migrate_output'])): ?>
            <form method="POST" action="?step=3">
                <input type="hidden" name="action" value="migrate_next">
                <a href="?step=4" class="btn btn-primary">Continue →</a>
            </form>
        <?php endif; ?>

        <?php if ($step === 6): ?>
            <form method="POST" action="?step=6">
                <input type="hidden" name="action" value="finish">
                <button type="submit" class="btn btn-success">Go to Admin Panel →</button>
            </form>
        <?php endif; ?>
    </div>

</div>

<p style="margin-top:16px;font-size:11px;color:#94a3b8">Opterius Commerce Installer</p>

</body>
</html>

<?php

// ── Utility functions used in views ──────────────────────────────────────────

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function errors(array $errors): void
{
    foreach ($errors as $error) {
        echo '<div class="alert-error">' . $error . '</div>';
    }
}
