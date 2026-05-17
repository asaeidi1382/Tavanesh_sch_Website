<?php

require_once 'auth_new.php';

if (isAdmin()) {

    header('Location: admin_dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');

    $password = trim($_POST['password'] ?? '');

    if (adminLogin($username, $password)) {

        header('Location: admin_dashboard.php');
        exit;
    }

    $error = 'نام کاربری یا رمز عبور اشتباه است.';
}
?>

<!DOCTYPE html>

<html lang="fa" dir="rtl">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>ورود مدیریت — دبیرستان توانش</title>

<link rel="icon"
      href="/images/logo-T.png"
      type="image/png">

<style>









*{
  margin:0;
  padding:0;
  box-sizing:border-box;
}

:root {

  --turquoise:#19b8c2;
  --turquoise-dark:#0c8790;
  --turquoise-light:#e6f8fa;
  --turquoise-lighter:#f3fcfd;

  --text:#113d42;
  --gray:#5b8c91;

  --shadow-sm:0 4px 15px rgba(0,0,0,.08);
  --shadow-md:0 15px 40px rgba(0,0,0,.12);

  --transition:all .35s cubic-bezier(.4,0,.2,1);
}

body {

  min-height:100vh;

  font-family:'Vazirmatn',sans-serif;

  background:
    radial-gradient(circle at top right,
      rgba(25,184,194,.16),
      transparent 35%),

    radial-gradient(circle at bottom left,
      rgba(25,184,194,.14),
      transparent 30%),

    linear-gradient(to bottom,#f5fbfd,#fff);

  display:flex;

  justify-content:center;

  align-items:center;

  padding:30px;
}

.login-card {

  width:100%;
  max-width:430px;

  background:#fff;

  border:1.5px solid var(--turquoise-light);

  border-radius:28px;

  padding:38px 34px;

  box-shadow:var(--shadow-md);

  position:relative;

  overflow:hidden;
}

.login-card::before {

  content:'';

  position:absolute;

  top:0;
  right:0;
  left:0;

  height:7px;

  background:linear-gradient(
      90deg,
      var(--turquoise-dark),
      var(--turquoise)
  );
}

.logo-wrap {

  width:95px;
  height:95px;

  margin:0 auto 22px;

  border-radius:24px;

  background:var(--turquoise-light);

  display:flex;

  align-items:center;

  justify-content:center;

  box-shadow:var(--shadow-sm);
}

.logo-wrap img {

  width:78%;
  height:78%;

  object-fit:contain;
}

.title {

  text-align:center;

  margin-bottom:8px;

  font-size:1.6rem;

  font-weight:800;

  color:var(--text);
}

.subtitle {

  text-align:center;

  color:var(--gray);

  margin-bottom:28px;

  font-size:.9rem;

  line-height:1.9;
}

.field {

  margin-bottom:18px;
}

.field label {

  display:block;

  margin-bottom:8px;

  font-size:.9rem;

  font-weight:700;

  color:var(--text);
}

.field input {

  width:100%;

  padding:14px 16px;

  border-radius:16px;

  border:1.5px solid #d4eef1;

  background:#fbfeff;

  font-family:'Vazirmatn';

  font-size:.95rem;

  transition:var(--transition);
}

.field input:focus {

  outline:none;

  border-color:var(--turquoise);

  background:#fff;

  box-shadow:0 0 0 4px rgba(25,184,194,.10);
}

.login-btn {

  width:100%;

  border:none;

  background:linear-gradient(
      135deg,
      var(--turquoise),
      var(--turquoise-dark)
  );

  color:#fff;

  padding:15px;

  border-radius:16px;

  font-family:'Vazirmatn';

  font-size:1rem;

  font-weight:800;

  cursor:pointer;

  transition:var(--transition);

  margin-top:8px;
}

.login-btn:hover {

  transform:translateY(-2px);

  box-shadow:0 10px 25px rgba(25,184,194,.28);
}

.error {

  background:#fff0f0;

  border:1px solid #ffd2d2;

  color:#c62828;

  padding:14px 16px;

  border-radius:14px;

  margin-bottom:20px;

  font-size:.88rem;
}

.footer-note {

  margin-top:24px;

  text-align:center;

  color:var(--gray);

  font-size:.8rem;

  line-height:1.9;
}

</style>

  <?php include 'header_styles.php'; ?>
</head>

<body>
<?php include 'topbar.php'; ?>
<div class="layout">
<?php include 'sidebar.php'; ?>
<main class="content">












<div class="login-card">

  <div class="logo-wrap">

    <img src="/images/logo-Tw.png">

  </div>

  <div class="title">

    ورود مدیریت

  </div>

  <div class="subtitle">

    سامانه مدیریت دبیرستان دخترانه توانش

  </div>

  <?php if($error): ?>

    <div class="error">

      <?= htmlspecialchars($error) ?>

    </div>

  <?php endif; ?>

  <form method="POST">

    <div class="field">

      <label>نام کاربری</label>

      <input type="text"
             name="username"
             required>

    </div>

    <div class="field">

      <label>رمز عبور</label>

      <input type="password"
             name="password"
             required>

    </div>

    <button type="submit"
            class="login-btn">

      ورود به پنل مدیریت

    </button>

  </form>

  <div class="footer-note">

    © سامانه مدیریت دبیرستان توانش

  </div>

</div>





</main>
</div>
</body>
</html>