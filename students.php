<?php

require_once 'auth_new.php';

requireAdmin();

$db = getDB();

$search = trim($_GET['search'] ?? '');

if ($search) {

    $stmt = $db->prepare("
        SELECT *
        FROM students
        WHERE
            full_name LIKE ?
            OR national_id LIKE ?
        ORDER BY id DESC
    ");

    $stmt->execute([
        "%$search%",
        "%$search%"
    ]);

} else {

    $stmt = $db->query("
        SELECT *
        FROM students
        ORDER BY id DESC
    ");
}

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>

<html lang="fa" dir="rtl">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>مدیریت دانش‌آموزان</title>

<link rel="icon"
      href="/images/logo-T.png">

<style>





*{
  margin:0;
  padding:0;
  box-sizing:border-box;
}

:root {

  --turquoise:#19b8c2;
  --turquoise-dark:#0d8790;
  --turquoise-light:#e8f8fa;

  --text:#103d42;
  --gray:#618b90;

  --shadow:0 10px 30px rgba(0,0,0,.08);
}

body {

  background:#f5fbfd;

  font-family:'Vazirmatn';

  color:var(--text);
}

.topbar {

  background:var(--turquoise);

  color:#fff;

  box-shadow:var(--shadow);
}

.topbar-inner {

  max-width:1300px;

  margin:auto;

  padding:15px 20px;

  display:flex;

  align-items:center;

  justify-content:space-between;
}

.brand {

  font-size:1.2rem;

  font-weight:700;
}

.top-actions {

  display:flex;

  align-items:center;

  gap:12px;
}

.top-btn {

  background:rgba(255,255,255,.16);

  color:#fff;

  text-decoration:none;

  padding:10px 15px;

  border-radius:12px;

  font-size:.9rem;

  border:1px solid rgba(255,255,255,.2);
}

.container {

  max-width:1300px;

  margin:auto;

  padding:35px 20px;
}

.page-head {

  display:flex;

  justify-content:space-between;

  align-items:center;

  margin-bottom:28px;

  gap:20px;

  flex-wrap:wrap;
}

.page-title {

  font-size:1.6rem;

  font-weight:700;
}

.add-btn {

  background:linear-gradient(
      135deg,
      var(--turquoise),
      var(--turquoise-dark)
  );

  color:#fff;

  text-decoration:none;

  padding:14px 22px;

  border-radius:16px;

  font-size:.92rem;

  font-weight:700;

  box-shadow:var(--shadow);
}

.search-box {

  background:#fff;

  border-radius:20px;

  padding:18px;

  margin-bottom:28px;

  box-shadow:var(--shadow);
}

.search-form {

  display:flex;

  gap:12px;
}

.search-form input {

  flex:1;

  padding:14px 16px;

  border-radius:14px;

  border:1.5px solid #d7eef1;

  font-family:'Vazirmatn';
}

.search-form button {

  background:var(--turquoise);

  color:#fff;

  border:none;

  border-radius:14px;

  padding:0 22px;

  font-family:'Vazirmatn';

  cursor:pointer;
}

.table-card {

  background:#fff;

  border-radius:24px;

  overflow:hidden;

  box-shadow:var(--shadow);
}

table {

  width:100%;

  border-collapse:collapse;
}

thead {

  background:var(--turquoise-light);
}

th {

  padding:18px;

  text-align:right;

  font-size:.92rem;
}

td {

  padding:18px;

  border-top:1px solid #eef6f7;

  font-size:.9rem;
}

tr:hover {

  background:#fbfeff;
}

.badge {

  display:inline-block;

  padding:7px 12px;

  border-radius:999px;

  background:#e7f8fa;

  color:var(--turquoise-dark);

  font-size:.8rem;

  font-weight:700;
}

.action-btn {

  text-decoration:none;

  padding:8px 14px;

  border-radius:10px;

  font-size:.82rem;

  display:inline-block;
}

.view-btn {

  background:#e8f8fa;

  color:var(--turquoise-dark);
}

.delete-btn {

  background:#ffecec;

  color:#d32f2f;
}

.empty {

  padding:60px 20px;

  text-align:center;

  color:var(--gray);
}

@media(max-width:900px){

  .table-card {

    overflow:auto;
  }

  table {

    min-width:900px;
  }
}

</style>

  <?php include 'header_styles.php'; ?>
</head>

<body>
<?php include 'topbar.php'; ?>
<div class="layout">
<?php include 'sidebar.php'; ?>
<main class="content">














<div class="container">

  <div class="page-head">

    <div class="page-title">

      لیست دانش‌آموزان

    </div>

    <a href="add_student.php"
       class="add-btn">

      ➕ افزودن دانش‌آموز

    </a>

  </div>

  <div class="search-box">

    <form method="GET"
          class="search-form">

      <input type="text"
             name="search"
             value="<?= htmlspecialchars($search) ?>"
             placeholder="جستجو بر اساس نام یا کد ملی">

      <button type="submit">

        جستجو

      </button>

    </form>

  </div>

  <div class="table-card">

    <?php if(count($students)): ?>

    <table>

      <thead>

        <tr>

          <th>#</th>

          <th>نام دانش‌آموز</th>

          <th>کد ملی</th>

          <th>پایه</th>

          <th>رشته</th>

          <th>وضعیت</th>

          <th>عملیات</th>

        </tr>

      </thead>

      <tbody>

      <?php foreach($students as $student): ?>

        <tr>

          <td>

            <?= $student['id'] ?>

          </td>

          <td>

            <?= htmlspecialchars($student['full_name']) ?>

          </td>

          <td>

            <?= htmlspecialchars($student['national_id']) ?>

          </td>

          <td>

            <?= htmlspecialchars($student['grade']) ?>

          </td>

          <td>

            <?= htmlspecialchars($student['major']) ?>

          </td>

          <td>

            <span class="badge">

              فعال

            </span>

          </td>

          <td>

				<a href="student_profile.php?id=<?= $student['id'] ?>"
				class="action-btn view-btn">

               مشاهده

            </a>

				<a href="delete_student.php?id=<?= $student['id'] ?>"
				class="action-btn delete-btn"
				onclick="return confirm('حذف شود؟')">

               حذف

            </a>

          </td>

        </tr>

      <?php endforeach; ?>

      </tbody>

    </table>

    <?php else: ?>

      <div class="empty">

        هنوز دانش‌آموزی ثبت نشده است.

      </div>

    <?php endif; ?>

  </div>

</div>





</main>
</div>
</body>
</html>