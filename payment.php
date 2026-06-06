<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

const MERCHANT_ID = '0babc2f6-e2e7-43db-a75c-35ba6fb361a9';
const CALLBACK_URL = 'https://alirezasajadi.ir/verify.php';
const ZARINPAL_REQUEST_URL = 'https://api.zarinpal.com/pg/v4/payment/request.json';
const ZARINPAL_START_URL = 'https://www.zarinpal.com/pg/StartPay/';
const ORDERS_FILE = __DIR__ . '/orders.json';
const COURSE_TITLE = 'آموزش طراحی سایت با هوش مصنوعی';
const ORIGINAL_PRICE = 9850000;
const DISCOUNT_PRICE = 5500000;
const PAYMENT_AMOUNT = 55000000;

function respond_json(bool $error, string $message = '', array $extra = []): never
{
    $payload = array_merge(['error' => $error], $extra);
    if ($message !== '') {
        $payload['message'] = $message;
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
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

function clean_input(string $key): string
{
    $value = $_POST[$key] ?? '';
    if (is_array($value)) {
        return '';
    }

    return trim(strip_tags(normalize_digits((string) $value)));
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

function ensure_orders_file(): void
{
    if (!file_exists(ORDERS_FILE)) {
        file_put_contents(ORDERS_FILE, json_encode([], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    }
}

function read_orders(): array
{
    ensure_orders_file();
    $content = file_get_contents(ORDERS_FILE);
    if ($content === false || trim($content) === '') {
        return [];
    }

    $orders = json_decode($content, true);
    return is_array($orders) ? $orders : [];
}

function write_orders(array $orders): bool
{
    ensure_orders_file();
    $json = json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if ($json === false) {
        return false;
    }

    return file_put_contents(ORDERS_FILE, $json . PHP_EOL, LOCK_EX) !== false;
}

function make_tracking_code(array $orders): string
{
    $existing = array_column($orders, 'tracking_code');
    do {
        $code = 'AI-' . gmdate('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    } while (in_array($code, $existing, true));

    return $code;
}

function client_ip(): string
{
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', (string) $_SERVER[$key])[0]);
            return substr($ip, 0, 100);
        }
    }

    return '';
}

function send_zarinpal_request(array $payload): array
{
    $jsonData = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($jsonData === false) {
        return ['curl_error' => 'خطا در آماده‌سازی اطلاعات پرداخت'];
    }

    $ch = curl_init(ZARINPAL_REQUEST_URL);
    curl_setopt_array($ch, [
        CURLOPT_USERAGENT => 'ZarinPal Rest Api v4',
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData),
        ],
    ]);

    $result = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['curl_error' => $curlError];
    }

    $decoded = json_decode((string) $result, true);
    return is_array($decoded) ? $decoded : ['errors' => ['message' => 'پاسخ درگاه پرداخت قابل خواندن نیست']];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond_json(true, 'برای شروع پرداخت فقط ارسال فرم به روش POST مجاز است.');
}

$name = clean_input('name');
$rawPhone = clean_input('phone');
$email = clean_input('email');
$level = clean_input('level');
$goal = clean_input('goal');
$note = clean_input('note');
$postedAmount = clean_input('amount');

if ($name === '') {
    respond_json(true, 'نام و نام خانوادگی را وارد کنید.');
}

$phone = normalize_mobile($rawPhone);
if ($phone === '') {
    respond_json(true, 'شماره موبایل ایران را به‌درستی وارد کنید؛ مثل 09123456789.');
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond_json(true, 'فرمت ایمیل واردشده صحیح نیست.');
}

if ($postedAmount !== '' && (int) $postedAmount !== PAYMENT_AMOUNT) {
    respond_json(true, 'مبلغ پرداخت با مبلغ دوره هم‌خوانی ندارد.');
}

$orders = read_orders();
$trackingCode = make_tracking_code($orders);
$description = 'خرید دوره آموزش طراحی سایت با هوش مصنوعی توسط ' . $name;
$metadata = [
    'mobile' => $phone,
    'email' => $email,
];
$gatewayPayload = [
    'merchant_id' => MERCHANT_ID,
    'amount' => PAYMENT_AMOUNT,
    'callback_url' => CALLBACK_URL,
    'description' => $description,
    'metadata' => $metadata,
];
$safeGatewayPayload = $gatewayPayload;
unset($safeGatewayPayload['merchant_id']);

$order = [
    'id' => count($orders) + 1,
    'tracking_code' => $trackingCode,
    'authority' => null,
    'name' => $name,
    'phone' => $phone,
    'email' => $email,
    'level' => $level,
    'goal' => $goal,
    'note' => $note,
    'course_title' => COURSE_TITLE,
    'original_price' => ORIGINAL_PRICE,
    'discount_price' => DISCOUNT_PRICE,
    'amount' => PAYMENT_AMOUNT,
    'status' => 'pending',
    'created_at' => gmdate('c'),
    'paid_at' => null,
    'ref_id' => null,
    'zarinpal_request' => $safeGatewayPayload,
    'zarinpal_response' => null,
    'user_ip' => client_ip(),
    'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
];

$orders[] = $order;
if (!write_orders($orders)) {
    respond_json(true, 'امکان ذخیره سفارش وجود ندارد. لطفاً کمی بعد دوباره تلاش کنید.');
}

$gatewayResponse = send_zarinpal_request($gatewayPayload);
$orders = read_orders();
$orderIndex = array_search($trackingCode, array_column($orders, 'tracking_code'), true);

if ($orderIndex !== false) {
    $orders[$orderIndex]['zarinpal_response'] = $gatewayResponse;
}

if (!empty($gatewayResponse['curl_error'])) {
    if ($orderIndex !== false) {
        $orders[$orderIndex]['status'] = 'failed';
        write_orders($orders);
    }
    respond_json(true, 'ارتباط با درگاه پرداخت برقرار نشد. لطفاً چند لحظه بعد دوباره تلاش کنید.');
}

$authority = $gatewayResponse['data']['authority'] ?? '';
$code = (int) ($gatewayResponse['data']['code'] ?? 0);

if ($authority !== '' && $code === 100) {
    if ($orderIndex !== false) {
        $orders[$orderIndex]['authority'] = $authority;
        write_orders($orders);
    }

    respond_json(false, '', [
        'url' => ZARINPAL_START_URL . $authority,
        'tracking_code' => $trackingCode,
    ]);
}

if ($orderIndex !== false) {
    $orders[$orderIndex]['status'] = 'failed';
    write_orders($orders);
}

$message = $gatewayResponse['errors']['message'] ?? 'خطا در ارسال اطلاعات به درگاه پرداخت. لطفاً دوباره تلاش کنید.';
respond_json(true, 'زرین‌پال درخواست پرداخت را نپذیرفت: ' . $message);
