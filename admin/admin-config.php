<?php
declare(strict_types=1);

const ADMIN_USERNAME = 'admin';
const ADMIN_PASSWORD_HASH = '$2y$12$6O/ZFfHrKsblukdRLSNYHOeYEy3jF/ufxPqm4pdvT.Vo4kgozAFDO';
const ADMIN_SESSION_KEY = 'course_admin_authenticated';
const ADMIN_BASE_PATH = __DIR__;
const DATA_DIR = __DIR__ . '/../data';
const ORDERS_FILE = DATA_DIR . '/orders.json';
const LEADS_FILE = DATA_DIR . '/leads.json';
const CHATS_FILE = DATA_DIR . '/chats.json';
const SETTINGS_FILE = DATA_DIR . '/settings.json';

const DEFAULT_SETTINGS = [
    'course_title' => 'آموزش طراحی سایت با هوش مصنوعی',
    'original_price' => 9850000,
    'discount_price' => 5500000,
    'registration_enabled' => true,
    'discount_badge' => 'تخفیف ویژه ثبت‌نام',
    'support_phone' => '',
    'site_return_url' => 'https://alirezasajadi.ir/',
];

function admin_start_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name('ai_course_admin');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
        session_start();
    }
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function normalize_digits(string $value): string
{
    return strtr($value, [
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ]);
}

function clean_text(mixed $value, int $maxLength = 1000): string
{
    if (is_array($value)) {
        return '';
    }
    $clean = trim(strip_tags(normalize_digits((string) $value)));
    return mb_substr($clean, 0, $maxLength, 'UTF-8');
}

function normalize_mobile(string $phone): string
{
    $phone = normalize_digits($phone);
    $phone = preg_replace('/[\s\-()]+/', '', $phone) ?? '';
    if (preg_match('/^(?:\+98|0098|98)?(9\d{9})$/', $phone, $matches)) {
        return '0' . $matches[1];
    }
    if (preg_match('/^09\d{9}$/', $phone)) {
        return $phone;
    }
    return '';
}

function ensure_data_file(string $file, array $default = []): void
{
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
    if (!file_exists($file)) {
        write_json_file($file, $default);
    }
}

function read_json_file(string $file, array $default = []): array
{
    ensure_data_file($file, $default);
    $content = file_get_contents($file);
    if ($content === false || trim($content) === '') {
        return $default;
    }
    $decoded = json_decode($content, true);
    return is_array($decoded) ? $decoded : $default;
}

function write_json_file(string $file, array $data): bool
{
    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0755, true);
    }
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if ($json === false) {
        return false;
    }
    return file_put_contents($file, $json . PHP_EOL, LOCK_EX) !== false;
}

function read_orders(): array { return read_json_file(ORDERS_FILE, []); }
function write_orders(array $orders): bool { return write_json_file(ORDERS_FILE, array_values($orders)); }
function read_leads(): array { return read_json_file(LEADS_FILE, []); }
function write_leads(array $leads): bool { return write_json_file(LEADS_FILE, array_values($leads)); }
function read_chats(): array { return read_json_file(CHATS_FILE, []); }
function write_chats(array $chats): bool { return write_json_file(CHATS_FILE, array_values($chats)); }

function read_settings(): array
{
    return array_merge(DEFAULT_SETTINGS, read_json_file(SETTINGS_FILE, DEFAULT_SETTINGS));
}

function write_settings(array $settings): bool
{
    return write_json_file(SETTINGS_FILE, array_merge(DEFAULT_SETTINGS, $settings));
}

function admin_is_authenticated(): bool
{
    admin_start_session();
    return !empty($_SESSION[ADMIN_SESSION_KEY]);
}

function require_admin(): void
{
    if (!admin_is_authenticated()) {
        header('Location: login.php');
        exit;
    }
}

function verify_admin_credentials(string $username, string $password): bool
{
    return hash_equals(ADMIN_USERNAME, $username) && password_verify($password, ADMIN_PASSWORD_HASH);
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string
{
    admin_start_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf_token'];
}

function require_csrf(): void
{
    admin_start_session();
    $token = (string) ($_POST['csrf_token'] ?? '');
    if ($token === '' || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
        http_response_code(403);
        exit('درخواست نامعتبر است.');
    }
}

function format_toman(mixed $amount): string
{
    $value = (int) $amount;
    if ($value > 10000000) {
        $value = (int) round($value / 10);
    }
    return number_format($value) . ' تومان';
}

function format_datetime(mixed $date): string
{
    if (!$date) {
        return '-';
    }
    $timestamp = strtotime((string) $date);
    return $timestamp ? gmdate('Y-m-d H:i', $timestamp) : e($date);
}

function item_matches_query(array $item, string $query, array $fields): bool
{
    if ($query === '') {
        return true;
    }
    foreach ($fields as $field) {
        $value = mb_strtolower((string) ($item[$field] ?? ''), 'UTF-8');
        if (mb_strpos($value, $query, 0, 'UTF-8') !== false) {
            return true;
        }
    }
    return false;
}

function send_csv(string $filename, array $headers, array $rows): never
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, $headers);
    foreach ($rows as $row) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

function upsert_lead(array $data): array
{
    $leads = read_leads();
    $phone = normalize_mobile((string) ($data['phone'] ?? ''));
    $now = gmdate('c');
    $index = -1;
    if ($phone !== '') {
        foreach ($leads as $i => $lead) {
            if (($lead['phone'] ?? '') === $phone) {
                $index = $i;
                break;
            }
        }
    }

    $payload = [
        'name' => clean_text($data['name'] ?? '', 160),
        'phone' => $phone ?: clean_text($data['phone'] ?? '', 40),
        'email' => clean_text($data['email'] ?? '', 180),
        'level' => clean_text($data['level'] ?? '', 250),
        'goal' => clean_text($data['goal'] ?? '', 400),
        'source' => clean_text($data['source'] ?? 'register-form', 60),
        'intent' => clean_text($data['intent'] ?? '', 60),
        'follow_status' => clean_text($data['follow_status'] ?? 'new', 40),
        'admin_note' => clean_text($data['admin_note'] ?? '', 2000),
        'updated_at' => $now,
    ];

    if ($index >= 0) {
        $leads[$index] = array_merge($leads[$index], array_filter($payload, static fn ($value) => $value !== ''));
    } else {
        $payload['id'] = count($leads) + 1;
        $payload['created_at'] = $now;
        $leads[] = $payload;
        $index = array_key_last($leads);
    }
    write_leads($leads);
    return $leads[$index];
}

function mark_lead_converted(string $phone): void
{
    $phone = normalize_mobile($phone);
    if ($phone === '') {
        return;
    }
    $leads = read_leads();
    foreach ($leads as &$lead) {
        if (($lead['phone'] ?? '') === $phone) {
            $lead['follow_status'] = 'converted';
            $lead['updated_at'] = gmdate('c');
        }
    }
    unset($lead);
    write_leads($leads);
}
