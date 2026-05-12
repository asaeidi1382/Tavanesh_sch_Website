<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: https://tavanesh-sch.ir");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");

// مسیر فایل شمارنده
$counterFile = __DIR__ . '/counter.json';

// خواندن داده‌ها
if (!file_exists($counterFile)) {
    file_put_contents($counterFile, json_encode(['total' => 0, 'daily' => []], JSON_UNESCAPED_UNICODE));
}
$data = json_decode(file_get_contents($counterFile), true);

if (!$data) {
    http_response_code(500);
    echo json_encode(['error' => 'خطا در خواندن شمارنده']);
    exit;
}

$today = date('Y-m-d');

// افزایش کل بازدید
$data['total'] = isset($data['total']) ? $data['total'] + 1 : 1;

// افزایش بازدید امروز
if (!isset($data['daily'][$today])) {
    $data['daily'][$today] = 0;
}
$data['daily'][$today]++;

// ذخیره
file_put_contents($counterFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// پاسخ
echo json_encode([
    'total' => $data['total'],
    'today' => $data['daily'][$today]
], JSON_UNESCAPED_UNICODE);