<?php
require_once 'auth.php';
requireLogin();

$db = getDB();
$active_year = $_SESSION['active_year'] ?? '1404-1405';
$user_id = $_SESSION['username'];
$role = $_SESSION['role'];
$isAdmin = (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) || ($_SESSION['role'] === 'admin');

// Check if user is a teacher or admin
$isTeacher = false;
if ($role === 'staff') {
    $stmt = $db->prepare("SELECT position FROM staff_profiles WHERE national_id = ? AND academic_year = ?");
    $stmt->execute([$user_id, $active_year]);
    $staff_info = $stmt->fetch();
    $isTeacher = ($staff_info && $staff_info['position'] && strpos($staff_info['position'], 'دبیر') !== false);
}

if (!$isTeacher && !$isAdmin) {
    die("دسترسی محدود شده است.");
}

$msgs = [];

// Handle Exam Creation/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_exam'])) {
        $id = $_POST['exam_id'] ?? null;
        $title = trim($_POST['title']);
        $date_y = $_POST['date_y'];
        $date_m = $_POST['date_m'];
        $date_d = $_POST['date_d'];
        $date = sprintf("%04d/%02d/%02d", $date_y, $date_m, $date_d);
        $lesson = trim($_POST['lesson']);
        $grade = $_POST['grade'];
        $major = $_POST['major'];
        $max_score = (float)$_POST['max_score'];
        $is_published = isset($_POST['is_published']) ? 1 : 0;
        $teacher_id = $isAdmin ? $_POST['teacher_id'] : $user_id;

        if ($id) {
            // Update
            $sql = "UPDATE exams SET title=?, date=?, lesson=?, grade=?, major=?, teacher_id=?, max_score=?, is_published=? WHERE id=?";
            $params = [$title, $date, $lesson, $grade, $major, $teacher_id, $max_score, $is_published, $id];
            if (!$isAdmin) {
                $sql .= " AND teacher_id=?";
                $params[] = $user_id;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $msgs[] = ['type' => 'success', 'text' => '✅ امتحان با موفقیت بروزرسانی شد.'];
        } else {
            // Insert
            $stmt = $db->prepare("INSERT INTO exams (title, date, lesson, grade, major, teacher_id, max_score, is_published, academic_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $date, $lesson, $grade, $major, $teacher_id, $max_score, $is_published, $active_year]);
            $msgs[] = ['type' => 'success', 'text' => '✅ امتحان جدید با موفقیت ثبت شد.'];
        }
    }

    if (isset($_POST['delete_exam'])) {
        $id = $_POST['exam_id'];
        $sql = "DELETE FROM exams WHERE id=?";
        $params = [$id];
        if (!$isAdmin) {
            $sql .= " AND teacher_id=?";
            $params[] = $user_id;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        // Also delete scores
        $db->prepare("DELETE FROM scores WHERE exam_id=?")->execute([$id]);
        $msgs[] = ['type' => 'success', 'text' => '✅ امتحان و نمرات آن حذف شدند.'];
    }
}

// Fetch Exams
if ($isAdmin) {
    $stmt = $db->prepare("SELECT e.*, st.first_name, st.last_name FROM exams e LEFT JOIN staff_profiles st ON e.teacher_id = st.national_id AND e.academic_year = st.academic_year WHERE e.academic_year = ? ORDER BY e.date DESC");
    $stmt->execute([$active_year]);
} else {
    $stmt = $db->prepare("SELECT * FROM exams WHERE teacher_id = ? AND academic_year = ? ORDER BY date DESC");
    $stmt->execute([$user_id, $active_year]);
}
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Teachers for Admin
$teachers = [];
if ($isAdmin) {
    $stmt = $db->prepare("SELECT national_id, first_name, last_name FROM staff_profiles WHERE academic_year = ? AND position LIKE '%دبیر%'");
    $stmt->execute([$active_year]);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get Exam for Editing
$edit_exam = null;
if (isset($_GET['edit'])) {
    $sql = "SELECT * FROM exams WHERE id=?";
    $params = [$_GET['edit']];
    if (!$isAdmin) {
        $sql .= " AND teacher_id=?";
        $params[] = $user_id;
    }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $edit_exam = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>مدیریت امتحانات — دبیرستان توانش</title>
<link rel="icon" href="/images/logo-T.png" type="image/png">
<style>
@font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Regular.woff2') format('woff2'); font-weight:400; font-display:swap; }
@font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Bold.woff2') format('woff2'); font-weight:700; font-display:swap; }
body { font-family:'Vazirmatn', sans-serif; background:#f5fbfd; color:#0f3d42; padding:20px; line-height:1.6; }
.container { max-width:1000px; margin:0 auto; }
.card { background:#fff; border-radius:18px; padding:20px; box-shadow:0 4px 15px rgba(0,0,0,.05); border:1.5px solid #e6f8fa; margin-bottom:20px; }
h1, h2, h3 { color:#0c8790; margin-bottom:15px; }
.field { margin-bottom:12px; }
label { display:block; font-size:.8rem; font-weight:700; margin-bottom:4px; color:#4e8a90; }
input[type=text], input[type=number], select { width:100%; padding:8px 12px; border-radius:10px; border:1.5px solid #c0e5ea; font-family:Vazirmatn; font-size:.9rem; }
.btn { padding:10px 20px; border-radius:10px; border:none; cursor:pointer; font-family:Vazirmatn; font-weight:700; transition:0.3s; text-decoration:none; display:inline-block; }
.btn-primary { background:#19b8c2; color:#fff; }
.btn-primary:hover { background:#0c8790; }
.btn-danger { background:#c94040; color:#fff; }
.btn-secondary { background:#e6f8fa; color:#0c8790; }
.alert { padding:12px; border-radius:12px; margin-bottom:20px; }
.alert-success { background:#e6f9f0; color:#1a9960; border:1px solid #d1f2e1; }
.table-wrap { overflow-x:auto; border-radius:14px; border:1.5px solid #e6f8fa; }
table { width:100%; border-collapse:collapse; background:#fff; }
th, td { padding:12px; text-align:right; border-bottom:1px solid #f0fbfd; }
th { background:#f0fbfd; color:#0c8790; font-size:.85rem; }
.badge { padding:4px 8px; border-radius:6px; font-size:.75rem; font-weight:700; }
.badge-success { background:#e6f9f0; color:#1a9960; }
.badge-warning { background:#fff9e6; color:#997a1a; }
</style>
</head>
<body>
<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1>📝 مدیریت امتحانات</h1>
        <a href="dashboard.php" class="btn btn-secondary">← بازگشت به داشبورد</a>
    </div>

    <?php foreach ($msgs as $m): ?>
        <div class="alert alert-<?= $m['type'] === 'success' ? 'success' : 'danger' ?>"><?= $m['text'] ?></div>
    <?php endforeach; ?>

    <div class="card">
        <h3><?= $edit_exam ? '✏️ ویرایش امتحان' : '➕ ایجاد امتحان جدید' ?></h3>
        <form method="POST" action="manage_exams.php">
            <?php if ($edit_exam): ?>
                <input type="hidden" name="exam_id" value="<?= $edit_exam['id'] ?>">
            <?php endif; ?>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="field">
                    <label>عنوان امتحان</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($edit_exam['title'] ?? '') ?>" required placeholder="مثلاً: میان‌ترم فصل اول">
                </div>
                <div class="field">
                    <label>تاریخ امتحان</label>
                    <?php
                        $d_y = $d_m = $d_d = "";
                        if (!empty($edit_exam['date'])) {
                            list($d_y, $d_m, $d_d) = explode('/', $edit_exam['date']);
                        } else {
                            list($d_y, $d_m, $d_d) = explode('/', get_jalali_today());
                        }
                    ?>
                    <div style="display:flex; gap:5px; direction:rtl;">
                        <select name="date_d" style="flex:1;">
                            <?php for($i=1; $i<=31; $i++) echo "<option value='".sprintf("%02d",$i)."' ".((int)$d_d==$i?'selected':'').">$i</option>"; ?>
                        </select>
                        <select name="date_m" style="flex:1;">
                            <?php for($i=1; $i<=12; $i++) echo "<option value='".sprintf("%02d",$i)."' ".((int)$d_m==$i?'selected':'').">$i</option>"; ?>
                        </select>
                        <select name="date_y" style="flex:1;">
                            <?php foreach(['1403','1404','1405','1406'] as $y) echo "<option value='$y' ".($d_y==$y?'selected':'').">$y</option>"; ?>
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label>درس</label>
                    <input type="text" name="lesson" value="<?= htmlspecialchars($edit_exam['lesson'] ?? '') ?>" required placeholder="مثلاً: ریاضی">
                </div>
                <div class="field">
                    <label>پایه</label>
                    <select name="grade" required>
                        <?php foreach(['دهم','یازدهم','دوازدهم'] as $g) echo "<option value='$g' ".((($edit_exam['grade']??'')==$g)?'selected':'').">$g</option>"; ?>
                    </select>
                </div>
                <div class="field">
                    <label>رشته</label>
                    <select name="major" required>
                        <?php foreach(['ریاضی','تجربی','انسانی'] as $m) echo "<option value='$m' ".((($edit_exam['major']??'')==$m)?'selected':'').">$m</option>"; ?>
                    </select>
                </div>
                <div class="field">
                    <label>نمره از چند</label>
                    <input type="number" step="0.25" name="max_score" value="<?= htmlspecialchars($edit_exam['max_score'] ?? '20') ?>" required>
                </div>
                <?php if ($isAdmin): ?>
                <div class="field">
                    <label>دبیر</label>
                    <select name="teacher_id" required>
                        <?php foreach($teachers as $t) echo "<option value='{$t['national_id']}' ".((($edit_exam['teacher_id']??'')==$t['national_id'])?'selected':'').">{$t['first_name']} {$t['last_name']}</option>"; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="field" style="display:flex; align-items:center; gap:10px; margin-top:25px;">
                    <input type="checkbox" name="is_published" id="pub" <?= ($edit_exam ? ($edit_exam['is_published'] ? 'checked' : '') : 'checked') ?>>
                    <label for="pub" style="margin-bottom:0;">انتشار نمرات برای دانش‌آموزان</label>
                </div>
            </div>
            <div style="margin-top:10px;">
                <button type="submit" name="save_exam" class="btn btn-primary"><?= $edit_exam ? 'بروزرسانی امتحان' : 'ثبت امتحان' ?></button>
                <?php if ($edit_exam): ?>
                    <a href="manage_exams.php" class="btn btn-secondary">انصراف</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <h3>📋 لیست امتحانات</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>عنوان</th>
                        <th>درس</th>
                        <th>پایه و رشته</th>
                        <th>تاریخ</th>
                        <?php if ($isAdmin): ?><th>دبیر</th><?php endif; ?>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($exams)): ?>
                        <tr><td colspan="<?= $isAdmin ? 7 : 6 ?>" style="text-align:center;">هنوز امتحانی ثبت نشده است.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($exams as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['title']) ?></td>
                        <td><?= htmlspecialchars($e['lesson']) ?></td>
                        <td><?= htmlspecialchars($e['grade'] . ' - ' . $e['major']) ?></td>
                        <td><?= to_persian_num($e['date']) ?></td>
                        <?php if ($isAdmin): ?>
                            <td><?= htmlspecialchars(($e['first_name']??'') . ' ' . ($e['last_name']??'')) ?></td>
                        <?php endif; ?>
                        <td>
                            <?php if ($e['is_published']): ?>
                                <span class="badge badge-success">منتشر شده</span>
                            <?php else: ?>
                                <span class="badge badge-warning">منتشر نشده</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="manage_scores.php?exam_id=<?= $e['id'] ?>" class="btn btn-secondary" style="font-size:.75rem;">📝 ثبت نمرات</a>
                            <a href="manage_exams.php?edit=<?= $e['id'] ?>" class="btn btn-primary" style="font-size:.75rem;">✏️</a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('آیا از حذف این امتحان و تمامی نمرات آن مطمئن هستید؟');">
                                <input type="hidden" name="exam_id" value="<?= $e['id'] ?>">
                                <button type="submit" name="delete_exam" class="btn btn-danger" style="font-size:.75rem;">🗑️</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
