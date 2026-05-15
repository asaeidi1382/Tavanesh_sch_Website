<?php
require_once 'auth.php';
requireLogin();

$db = getDB();
$active_year = $_SESSION['active_year'] ?? '1404-1405';
$user_id = $_SESSION['username'];
$role = $_SESSION['role'];

if ($role !== 'student') {
    die("این صفحه مختص دانش‌آموزان است.");
}

// Fetch published exams and the student's scores
$stmt = $db->prepare("
    SELECT e.*, s.score, s.status, s.description, st.first_name as t_first, st.last_name as t_last
    FROM exams e
    LEFT JOIN scores s ON e.id = s.exam_id AND s.student_id = ?
    LEFT JOIN staff_profiles st ON e.teacher_id = st.national_id AND e.academic_year = st.academic_year
    WHERE e.academic_year = ? AND e.is_published = 1
    ORDER BY e.date DESC
");
$stmt->execute([$user_id, $active_year]);
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStatusLabel($status) {
    switch ($status) {
        case 'present': return 'حاضر';
        case 'absent': return 'غایب';
        case 'excused': return 'غایب موجه';
        case 'not_recorded': return 'ثبت نشده';
        default: return '—';
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>نمرات من — دبیرستان توانش</title>
<link rel="icon" href="/images/logo-T.png" type="image/png">
<style>
@font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Regular.woff2') format('woff2'); font-weight:400; font-display:swap; }
@font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Bold.woff2') format('woff2'); font-weight:700; font-display:swap; }
body { font-family:'Vazirmatn', sans-serif; background:#f5fbfd; color:#0f3d42; padding:20px; line-height:1.6; }
.container { max-width:900px; margin:0 auto; }
.card { background:#fff; border-radius:18px; padding:25px; box-shadow:0 4px 15px rgba(0,0,0,.05); border:1.5px solid #e6f8fa; }
h1 { color:#0c8790; margin-bottom:20px; text-align:center; }
.btn { padding:10px 20px; border-radius:10px; border:none; cursor:pointer; font-family:Vazirmatn; font-weight:700; transition:0.3s; text-decoration:none; display:inline-block; }
.btn-secondary { background:#e6f8fa; color:#0c8790; margin-bottom:20px; }
.table-wrap { overflow-x:auto; border-radius:14px; border:1.5px solid #e6f8fa; }
table { width:100%; border-collapse:collapse; background:#fff; }
th, td { padding:14px; text-align:right; border-bottom:1px solid #f0fbfd; }
th { background:#f0fbfd; color:#0c8790; font-size:.85rem; }
.score-value { font-weight:700; color:#19b8c2; font-size:1.1rem; }
.status-absent { color:#c94040; }
.status-excused { color:#997a1a; }
</style>
</head>
<body>
<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <h1>🏆 نمرات من</h1>
        <a href="dashboard.php" class="btn btn-secondary">← بازگشت به داشبورد</a>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>عنوان امتحان</th>
                        <th>تاریخ</th>
                        <th>درس</th>
                        <th>دبیر</th>
                        <th>نمره</th>
                        <th>از چند</th>
                        <th>وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($exams)): ?>
                        <tr><td colspan="7" style="text-align:center; padding:30px;">هنوز نمره‌ای منتشر نشده است.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($exams as $e): ?>
                    <tr>
                        <td style="font-weight:700;"><?= htmlspecialchars($e['title']) ?></td>
                        <td><?= to_persian_num($e['date']) ?></td>
                        <td><?= htmlspecialchars($e['lesson']) ?></td>
                        <td><?= htmlspecialchars($e['t_first'] . ' ' . $e['t_last']) ?></td>
                        <td class="score-value">
                            <?php
                            if ($e['status'] === 'present') {
                                echo ($e['score'] !== null) ? to_persian_num($e['score']) : '—';
                            } else {
                                echo '—';
                            }
                            ?>
                        </td>
                        <td><?= to_persian_num($e['max_score']) ?></td>
                        <td class="status-<?= $e['status'] ?>">
                            <?= getStatusLabel($e['status']) ?>
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
