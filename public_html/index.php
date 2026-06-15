<?php
declare(strict_types=1);
require_once __DIR__ . '/admin/admin-config.php';
$settings = read_settings();
$landingContent = read_landing_content();
$cmsContent = read_cms_content();
$hero = active_sorted($cmsContent['hero'] ?? [])[0] ?? [];
$seo = active_sorted($cmsContent['seo'] ?? [])[0] ?? [];
function front_e(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function front_json(array $value): string { return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '{}'; }
function fa_price(mixed $amount): string { return strtr(number_format((int) $amount), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹',','=>'٬']) . ' تومان'; }
?>
<!doctype html>
<html lang="fa" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#06111f" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap"
      rel="stylesheet"
    />
    <link rel="icon" type="image/png" href="./favicon.png" />
    <title><?= front_e(($seo['title'] ?? '') ?: (($settings['seo_title'] ?? '') ?: (($settings['course_title'] ?? '') . ' | ' . ($settings['instructor'] ?? '')))) ?></title>
    <meta
      name="description"
      content="<?= front_e(($seo['meta_description'] ?? '') ?: (($settings['meta_description'] ?? '') ?: 'یک مسیر عملی و مرحله‌به‌مرحله برای ساخت لندینگ، سایت شخصی و نمونه‌کار حرفه‌ای با کمک ابزارهای هوشمند.')) ?>"
    />
    <meta
      name="keywords"
      content="<?= front_e($settings['course_title'] ?? 'آموزش طراحی سایت با هوش مصنوعی') ?>، طراحی سایت با AI، وایب کدینگ، آموزش طراحی سایت، ساخت سایت با هوش مصنوعی، طراحی سایت بدون سردرگمی، آموزش پروژه محور طراحی سایت"
    />
    <meta name="author" content="<?= front_e($settings['instructor'] ?? 'علی‌رضا سجادی') ?>" />
    <meta property="og:locale" content="fa_IR" />
    <meta
      property="og:title"
      content="<?= front_e(($seo['og_title'] ?? '') ?: (($settings['seo_title'] ?? '') ?: (($settings['course_title'] ?? '') . ' | ' . ($settings['instructor'] ?? '')))) ?>"
    />
    <meta
      property="og:description"
      content="<?= front_e($seo['og_description'] ?? '') ?>"
    />
    <meta property="og:type" content="website" />
    <meta property="og:image" content="<?= front_e($seo['og_image'] ?? './assets/instructor-hero-BtbWE4mA.jpg') ?>" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= front_e($seo['twitter_title'] ?? ($settings['course_title'] ?? 'آموزش طراحی سایت با هوش مصنوعی')) ?>" />
    <meta
      name="twitter:description"
      content="<?= front_e($seo['twitter_description'] ?? '') ?>"
    />
    <meta
      name="twitter:image"
      content="<?= front_e($seo['twitter_image'] ?? './assets/instructor-hero-BtbWE4mA.jpg') ?>"
    />
    <link rel="stylesheet" href="./assets/landing.css" />
    <script>window.LANDING_DATA = <?= front_json($landingContent) ?>;</script>
    <script defer src="./assets/landing.js"></script>
  </head>
  <body>
    <div class="scroll-progress" aria-hidden="true"></div>
    <div class="site-bg" aria-hidden="true">
      <span></span><span></span><span></span>
    </div>

    <header class="site-header" data-header>
      <nav class="nav container" aria-label="منوی اصلی">
        <a
          class="brand"
          href="#home"
          aria-label="<?= front_e($settings['instructor'] ?? 'علی‌رضا سجادی') ?>؛ <?= front_e($settings['course_title'] ?? 'آموزش طراحی سایت با هوش مصنوعی') ?>"
        >
          <span class="brand-mark">AI</span>
          <span class="brand-text">
            <span class="brand-title"><?= front_e($settings['instructor'] ?? 'علی‌رضا سجادی') ?></span>
            <strong><?= front_e($settings['course_title'] ?? 'آموزش طراحی سایت با هوش مصنوعی') ?></strong>
          </span>
        </a>
        <button
          class="menu-toggle"
          type="button"
          aria-label="باز کردن منو"
          aria-expanded="false"
          data-menu-toggle
        >
          <span></span><span></span><span></span>
        </button>
        <div class="nav-links" data-nav-links>
          <a href="#home">خانه</a><a href="#path">مسیر یادگیری</a
          ><a href="#projects">پروژه‌ها</a><a href="#curriculum">سرفصل‌ها</a
          ><a href="#instructor">درباره مدرس</a><a href="#course-register">خرید دوره</a><a href="#faq">سوالات متداول</a>
        </div>
        <a
          class="btn btn-small btn-primary nav-cta"
          href="#start"
          aria-label="شروع یادگیری"
          data-open-advisor
          data-advisor-intent="start"
          >شروع یادگیری</a
        >
      </nav>
    </header>

    <main>
      <section class="hero section" id="home">
        <div class="container hero-grid">
          <div class="hero-copy reveal">
            <span class="eyebrow"><?= front_e($hero['eyebrow'] ?? 'مسیر عملی طراحی سایت با AI') ?></span>
            <h1><?= front_e($hero['title'] ?? 'طراحی سایت را سریع‌تر، دقیق‌تر و حرفه‌ای‌تر یاد بگیر') ?></h1>
            <p class="lead"><?= front_e($hero['description'] ?? '') ?></p>
            <div class="hero-actions">
              <a class="btn btn-primary" href="#start" aria-label="شروع آموزش" data-open-advisor data-advisor-intent="start"
                ><?= front_e($hero['button_1_text'] ?? 'شروع آموزش') ?></a
              >
              <a
                class="btn btn-ghost"
                href="<?= front_e($hero['button_2_link'] ?? '#path') ?>"
                aria-label="دیدن مسیر یادگیری"
                ><?= front_e($hero['button_2_text'] ?? 'دیدن مسیر یادگیری') ?></a
              >
              <a
                class="btn btn-ghost"
                href="#start"
                aria-label="مشاوره رایگان"
                data-open-advisor
                data-advisor-intent="consultation"
                ><?= front_e($hero['button_3_text'] ?? 'مشاوره رایگان') ?></a
              >
            </div>
            <p class="trust-line"><?= front_e($hero['trust_text'] ?? '') ?></p>
            <div class="hero-stats" aria-label="ویژگی‌های دوره">
              <?php foreach (lines_to_array($hero['cards'] ?? '') as $card): ?><span><?= front_e($card) ?></span><?php endforeach; ?>
            </div>
          </div>
          <div class="hero-visual reveal" data-tilt>
            <div class="orb"></div>
            <img
              src="<?= front_e($hero['image'] ?? './assets/instructor-hero-BtbWE4mA.jpg') ?>"
              alt="<?= front_e($settings['instructor'] ?? 'علی‌رضا سجادی') ?> مدرس <?= front_e($settings['course_title'] ?? 'آموزش طراحی سایت با هوش مصنوعی') ?>"
              width="640"
              height="760"
              fetchpriority="high"
            />
            <?php foreach (lines_to_array($hero['float_cards'] ?? '') as $i => $card): ?><div class="float-card <?= ['code-card','build-card','smart-card'][$i % 3] ?>"><?= front_e($card) ?></div><?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="section" id="problem">
        <div class="container">
          <div class="section-head reveal">
            <span class="eyebrow">چالش واقعی</span>
            <h2>چرا یادگیری طراحی سایت برای خیلی‌ها به خروجی نمی‌رسد؟</h2>
          </div>
          <div class="cards three stagger">
            <?php foreach (active_sorted($cmsContent['challenges'] ?? []) as $challenge): ?>
            <article class="glass-card">
              <b><?= front_e($challenge['number'] ?? '') ?></b>
              <h3><?= front_e($challenge['title'] ?? '') ?></h3>
              <p><?= front_e($challenge['description'] ?? '') ?></p>
            </article>
            <?php endforeach; ?>
          </div>
          <p class="impact reveal">
            اینجا فقط آموزش نمی‌بینی؛ یک مسیر روشن برای ساخت و بهبود خروجی داری.
          </p>
        </div>
      </section>

      <section class="section" id="path">
        <div class="container">
          <div class="split">
            <div class="section-head reveal">
              <span class="eyebrow">راه‌حل دوره</span>
              <h2>از ایده تا صفحه حرفه‌ای؛ با مسیر کوتاه و قابل اجرا</h2>
              <p>
                ایده‌ات را شفاف می‌کنی، ساختار صفحه را می‌چینی، خروجی اولیه
                می‌گیری و با اصلاح‌های دقیق، آن را به یک نمونه قابل ارائه تبدیل
                می‌کنی.
              </p>
            </div>
            <div class="timeline stagger">
              <?php foreach (active_sorted($cmsContent['learning_path'] ?? []) as $step): ?>
              <div><span><?= front_e($step['number'] ?? '') ?></span><h3><?= front_e($step['title'] ?? '') ?></h3><p><?= front_e($step['description'] ?? '') ?></p></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </section>

      <section class="section" id="learn">
        <div class="container">
          <div class="section-head center reveal">
            <span class="eyebrow">مهارت‌های عملی</span>
            <h2>چه چیزهایی یاد می‌گیری؟</h2>
          </div>
          <div class="cards feature-grid stagger" data-feature-grid></div>
        </div>
      </section>

      <section class="section" id="projects">
        <div class="container">
          <div class="section-head reveal">
            <span class="eyebrow">نمونه خروجی‌ها</span>
            <h2>در پایان مسیر، چند مدل صفحه قابل ارائه می‌سازی</h2>
          </div>
          <div class="project-grid stagger" data-project-grid></div>
        </div>
      </section>

      <section class="section compare-section">
        <div class="container">
          <div class="section-head center reveal">
            <span class="eyebrow">تصمیم هوشمندانه</span>
            <h2>یادگیری پراکنده یا مسیر هدایت‌شده؟</h2>
          </div>
          <div class="compare-grid reveal">
            <article class="compare-card old">
              <h3>یادگیری پراکنده</h3>
              <ul>
                <li>مسیر طولانی و نامشخص</li>
                <li>تمرکز روی حفظ کردن</li>
                <li>توقف در خطاهای ساده</li>
                <li>خروجی دیرهنگام</li>
                <li>سردرگمی در شروع</li>
              </ul>
            </article>
            <article class="compare-card new">
              <h3>یادگیری هدایت‌شده با AI</h3>
              <ul>
                <li>شروع سریع‌تر و هدفمند</li>
                <li>تمرکز روی ساخت خروجی</li>
                <li>اصلاح مرحله‌به‌مرحله</li>
                <li>یادگیری همراه با تمرین</li>
                <li>مناسب ساخت نمونه‌کار</li>
              </ul>
            </article>
          </div>
        </div>
      </section>

      <section class="section" id="audience">
        <div class="container">
          <div class="section-head reveal">
            <span class="eyebrow">برای چه کسانی؟</span>
            <h2>
              اگر می‌خواهی سریع‌تر به خروجی قابل نمایش برسی، این مسیر برای توست
            </h2>
          </div>
          <div class="cards audience-grid stagger" data-audience-grid></div>
        </div>
      </section>

      <section class="section instructor" id="instructor">
        <div class="container instructor-grid">
          <div class="instructor-photo reveal">
            <img
              src="<?= front_e((active_sorted($cmsContent['instructor'] ?? [])[0]['image'] ?? './assets/instructor-hero-BtbWE4mA.jpg')) ?>"
              alt="تصویر <?= front_e($settings['instructor'] ?? 'علی‌رضا سجادی') ?> مدرس دوره"
              width="520"
              height="620"
              loading="lazy"
            />
          </div>
          <div class="reveal">
            <span class="eyebrow">مدرس دوره</span>
            <?php $instructorBlock = active_sorted($cmsContent['instructor'] ?? [])[0] ?? []; ?>
            <h2><?= front_e(($instructorBlock['name'] ?? ($settings['instructor'] ?? 'علی‌رضا سجادی')) . '؛ ' . ($instructorBlock['job_title'] ?? 'مدرس طراحی سایت با ابزارهای هوشمند')) ?></h2>
            <p><?= front_e(($instructorBlock['short_bio'] ?? '') . ' ' . ($instructorBlock['description'] ?? '')) ?></p>
            <div class="trust-cards">
              <?php foreach (lines_to_array($instructorBlock['trust_features'] ?? '') as $trust): ?><span><?= front_e($trust) ?></span><?php endforeach; ?>
            </div>
          </div>
        </div>
      </section>

      <section class="section cta-band" id="start">
        <div class="container reveal">
          <div>
            <span class="eyebrow">شروع مسیر</span>
            <h2>آماده‌ای اولین خروجی جدی‌ات را بسازی؟</h2>
            <p>
              از همین‌جا طراحی سایت را با برنامه، تمرین و بازخورد مرحله‌ای شروع
              کن.
            </p>
          </div>
          <div class="hero-actions">
            <a class="btn btn-light" href="#curriculum" aria-label="شروع یادگیری" data-open-advisor data-advisor-intent="start"
              >شروع یادگیری</a
            >
            <a class="btn btn-ghost" href="#course-register" aria-label="ثبت‌نام در دوره" data-open-register data-register-course
              >ثبت‌نام در دوره</a
            >
          </div>
        </div>
      </section>


      <section class="section course-purchase" id="course-register">
        <div class="container course-purchase-grid">
          <div class="course-purchase-copy reveal">
            <span class="eyebrow">ثبت‌نام دوره</span>
            <h2>ثبت‌نام در دوره <?= front_e($settings['course_title'] ?? 'آموزش طراحی سایت با هوش مصنوعی') ?></h2>
            <p class="lead">
              یک مسیر عملی برای ساخت سایت و وب‌اپ قابل ارائه با کمک هوش مصنوعی؛
              مناسب افرادی که می‌خواهند سریع‌تر از ایده به پروژه واقعی برسند.
            </p>
            <p>
              در این دوره یاد می‌گیری چطور با کمک هوش مصنوعی و ابزار مخصوص دوره،
              ایده‌ات را به یک سایت واقعی تبدیل کنی؛ از طراحی ظاهر صفحات تا ساخت
              منطق سایت، فرم‌ها، دیتابیس، پنل مدیریت و فرآیندهای سمت سرور.
            </p>
            <div class="course-feature-grid" aria-label="امکانات دوره">
              <?php foreach (active_sorted($cmsContent['course_features'] ?? []) as $feature): ?><span><?= front_e($feature['title'] ?? '') ?></span><?php endforeach; ?>
            </div>
          </div>

          <aside class="course-price-card reveal" aria-label="کارت قیمت دوره">
            <div class="discount-badge"><?= front_e($settings['discount_badge'] ?? 'تخفیف ویژه ثبت‌نام') ?></div>
            <p class="course-type">آنلاین، پروژه‌محور و مرحله‌به‌مرحله</p>
            <h3><?= front_e($settings['course_title'] ?? 'آموزش طراحی سایت با هوش مصنوعی') ?></h3>
            <p class="teacher">مدرس: <?= front_e($settings['instructor'] ?? 'علی‌رضا سجادی') ?></p>
            <div class="price-box">
              <span class="old-price"><?= fa_price($settings['original_price'] ?? 9850000) ?></span>
              <strong><?= fa_price($settings['discount_price'] ?? 5500000) ?></strong>
              <small>مبلغ پرداختی نهایی دوره</small>
            </div>
            <ul class="course-summary-list">
              <li>دسترسی آنلاین به مسیر عملی دوره</li>
              <li>تمرکز روی ساخت خروجی قابل ارائه</li>
              <li>مناسب شروع جدی، نمونه‌کار و پروژه واقعی</li>
            </ul>
            <div class="purchase-actions">
              <button class="btn btn-primary btn-register" type="button" data-open-register data-register-course>
                الان ثبت‌نام کنید
              </button>
              <button class="btn btn-ghost btn-consult" type="button" data-open-advisor data-advisor-intent="consultation">
                مشاوره رایگان قبل از خرید
              </button>
            </div>
          </aside>
        </div>
      </section>

      <section class="section" id="curriculum">
        <div class="container">
          <div class="section-head reveal">
            <span class="eyebrow">نقشه راه</span>
            <h2>سرفصل‌های دوره</h2>
          </div>
          <div class="accordion reveal" data-accordion></div>
        </div>
      </section>

      <section class="section" id="results">
        <div class="container">
          <div class="section-head center reveal">
            <span class="eyebrow">نتیجه نهایی</span>
            <h2>بعد از دوره چه توانایی‌هایی داری؟</h2>
          </div>
          <div class="cards results-grid stagger" data-results-grid></div>
        </div>
      </section>

      <section class="section testimonials">
        <div class="container">
          <div class="section-head reveal">
            <span class="eyebrow">اعتمادسازی</span>
            <h2>نظر هنرجوها</h2>
          </div>
          <div class="cards three stagger" data-testimonials></div>
        </div>
      </section>

      <section class="section" id="faq">
        <div class="container">
          <div class="section-head reveal">
            <span class="eyebrow">سوالات متداول</span>
            <h2>قبل از شروع شاید این سوال‌ها را داشته باشی</h2>
          </div>
          <div class="accordion reveal" data-faq></div>
        </div>
      </section>

      <section class="section final-cta">
        <div class="container reveal">
          <h2>این بار فقط آموزش نبین؛ خروجی بساز.</h2>
          <p>
            با یک مسیر عملی، از ایده به صفحه قابل نمایش و نمونه‌کار اولیه برس.
          </p>
          <div class="hero-actions">
            <a
              class="btn btn-primary"
              href="#start"
              aria-label="شروع یادگیری طراحی سایت با AI"
              data-open-advisor
              data-advisor-intent="start"
              >شروع یادگیری طراحی سایت</a
            ><a
              class="btn btn-ghost"
              href="#curriculum"
              aria-label="مشاهده سرفصل‌ها"
              >مشاهده سرفصل‌ها</a
            ><a
              class="btn btn-ghost"
              href="#start"
              aria-label="دریافت مشاوره"
              data-open-advisor
              data-advisor-intent="consultation"
              >دریافت مشاوره</a
            >
          </div>
        </div>
      </section>
    </main>


    <div class="register-modal" data-register-modal aria-hidden="true">
      <div class="register-backdrop" data-close-register></div>
      <div
        class="register-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="register-title"
        aria-describedby="register-description"
      >
        <button class="register-close" type="button" aria-label="بستن فرم ثبت‌نام" data-close-register>×</button>
        <div class="register-layout">
          <form class="register-form" data-register-form novalidate>
            <span class="eyebrow">خرید دوره</span>
            <h2 id="register-title">ثبت‌نام و خرید دوره</h2>
            <p id="register-description">
              اطلاعاتت رو وارد کن تا ثبت‌نام اولیه انجام بشه و وارد مرحله پرداخت بشی.
            </p>
            <input type="hidden" name="course" value="<?= front_e($settings['course_title'] ?? 'آموزش طراحی سایت با هوش مصنوعی') ?>" />
            <input type="hidden" name="amount" value="<?= front_e($settings['payment_amount'] ?? 55000000) ?>" />

            <label class="form-field">
              <span>نام و نام خانوادگی <b>*</b></span>
              <input type="text" name="name" autocomplete="name" required placeholder="مثلاً علی محمدی" />
              <small data-error-for="name"></small>
            </label>

            <label class="form-field">
              <span>شماره موبایل <b>*</b></span>
              <input type="tel" name="phone" inputmode="tel" autocomplete="tel" required placeholder="مثلاً 09123456789" />
              <small data-error-for="phone"></small>
            </label>

            <label class="form-field">
              <span>ایمیل</span>
              <input type="email" name="email" autocomplete="email" placeholder="اختیاری" />
              <small data-error-for="email"></small>
            </label>

            <label class="form-field">
              <span>سطح آشنایی با طراحی سایت</span>
              <select name="level">
                <option value="">انتخاب کنید</option>
                <option>کاملاً مبتدی هستم</option>
                <option>کمی HTML/CSS بلدم</option>
                <option>قبلاً سایت ساختم</option>
                <option>صاحب کسب‌وکارم</option>
                <option>قصد دارم فریلنسری را شروع کنم</option>
              </select>
            </label>

            <label class="form-field">
              <span>هدف از شرکت در دوره</span>
              <select name="goal">
                <option value="">انتخاب کنید</option>
                <option>یادگیری طراحی سایت از صفر</option>
                <option>ساخت سایت برای کسب‌وکار خودم</option>
                <option>ساخت نمونه‌کار</option>
                <option>شروع فریلنسری</option>
                <option>ساخت سایت با هوش مصنوعی</option>
                <option>ساخت وب‌اپ و نرم‌افزار تحت وب</option>
              </select>
            </label>

            <label class="form-field full">
              <span>توضیحات بیشتر</span>
              <textarea name="note" rows="4" placeholder="اگر سوال یا توضیحی داری اینجا بنویس..."></textarea>
            </label>

            <div class="form-alert" data-register-message aria-live="polite"></div>
            <div class="register-buttons">
              <button class="btn btn-primary" type="submit" data-submit-register>
                ادامه و پرداخت <?= fa_price($settings['discount_price'] ?? 5500000) ?>
              </button>
              <button class="btn btn-ghost" type="button" data-back-to-advisor>
                بازگشت به مشاوره
              </button>
            </div>
          </form>

          <aside class="register-summary" aria-label="خلاصه دوره و قیمت">
            <span class="discount-badge">ظرفیت محدود</span>
            <h3><?= front_e($settings['course_title'] ?? 'آموزش طراحی سایت با هوش مصنوعی') ?></h3>
            <p>مدرس: <?= front_e($settings['instructor'] ?? 'علی‌رضا سجادی') ?></p>
            <p>آنلاین، پروژه‌محور و مرحله‌به‌مرحله</p>
            <div class="price-box compact">
              <span class="old-price"><?= fa_price($settings['original_price'] ?? 9850000) ?></span>
              <strong><?= fa_price($settings['discount_price'] ?? 5500000) ?></strong>
              <small>مبلغ درگاه: 55,000,000 ریال</small>
            </div>
            <ul class="course-summary-list">
              <li>ساخت صفحات، فرم‌ها و منطق سایت</li>
              <li>آشنایی با بک‌اند، دیتابیس و سمت سرور</li>
              <li>خروجی مناسب نمونه‌کار اولیه و پروژه واقعی</li>
            </ul>
          </aside>
        </div>
      </div>
    </div>

    <footer class="footer">
      <div class="container footer-grid">
        <div>
          <a class="brand" href="#home"
            ><span class="brand-mark">AI</span
            ><span class="brand-text"
              ><span class="brand-title"><?= front_e($settings['instructor'] ?? 'علی‌رضا سجادی') ?></span
              ><strong><?= front_e($settings['course_title'] ?? 'آموزش طراحی سایت با هوش مصنوعی') ?></strong></span
            ></a
          >
          <p>
            مسیر عملی طراحی سایت؛ از ایده و متن تا چیدمان، اصلاح و آماده‌سازی
            خروجی قابل ارائه.
          </p>
        </div>
        <div>
          <h3>لینک‌های سریع</h3>
          <a href="#path">مسیر یادگیری</a><a href="#projects">پروژه‌ها</a
          ><a href="#curriculum">سرفصل‌ها</a><a href="#faq">FAQ</a>
        </div>
        <div>
          <h3>شبکه‌های اجتماعی</h3>
          <a href="#" aria-label="اینستاگرام <?= front_e($settings['instructor'] ?? 'علی‌رضا سجادی') ?>">Instagram</a
          ><a href="#" aria-label="کانال آموزشی <?= front_e($settings['instructor'] ?? 'علی‌رضا سجادی') ?>">کانال آموزشی</a
          ><a href="#" aria-label="لینکدین <?= front_e($settings['instructor'] ?? 'علی‌رضا سجادی') ?>">LinkedIn</a>
        </div>
      </div>
      <div class="copyright">© 2026 <?= front_e($settings['instructor'] ?? 'علی‌رضا سجادی') ?>. همه حقوق محفوظ است. <a href="admin/login.php" class="admin-login-link">ورود ادمین</a></div>
    </footer>
  </body>
</html>
