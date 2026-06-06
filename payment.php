<?php
declare(strict_types=1);

require_once __DIR__ . '/admin/admin-config.php';
header('Content-Type: application/json; charset=utf-8');

const ZARINPAL_REQUEST_URL = 'https://api.zarinpal.com/pg/v4/payment/request.json';
const ZARINPAL_START_URL = 'https://www.zarinpal.com/pg/StartPay/';

function respond_json(bool $error, string $message = '', array $extra = []): never
{
    $payload = array_merge(['error' => $error], $extra);
    if ($message !== '') {
        $payload['message'] = $message;
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function post_value(string $key, int $maxLength = 1000): string
{
    return clean_text($_POST[$key] ?? '', $maxLength);
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
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            return substr(trim(explode(',', (string) $_SERVER[$key])[0]), 0, 100);
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
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Content-Length: ' . strlen($jsonData)],
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

$settings = read_settings();
if (empty($settings['registration_enabled'])) {
    respond_json(true, 'ثبت‌نام دوره در حال حاضر غیرفعال است.');
}

$name = post_value('name', 160);
$rawPhone = post_value('phone', 60);
$email = post_value('email', 180);
$level = post_value('level', 250);
$goal = post_value('goal', 400);
$note = post_value('note', 1000);
$sessionId = post_value('session_id', 140);
$postedAmount = post_value('amount', 30);

if ($name === '') respond_json(true, 'نام و نام خانوادگی را وارد کنید.');
$phone = normalize_mobile($rawPhone);
if ($phone === '') respond_json(true, 'شماره موبایل ایران را به‌درستی وارد کنید؛ مثل 09123456789.');
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) respond_json(true, 'فرمت ایمیل واردشده صحیح نیست.');
$paymentAmount = (int) ($settings['payment_amount'] ?? DEFAULT_SETTINGS['payment_amount']);
if ($postedAmount !== '' && (int) $postedAmount !== $paymentAmount) respond_json(true, 'مبلغ پرداخت با مبلغ دوره هم‌خوانی ندارد.');

upsert_lead(['session_id' => $sessionId, 'name' => $name, 'phone' => $phone, 'email' => $email, 'level' => $level, 'goal' => $goal, 'source' => 'register-form', 'intent' => 'register', 'status' => 'new']);

$orders = read_orders();
$trackingCode = make_tracking_code($orders);
$description = 'خرید دوره آموزش طراحی سایت با هوش مصنوعی توسط ' . $name;
$gatewayPayload = [
    'merchant_id' => (string) ($settings['merchant_id'] ?? DEFAULT_SETTINGS['merchant_id']),
    'amount' => $paymentAmount,
    'callback_url' => (string) ($settings['callback_url'] ?? DEFAULT_SETTINGS['callback_url']),
    'description' => $description,
    'metadata' => ['mobile' => $phone, 'email' => $email],
];
$safeGatewayPayload = $gatewayPayload;
unset($safeGatewayPayload['merchant_id']);
$order = [
    'id' => count($orders) + 1,
    'tracking_code' => $trackingCode,
    'session_id' => $sessionId,
    'authority' => null,
    'name' => $name,
    'phone' => $phone,
    'email' => $email,
    'level' => $level,
    'goal' => $goal,
    'note' => $note,
    'course_title' => $settings['course_title'],
    'original_price' => $settings['original_price'],
    'discount_price' => $settings['discount_price'],
    'amount' => $paymentAmount,
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
if (!write_orders($orders)) respond_json(true, 'امکان ذخیره سفارش وجود ندارد. لطفاً کمی بعد دوباره تلاش کنید.');

$gatewayResponse = send_zarinpal_request($gatewayPayload);
$orders = read_orders();
$orderIndex = array_search($trackingCode, array_column($orders, 'tracking_code'), true);
if ($orderIndex !== false) $orders[$orderIndex]['zarinpal_response'] = $gatewayResponse;
if (!empty($gatewayResponse['curl_error'])) {
    if ($orderIndex !== false) { $orders[$orderIndex]['status'] = 'failed'; write_orders($orders); }
    respond_json(true, 'ارتباط با درگاه پرداخت برقرار نشد. لطفاً چند لحظه بعد دوباره تلاش کنید.');
}
$authority = $gatewayResponse['data']['authority'] ?? '';
$code = (int) ($gatewayResponse['data']['code'] ?? 0);
if ($authority !== '' && $code === 100) {
    if ($orderIndex !== false) { $orders[$orderIndex]['authority'] = $authority; write_orders($orders); }
    respond_json(false, '', ['url' => ZARINPAL_START_URL . $authority, 'tracking_code' => $trackingCode]);
}
if ($orderIndex !== false) { $orders[$orderIndex]['status'] = 'failed'; write_orders($orders); }
$message = $gatewayResponse['errors']['message'] ?? 'خطا در ارسال اطلاعات به درگاه پرداخت. لطفاً دوباره تلاش کنید.';
respond_json(true, 'زرین‌پال درخواست پرداخت را نپذیرفت: ' . $message);
