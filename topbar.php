<?php
// Function definitions wrapped in !function_exists to prevent redeclaration fatal errors
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}

$isLoggedIn = isLoggedIn();
$fullName = '';
$profile_image = null;

if ($isLoggedIn) {
    $fullName = $_SESSION['full_name'] ?? $_SESSION['username'];
    if (function_exists('getDB')) {
        try {
            $db = getDB();
            // Check if it's the SQLite database from auth.php by seeing if users table exists in this way
            // or just try-catch the query.
            $stmt = $db->prepare("SELECT profile_image FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $profile_image = $stmt->fetchColumn();
        } catch (Exception $e) {
            // Probably different DB schema (MySQL from auth_new.php)
        }
    }
}
?>
<header class="topbar">
  <div class="topbar-inner">
    <div class="brand">
      <div class="logo"><img src="images/logo-Tw.png" alt="لوگو توانش"></div>
      <div class="titles">
        <div class="site-title">دبیرستان دخترانه توانش</div>
        <div class="page-subtitle">خانه دختران توانمند فردا</div>
      </div>
    </div>
    <div class="topbar-left">
        <a href="<?= $isLoggedIn ? 'dashboard.php' : 'login.php' ?>" class="user-badge">
            <div class="user-img">
                <?php if ($isLoggedIn && $profile_image): ?>
                    <img src="<?= htmlspecialchars($profile_image) ?>" alt="تصویر پروفایل">
                <?php elseif ($isLoggedIn): ?>
                    👤
                <?php else: ?>
                    🔑
                <?php endif; ?>
            </div>
            <?= $isLoggedIn ? (function_exists('to_persian_num') ? to_persian_num(htmlspecialchars($fullName)) : htmlspecialchars($fullName)) : 'ورود کاربران' ?>
        </a>
        <div class="hamburger" id="hamburgerBtn"><span class="bar"></span></div>
    </div>
  </div>
</header>
