<?php
declare(strict_types=1);

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

function clean_url(mixed $value): string
{
    $url = clean_text($value, 500);
    return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
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

function data_defaults_for(string $file): array
{
    if ($file === SETTINGS_FILE) {
        return DEFAULT_SETTINGS;
    }
    return [];
}

function ensure_data_file(string $file, ?array $default = null): void
{
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
    $htaccess = DATA_DIR . '/.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "Require all denied\n<FilesMatch \"\\.(json)$\">\n  Require all denied\n</FilesMatch>\n", LOCK_EX);
    }
    if (!file_exists($file)) {
        write_json_file($file, $default ?? data_defaults_for($file));
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
        redirect('login.php');
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
        exit('درخواست نامعتبر است. لطفاً صفحه را دوباره باز کنید.');
    }
}

function status_label(string $status): string
{
    return [
        'paid' => 'پرداخت موفق', 'pending' => 'در انتظار پرداخت', 'failed' => 'ناموفق', 'cancelled' => 'لغوشده',
        'new' => 'جدید', 'contacted' => 'تماس گرفته شد', 'interested' => 'علاقه‌مند', 'not_interested' => 'عدم علاقه', 'converted' => 'تبدیل‌شده',
        'open' => 'باز', 'closed' => 'بسته',
    ][$status] ?? $status;
}

function format_toman(mixed $amount): string
{
    $value = (int) $amount;
    if ($value > 10000000) {
        $value = (int) round($value / 10);
    }
    return number_format($value) . ' تومان';
}

function format_rial(mixed $amount): string
{
    return number_format((int) $amount) . ' ریال';
}

function format_datetime(mixed $date): string
{
    if (!$date) {
        return '-';
    }
    $timestamp = strtotime((string) $date);
    return $timestamp ? gmdate('Y-m-d H:i', $timestamp) : (string) $date;
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
        fputcsv($out, array_map(fn($value) => (string) $value, $row));
    }
    fclose($out);
    exit;
}

function next_id(array $items): int
{
    $ids = array_map(fn($item) => (int) ($item['id'] ?? 0), $items);
    return (empty($ids) ? 0 : max($ids)) + 1;
}

function upsert_lead(array $data): array
{
    $leads = read_leads();
    $phone = normalize_mobile((string) ($data['phone'] ?? ''));
    $email = clean_text($data['email'] ?? '', 180);
    $now = gmdate('c');
    $index = -1;
    foreach ($leads as $i => $lead) {
        if (($phone !== '' && ($lead['phone'] ?? '') === $phone) || ($phone === '' && $email !== '' && ($lead['email'] ?? '') === $email)) {
            $index = $i;
            break;
        }
    }
    $payload = [
        'name' => clean_text($data['name'] ?? '', 160),
        'phone' => $phone,
        'email' => $email,
        'level' => clean_text($data['level'] ?? '', 250),
        'goal' => clean_text($data['goal'] ?? '', 500),
        'source' => clean_text($data['source'] ?? 'register-form', 80),
        'intent' => clean_text($data['intent'] ?? 'general', 80),
        'follow_status' => clean_text($data['follow_status'] ?? 'new', 50),
    ];
    if ($index < 0) {
        $lead = array_merge([
            'id' => next_id($leads),
            'created_at' => $now,
            'updated_at' => $now,
            'admin_note' => '',
        ], $payload);
        $leads[] = $lead;
    } else {
        $lead = $leads[$index];
        foreach ($payload as $key => $value) {
            if ($value !== '' || !isset($lead[$key])) {
                $lead[$key] = $value;
            }
        }
        $lead['updated_at'] = $now;
        $leads[$index] = $lead;
    }
    write_leads($leads);
    return $lead;
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

function latest_items(array $items, int $limit, string $dateField = 'created_at'): array
{
    usort($items, fn($a, $b) => strcmp((string) ($b[$dateField] ?? ''), (string) ($a[$dateField] ?? '')));
    return array_slice($items, 0, $limit);
}

function extract_last_user_message(array $chat): string
{
    $messages = $chat['messages'] ?? [];
    if (!is_array($messages)) {
        return '';
    }
    foreach (array_reverse($messages) as $message) {
        if (($message['role'] ?? '') === 'user') {
            return (string) ($message['content'] ?? '');
        }
    }
    return '';
}

function chat_message_count(array $chat): int
{
    return is_array($chat['messages'] ?? null) ? count($chat['messages']) : 0;
}
