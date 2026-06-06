<?php
header('Content-Type: application/json; charset=utf-8');

// داده‌ها از فرم
$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';

if (empty($name) || empty($phone)) {
    echo json_encode(["error" => true, "message" => "اطلاعات ناقص است"]);
    exit;
}

// مشخصات پرداخت
$merchant_id = "0babc2f6-e2e7-43db-a75c-35ba6fb361a9"; // مرچنت کد واقعی خودت
$amount = 1990000; // مبلغ به ریال
$callback_url = "https://alirezasajadi.ir/verify.php"; // آدرس بازگشت
$description = "خرید دوره آموزشی توسط {$name}";
$metadata = ["mobile" => $phone];

$data = [
    "merchant_id" => $merchant_id,
    "amount" => $amount,
    "callback_url" => $callback_url,
    "description" => $description,
    "metadata" => $metadata
];

$jsonData = json_encode($data);

$ch = curl_init('https://api.zarinpal.com/pg/v4/payment/request.json');
curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
]);

$result = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);
$result = json_decode($result, true);

if ($err) {
    echo json_encode(["error" => true, "message" => $err]);
} else {
    if (!empty($result['data']['authority']) && $result['data']['code'] == 100) {
        $url = "https://www.zarinpal.com/pg/StartPay/" . $result['data']['authority'];
        echo json_encode(["error" => false, "url" => $url]);
    } else {
        echo json_encode(["error" => true, "message" => $result['errors']['message'] ?? "خطا در ارسال به درگاه"]);
    }
}
?>
