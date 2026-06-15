<?php
declare(strict_types=1);

/**
 * تنظیمات اولیه پنل ادمین.
 * برای نسخه‌های بعدی می‌توان این مقادیر را از دیتابیس یا فایل env خواند.
 */
const ADMIN_USERNAME = 'admin';
const ADMIN_PASSWORD_HASH = '$2y$12$6O/ZFfHrKsblukdRLSNYHOeYEy3jF/ufxPqm4pdvT.Vo4kgozAFDO'; // Alireza0911@
const ADMIN_SESSION_KEY = 'course_admin_authenticated';

const ADMIN_BASE_PATH = __DIR__;

/**
 * مسیر داده‌های سایت PHP
 * سفارش‌ها، تنظیمات پرداخت و اطلاعات مربوط به checkout بهتر است همینجا بماند.
 */
const DATA_DIR = __DIR__ . '/../data';

const ORDERS_FILE = DATA_DIR . '/orders.json';
const SETTINGS_FILE = DATA_DIR . '/settings.json';
const LANDING_CONTENT_FILE = DATA_DIR . '/landing-content.json';

/**
 * مسیر داده‌های سرویس Node.js مشاور هوشمند
 * چت‌ها و لیدهایی که از چت‌بات ثبت می‌شوند اینجا ذخیره می‌شوند.
 */
const NODE_DATA_DIR = '/home/reentawa/ai-consultant/data';

const CHATS_FILE = NODE_DATA_DIR . '/chats.json';
const LEADS_FILE = NODE_DATA_DIR . '/leads.json';

const DEFAULT_SETTINGS = [
    'course_title' => 'آموزش طراحی سایت با هوش مصنوعی',
    'instructor' => 'علی‌رضا سجادی',
    'original_price' => 9850000,
    'discount_price' => 5500000,
    'payment_amount' => 55000000,
    'registration_enabled' => true,
    'discount_badge' => 'تخفیف ویژه ثبت‌نام',
    'support_phone' => '',
    'site_return_url' => 'https://alirezasajadi.ir/',
    'merchant_id' => '0babc2f6-e2e7-43db-a75c-35ba6fb361a9',
    'callback_url' => 'https://alirezasajadi.ir/verify.php',
    'seo_title' => 'آموزش طراحی سایت با هوش مصنوعی | علی‌رضا سجادی',
    'meta_description' => 'یک مسیر عملی و مرحله‌به‌مرحله برای ساخت لندینگ، سایت شخصی و نمونه‌کار حرفه‌ای با کمک ابزارهای هوشمند.',
];

require_once __DIR__ . '/admin-functions.php';