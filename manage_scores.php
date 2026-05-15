<?php
require_once 'auth.php';
requireLogin();

$db = getDB();
$active_year = $_SESSION['active_year'] ?? '1404-1405';
$user_id = $_SESSION['username'];
$isAdmin = (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) || ($_SESSION['role'] === 'admin');

if (!isset($_GET['exam_id'])) {
    header("Location: manage_exams.php");
    exit;
}

$exam_id = (int)$_GET['exam_id'];

// Fetch Exam details
$sql = "SELECT * FROM exams WHERE id=?";
$params = [$exam_id];
if (!$isAdmin) {
    $sql .= " AND teacher_id=?";
    $params[] = $user_id;
}
$stmt = $db->prepare($sql);
$stmt->execute($params);
$exam = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exam) {
    die("امتحان یافت نشد یا دسترسی محدود است.");
}

$msgs = [];

// Handle Scores Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_scores'])) {
    $scores_data = $_POST['scores'] ?? [];
    $db->beginTransaction();
    try {
        foreach ($scores_data as $student_id => $data) {
            $score = ($data['score'] === '') ? null : (float)$data['score'];
            $status = $data['status'];
            $description = trim($data['description'] ?? '');

            // Insert or Update score
            $stmt = $db->prepare("INSERT INTO scores (exam_id, student_id, score, status, description)
                                  VALUES (?, ?, ?, ?, ?)
                                  ON CONFLICT(exam_id, student_id) DO UPDATE SET
                                  score = excluded.score,
                                  status = excluded.status,
                                  description = excluded.description");
            $stmt->execute([$exam_id, $student_id, $score, $status, $description]);
        }
        $db->commit();
        $msgs[] = ['type' => 'success', 'text' => '✅ نمرات با موفقیت ثبت شدند.'];
    } catch (Exception $e) {
        $db->rollBack();
        $msgs[] = ['type' => 'error', 'text' => '❌ خطا در ثبت نمرات: ' . $e->getMessage()];
    }
}

// Fetch Students in the same Grade and Major
$stmt = $db->prepare("SELECT national_id, first_name, last_name FROM student_profiles WHERE grade = ? AND major = ? AND academic_year = ? ORDER BY last_name, first_name ASC");
$stmt->execute([$exam['grade'], $exam['major'], $active_year]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch existing scores
$stmt = $db->prepare("SELECT * FROM scores WHERE exam_id = ?");
$stmt->execute([$exam_id]);
$existing_scores_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
$existing_scores = [];
foreach ($existing_scores_raw as $s) {
    $existing_scores[$s['student_id']] = $s;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ثبت نمرات — <?= htmlspecialchars($exam['title']) ?></title>
<link rel="icon" href="/images/logo-T.png" type="image/png">
<style>
@font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Regular.woff2') format('woff2'); font-weight:400; font-display:swap; }
@font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Bold.woff2') format('woff2'); font-weight:700; font-display:swap; }
body { font-family:'Vazirmatn', sans-serif; background:#f5fbfd; color:#0f3d42; padding:20px; line-height:1.6; }
.container { max-width:1000px; margin:0 auto; }
.card { background:#fff; border-radius:18px; padding:25px; box-shadow:0 4px 15px rgba(0,0,0,.05); border:1.5px solid #e6f8fa; margin-bottom:20px; }
h1, h2, h3 { color:#0c8790; margin-bottom:10px; }
.info-bar { background:#e6f8fa; padding:15px; border-radius:12px; margin-bottom:20px; display:flex; gap:20px; font-size:.9rem; }
.btn { padding:10px 20px; border-radius:10px; border:none; cursor:pointer; font-family:Vazirmatn; font-weight:700; transition:0.3s; text-decoration:none; display:inline-block; }
.btn-primary { background:#19b8c2; color:#fff; }
.btn-primary:hover { background:#0c8790; }
.btn-secondary { background:#e6f8fa; color:#0c8790; }
.table-wrap { overflow-x:auto; border-radius:14px; border:1.5px solid #e6f8fa; }
table { width:100%; border-collapse:collapse; background:#fff; }
th, td { padding:12px; text-align:right; border-bottom:1px solid #f0fbfd; }
th { background:#f0fbfd; color:#0c8790; font-size:.85rem; }
input[type=number], input[type=text], select { padding:8px; border-radius:8px; border:1.5px solid #c0e5ea; font-family:Vazirmatn; }
.alert { padding:12px; border-radius:12px; margin-bottom:20px; }
.alert-success { background:#e6f9f0; color:#1a9960; border:1px solid #d1f2e1; }
.alert-error { background:#fceaea; color:#c94040; border:1px solid #f9d6d6; }
</style>
</head>
<body>
<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <h1>📝 ثبت نمرات</h1>
        <a href="manage_exams.php" class="btn btn-secondary">← بازگشت به لیست امتحانات</a>
    </div>

    <div class="info-bar">
        <div><strong>امتحان:</strong> <?= htmlspecialchars($exam['title']) ?></div>
        <div><strong>درس:</strong> <?= htmlspecialchars($exam['lesson']) ?></div>
        <div><strong>پایه:</strong> <?= htmlspecialchars($exam['grade']) ?></div>
        <div><strong>رشته:</strong> <?= htmlspecialchars($exam['major']) ?></div>
        <div><strong>نمره از:</strong> <?= to_persian_num($exam['max_score']) ?></div>
    </div>

    <?php foreach ($msgs as $m): ?>
        <div class="alert alert-<?= $m['type'] === 'success' ? 'success' : 'error' ?>"><?= $m['text'] ?></div>
    <?php endforeach; ?>

    <form method="POST">
        <div class="card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>نام و نام خانوادگی</th>
                            <th>کد ملی</th>
                            <th>نمره</th>
                            <th>وضعیت</th>
                            <th>توضیحات (اختیاری)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr><td colspan="5" style="text-align:center;">دانش‌آموزی در این پایه و رشته یافت نشد.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($students as $s):
                            $sid = $s['national_id'];
                            $current_score = $existing_scores[$sid]['score'] ?? '';
                            $current_status = $existing_scores[$sid]['status'] ?? 'present';
                            $current_desc = $existing_scores[$sid]['description'] ?? '';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
                            <td><?= to_persian_num($sid) ?></td>
                            <td>
                                <input type="number" step="0.25" min="0" max="<?= $exam['max_score'] ?>" name="scores[<?= $sid ?>][score]" value="<?= $current_score ?>" style="width:70px;" placeholder="---">
                            </td>
                            <td>
                                <select name="scores[<?= $sid ?>][status]">
                                    <option value="present" <?= $current_status === 'present' ? 'selected' : '' ?>>حاضر</option>
                                    <option value="absent" <?= $current_status === 'absent' ? 'selected' : '' ?>>غایب</option>
                                    <option value="excused" <?= $current_status === 'excused' ? 'selected' : '' ?>>غایب موجه</option>
                                    <option value="not_recorded" <?= $current_status === 'not_recorded' ? 'selected' : '' ?>>ثبت نشده</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="scores[<?= $sid ?>][description]" value="<?= htmlspecialchars($current_desc) ?>" style="width:100%;">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top:20px; text-align:center;">
                <button type="submit" name="save_scores" class="btn btn-primary" style="padding:12px 40px; font-size:1.1rem;">💾 ثبت کل نمرات</button>
            </div>
        </div>
    </form>
</div>
</body>
</html>
