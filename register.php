<?php
require_once 'auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username']  ?? '');
    $email     = trim($_POST['email']     ?? '');
    $password  = $_POST['password']       ?? '';
    $password2 = $_POST['password2']      ?? '';

    if (!$username || !$email || !$password || !$password2) {
        $error = 'لطفاً همه فیلدها را پر کنید.';
    } elseif ($password !== $password2) {
        $error = 'رمز عبور و تکرار آن با هم مطابقت ندارند.';
    } else {
        $result = registerUser($username, $email, $password);
        if ($result['success']) {
            $success = 'حساب کاربری با موفقیت ایجاد شد. می‌توانید <a href="login.php">وارد شوید</a>.';
        } else {
            // Map English error messages to Persian
            $msg = $result['error'];
            if (str_contains($msg, 'Username must be'))    $error = 'نام کاربری باید حداقل ۳ کاراکتر باشد.';
            elseif (str_contains($msg, 'Invalid email'))   $error = 'آدرس ایمیل معتبر نیست.';
            elseif (str_contains($msg, 'Password must be'))$error = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
            elseif (str_contains($msg, 'already taken'))   $error = 'این نام کاربری یا ایمیل قبلاً ثبت شده است.';
            else                                           $error = 'خطا در ثبت‌نام. لطفاً دوباره تلاش کنید.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ثبت‌نام — دبیرستان دخترانه توانش</title>
<link rel="icon" href="/images/logo-T.png" type="image/png">
<style>
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
  --green:            #1a9e6e;
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

.topbar {
  width:100%; max-width:460px;
  background: var(--turquoise);
  border-radius:20px 20px 0 0;
  padding:18px 24px;
  display:flex; align-items:center; gap:14px;
  box-shadow: var(--shadow-md);
}
.topbar-logo {
  width:58px; height:58px;
  background:rgba(255,255,255,.2); border-radius:14px; overflow:hidden;
  flex-shrink:0; box-shadow:0 4px 14px rgba(0,0,0,.18); transition:transform .3s;
}
.topbar-logo:hover { transform:translateY(-3px); }
.topbar-logo img { width:100%; height:100%; object-fit:contain; }
.topbar-text { color:#fff; }
.topbar-text h1 { font-size:1.25rem; font-weight:800; line-height:1.3; }
.topbar-text p  { font-size:.82rem; opacity:.9; font-weight:300; margin-top:2px; }

.card {
  width:100%; max-width:460px;
  background:#fff; border-radius:0 0 20px 20px;
  padding:32px 28px 28px;
  box-shadow:var(--shadow-lg);
  animation: slideUp .4s ease both;
}
@keyframes slideUp {
  from { opacity:0; transform:translateY(18px); }
  to   { opacity:1; transform:translateY(0); }
}

.card-title { font-size:1.3rem; font-weight:800; margin-bottom:4px; }
.card-sub   { font-size:.85rem; color:var(--gray); font-weight:300; margin-bottom:26px; }

.alert { border-radius:12px; padding:11px 14px; font-size:.85rem; margin-bottom:20px; }
.alert.error   { background:rgba(201,64,64,.08); border:1px solid rgba(201,64,64,.3); color:var(--red); animation:shake .3s ease; }
.alert.success { background:rgba(26,158,110,.08); border:1px solid rgba(26,158,110,.3); color:var(--green); }
.alert.success a { color:var(--turquoise-dark); font-weight:700; }
@keyframes shake {
  0%,100%{ transform:translateX(0); }
  25%{ transform:translateX(-5px); }
  75%{ transform:translateX(5px); }
}

.field { margin-bottom:18px; }
label { display:block; font-size:.78rem; font-weight:700; color:var(--gray); margin-bottom:7px; }
input[type=text],
input[type=email],
input[type=password] {
  width:100%; border:1.5px solid #c0e5ea; border-radius:12px;
  padding:12px 15px; font-family:'Vazirmatn', sans-serif; font-size:.95rem;
  color:var(--text); background:var(--turquoise-lighter); outline:none; text-align:right;
  transition:border-color .2s, box-shadow .2s, background .2s;
}
input:focus { border-color:var(--turquoise); background:#fff; box-shadow:0 0 0 3px rgba(25,184,194,.15); }
input::placeholder { color:#a8d5da; }
.hint { font-size:.75rem; color:#8ab8be; margin-top:5px; }

.btn-primary {
  width:100%; margin-top:8px; padding:13px;
  background:linear-gradient(135deg, var(--turquoise), var(--turquoise-dark));
  border:none; border-radius:12px;
  font-family:'Vazirmatn', sans-serif; font-size:1rem; font-weight:700;
  color:#fff; cursor:pointer;
  box-shadow:0 6px 20px rgba(25,184,194,.4);
  transition:transform .15s, box-shadow .15s;
}
.btn-primary:hover  { transform:translateY(-2px); box-shadow:0 10px 28px rgba(25,184,194,.5); }
.btn-primary:active { transform:translateY(1px); }

.divider { height:1px; background:linear-gradient(to left, transparent, #c0e5ea, transparent); margin:22px 0; }
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
    <p>ثبت‌نام در پرتال</p>
  </div>
</div>

<div class="card">
  <div class="card-title">ایجاد حساب کاربری</div>
  <div class="card-sub">اطلاعات زیر را برای ثبت‌نام تکمیل کنید.</div>

  <?php if ($error): ?>
    <div class="alert error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert success"><?= $success ?></div>
  <?php endif; ?>

  <?php if (!$success): ?>
  <form method="POST" novalidate>
    <div class="field">
      <label for="username">نام کاربری</label>
      <input type="text" id="username" name="username"
        placeholder="یک نام کاربری انتخاب کنید"
        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
        autocomplete="username" required>
      <div class="hint">حداقل ۳ کاراکتر</div>
    </div>

    <div class="field">
      <label for="email">ایمیل</label>
      <input type="email" id="email" name="email"
        placeholder="آدرس ایمیل خود را وارد کنید"
        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
        autocomplete="email" required>
    </div>

    <div class="field">
      <label for="password">رمز عبور</label>
      <input type="password" id="password" name="password"
        placeholder="یک رمز عبور قوی انتخاب کنید"
        autocomplete="new-password" required>
      <div class="hint">حداقل ۶ کاراکتر</div>
    </div>

    <div class="field">
      <label for="password2">تکرار رمز عبور</label>
      <input type="password" id="password2" name="password2"
        placeholder="رمز عبور را دوباره وارد کنید"
        autocomplete="new-password" required>
    </div>

    <button type="submit" class="btn-primary">ثبت‌نام ←</button>
  </form>
  <?php endif; ?>

  <div class="divider"></div>
  <div class="card-footer">
    قبلاً ثبت‌نام کرده‌اید؟ <a href="login.php">وارد شوید</a>
  </div>
</div>

</body>
</html>
