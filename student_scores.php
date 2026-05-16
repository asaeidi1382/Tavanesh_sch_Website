<?php
require_once 'auth.php';
requireLogin();

$db = getDB();
$selected_year = $_GET['year'] ?? ($_SESSION['active_year'] ?? '1404-1405');
$user_id = $_SESSION['username'];
$role = $_SESSION['role'];

if ($role !== 'student') {
    die("این صفحه مختص دانش‌آموزان است.");
}

// واکشی پروفایل دانش‌آموز برای سال انتخابی
$stmt = $db->prepare("SELECT grade, major FROM student_profiles WHERE national_id = ? AND academic_year = ?");
$stmt->execute([$user_id, $selected_year]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

$exams = [];
$profile_missing = false;

if (!$profile) {
    $profile_missing = true;
} else {
    // Fetch published exams and the student's scores filtering by grade and major
    $stmt = $db->prepare("
        SELECT e.*, s.score, s.status, s.description, st.first_name as t_first, st.last_name as t_last
        FROM exams e
        LEFT JOIN scores s ON e.id = s.exam_id AND s.student_id = ?
        LEFT JOIN staff_profiles st ON e.teacher_id = st.national_id AND e.academic_year = st.academic_year
        WHERE e.academic_year = ? AND e.is_published = 1 AND e.grade = ? AND e.major = ?
        ORDER BY e.date DESC
    ");
    $stmt->execute([$user_id, $selected_year, $profile['grade'], $profile['major']]);
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

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
.score-value { font-weight:700; font-size:1.1rem; }
.score-low { color:#c94040; }
.score-medium { color:#ff9800; }
.score-high { color:#19b8c2; }
.score-excellent { color:#1a9960; }
.status-absent { color:#c94040; }
.status-excused { color:#997a1a; }
th { cursor:pointer; user-select:none; }
th:hover { background:#e0f2f4 !important; }
</style>
</head>
<body>
<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:10px;">
        <div style="display:flex; align-items:center; gap:15px;">
            <h1>🏆 نمرات من</h1>
            <select onchange="location.href='?year='+this.value" style="padding:5px 10px; border-radius:8px; border:1.5px solid #c0e5ea; font-family:Vazirmatn; font-size:0.9rem; color:#0c8790; outline:none;">
                <?php 
                $years = ['1402-1403', '1403-1404', '1404-1405', '1404-1405', '1406-1407'];
                foreach($years as $y): ?>
                    <option value="<?= $y ?>" <?= $y===$selected_year?'selected':'' ?>><?= to_persian_num($y) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <a href="dashboard.php" class="btn btn-secondary">← بازگشت به داشبورد</a>
    </div>

    <div class="card">
        <?php if ($profile_missing): ?>
            <div style="text-align:center; padding:40px; color:#c94040;">
                <h3 style="margin-bottom:10px;">⚠️ عدم ثبت اطلاعات</h3>
                <p>در سال تحصیلی <?= to_persian_num($selected_year) ?> اطلاعاتی برای شما ثبت نگردیده است.</p>
            </div>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead id="scoresHead">
                    <tr>
                        <th onclick="sortTable(0)">عنوان امتحان ↕</th>
                        <th onclick="sortTable(1)">تاریخ ↕</th>
                        <th onclick="sortTable(2)">درس ↕</th>
                        <th onclick="sortTable(3)">دبیر ↕</th>
                        <th onclick="sortTable(4)">نمره ↕</th>
                        <th onclick="sortTable(5)">از چند ↕</th>
                        <th onclick="sortTable(6)">وضعیت ↕</th>
                        <th onclick="sortTable(7)">توضیحات دبیر ↕</th>
                    </tr>
                </thead>
                <tbody id="scoresBody">
                    <?php if (empty($exams)): ?>
                        <tr><td colspan="8" style="text-align:center; padding:30px;">هنوز نمره‌ای برای این سال تحصیلی منتشر نشده است.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($exams as $e): ?>
                    <tr>
                        <td style="font-weight:700;"><?= htmlspecialchars($e['title']) ?></td>
                        <td><?= to_persian_num($e['date']) ?></td>
                        <td><?= htmlspecialchars($e['lesson']) ?></td>
                        <td><?= htmlspecialchars($e['t_first'] . ' ' . $e['t_last']) ?></td>
                        <td class="score-value" data-val="<?= $e['score'] ?? -1 ?>">
                            <?php
                            if ($e['status'] === 'present' && $e['score'] !== null) {
                                $s = $e['score'];
                                $class = 'score-high';
                                if ($s < 10) $class = 'score-low';
                                elseif ($s < 15) $class = 'score-medium';
                                elseif ($s >= 18) $class = 'score-excellent';
                                echo "<span class='$class'>" . to_persian_num($s) . "</span>";
                            } else {
                                echo '—';
                            }
                            ?>
                        </td>
                        <td><?= to_persian_num($e['max_score']) ?></td>
                        <td class="status-<?= $e['status'] ?>">
                            <?= getStatusLabel($e['status']) ?>
                        </td>
                        <td style="font-size:.85rem; color:var(--gray);"><?= htmlspecialchars($e['description'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<script>
function sortTable(n) {
    var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    table = document.getElementById("scoresBody");
    switching = true;
    dir = "asc";
    while (switching) {
        switching = false;
        rows = table.rows;
        for (i = 0; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("TD")[n];
            y = rows[i + 1].getElementsByTagName("TD")[n];

            var xVal = x.getAttribute('data-val') || x.innerText.toLowerCase();
            var yVal = y.getAttribute('data-val') || y.innerText.toLowerCase();

            // Check if numeric
            if (!isNaN(parseFloat(xVal)) && isFinite(xVal) && !isNaN(parseFloat(yVal)) && isFinite(yVal)) {
                xVal = parseFloat(xVal);
                yVal = parseFloat(yVal);
            }

            if (dir == "asc") {
                if (xVal > yVal) {
                    shouldSwitch = true;
                    break;
                }
            } else if (dir == "desc") {
                if (xVal < yVal) {
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchcount++;
        } else {
            if (switchcount == 0 && dir == "asc") {
                dir = "desc";
                switching = true;
            }
        }
    }
}
</script>
</body>
</html>
