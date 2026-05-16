<?php
require_once 'auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username'] ?? '');
    $password        = $_POST['password'] ?? '';

    if (!$usernameOrEmail || !$password) {
        $error = 'لطفاً همه فیلدها را پر کنید.';
    } else {
        $result = loginUser($usernameOrEmail, $password);
        if ($result['success']) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'کد ملی یا رمز عبور اشتباه است.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ورود کاربران — دبیرستان دخترانه توانش</title>
<link rel="icon" href="/images/logo-T.png" type="image/png">
<style>
/* ───── فونت وزیرمتن — آفلاین از سرور ───── */
@font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Light.woff2') format('woff2'); font-weight:300; font-display:swap; }
@font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Regular.woff2') format('woff2'); font-weight:400; font-display:swap; }
@font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Medium.woff2') format('woff2'); font-weight:500; font-display:swap; }
@font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Bold.woff2') format('woff2'); font-weight:700; font-display:swap; }
@font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-ExtraBold.woff2') format('woff2'); font-weight:800; font-display:swap; }

*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }

:root {
  --turquoise:        #19b8c2;
  --turquoise-dark:   #0c8790;
  --turquoise-light:  #e6f8fa;
  --turquoise-lighter:#f0fbfd;
  --text:             #0f3d42;
  --gray:             #4e8a90;
  --red:              #c94040;
  --shadow-md:        0 12px 30px rgba(0,0,0,.12);
  --shadow-lg:        0 20px 50px rgba(0,0,0,.18);
}

body {
  min-height: 100vh;
  background: linear-gradient(160deg, #f5fbfd 0%, var(--turquoise-light) 60%, #c8eff3 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  font-family: 'Vazirmatn', sans-serif;
  color: var(--text);
  padding: 24px 16px;
}

/* نوار برند */
.topbar {
  width: 100%;
  max-width: 460px;
  background: var(--turquoise);
  border-radius: 20px 20px 0 0;
  padding: 18px 24px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: var(--shadow-md);
}
.topbar-logo {
  width: 58px; height: 58px;
  background: rgba(255,255,255,.2);
  border-radius: 14px;
  overflow: hidden;
  flex-shrink: 0;
  box-shadow: 0 4px 14px rgba(0,0,0,.18);
  transition: transform .3s;
}
.topbar-logo:hover { transform: translateY(-3px); }
.topbar-logo img { width:100%; height:100%; object-fit:contain; }
.topbar-text { color: #fff; }
.topbar-text h1 { font-size:1.25rem; font-weight:800; line-height:1.3; }
.topbar-text p  { font-size:.82rem; opacity:.9; font-weight:300; margin-top:2px; }

/* کارت */
.card {
  width: 100%;
  max-width: 460px;
  background: #fff;
  border-radius: 0 0 20px 20px;
  padding: 32px 28px 28px;
  box-shadow: var(--shadow-lg);
  animation: slideUp .4s ease both;
}
@keyframes slideUp {
  from { opacity:0; transform:translateY(18px); }
  to   { opacity:1; transform:translateY(0); }
}

.card-title { font-size:1.3rem; font-weight:800; margin-bottom:4px; }
.card-sub   { font-size:.85rem; color:var(--gray); font-weight:300; margin-bottom:26px; }

/* پیام خطا */
.alert {
  background: rgba(201,64,64,.08);
  border: 1px solid rgba(201,64,64,.3);
  border-radius: 12px;
  padding: 11px 14px;
  font-size:.85rem;
  color: var(--red);
  margin-bottom: 20px;
  animation: shake .3s ease;
}
@keyframes shake {
  0%,100%{ transform:translateX(0); }
  25%{ transform:translateX(-5px); }
  75%{ transform:translateX(5px); }
}

/* فیلد */
.field { margin-bottom:18px; }
label {
  display:block;
  font-size:.78rem;
  font-weight:700;
  color:var(--gray);
  margin-bottom:7px;
}
input[type=text],
input[type=email],
input[type=password] {
  width:100%;
  border: 1.5px solid #c0e5ea;
  border-radius:12px;
  padding:12px 15px;
  font-family:'Vazirmatn', sans-serif;
  font-size:.95rem;
  color:var(--text);
  background:var(--turquoise-lighter);
  outline:none;
  text-align:right;
  transition:border-color .2s, box-shadow .2s, background .2s;
}
input:focus {
  border-color: var(--turquoise);
  background: #fff;
  box-shadow: 0 0 0 3px rgba(25,184,194,.15);
}
input::placeholder { color:#a8d5da; }

/* دکمه ورود */
.btn-primary {
  width:100%;
  margin-top:8px;
  padding:13px;
  background: linear-gradient(135deg, var(--turquoise), var(--turquoise-dark));
  border:none;
  border-radius:12px;
  font-family:'Vazirmatn', sans-serif;
  font-size:1rem;
  font-weight:700;
  color:#fff;
  cursor:pointer;
  box-shadow: 0 6px 20px rgba(25,184,194,.4);
  transition: transform .15s, box-shadow .15s;
}
.btn-primary:hover  { transform:translateY(-2px); box-shadow:0 10px 28px rgba(25,184,194,.5); }
.btn-primary:active { transform:translateY(1px); }

/* جداکننده */
.divider { height:1px; background:linear-gradient(to left, transparent, #c0e5ea, transparent); margin:22px 0; }

/* پیوند ثبت‌نام */
.card-footer { text-align:center; font-size:.85rem; color:var(--gray); }
.card-footer a { color:var(--turquoise-dark); text-decoration:none; font-weight:700; }
.card-footer a:hover { color:var(--turquoise); }
</style>
</head>
<body>

<div class="topbar">
  <div class="topbar-logo">
    <img src="/images/logo-Tw.png" alt="لوگو دبیرستان توانش">
  </div>
  <div class="topbar-text">
    <h1>دبیرستان دخترانه توانش</h1>
    <p>پرتال کاربران</p>
  </div>
</div>

<div class="card">
  <div class="card-title">ورود به حساب کاربری</div>
  <div class="card-sub">دانش‌آموزان و کارکنان گرامی، خوش آمدید.</div>

  <?php if ($error): ?>
    <div class="alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" novalidate>
    <div class="field">
      <label for="username">کد ملی</label>
      <input
        type="text" id="username" name="username"
        placeholder="کد ملی خود را وارد کنید"
        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
        autocomplete="username" required>
    </div>

    <div class="field">
      <label for="password">رمز عبور</label>
      <input
        type="password" id="password" name="password"
        placeholder="رمز عبور خود را وارد کنید"
        autocomplete="current-password" required>
    </div>

    <button type="submit" class="btn-primary">ورود به سامانه ←</button>
  </form>

  <div class="divider"></div>
  <div class="card-footer">
    <a href="index.html">← بازگشت به صفحه اصلی</a>
  </div>
</div>

</body>
</html>
