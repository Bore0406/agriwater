<?php
session_start();
include "connect.php";
$u_account   = $_SESSION["u_account"] ?? '';
$loginStatus = !empty($u_account);

// 取得目前登入 u_no
$u_no = null;
if ($loginStatus) {
    $s = $conn->prepare("SELECT u_no FROM users WHERE u_account=:a");
    $s->bindParam(":a", $u_account); $s->execute();
    $row = $s->fetch(PDO::FETCH_ASSOC);
    $u_no = $row['u_no'] ?? null;
}

// 下拉選單：農田清單
$farms = [];
if ($u_no) {
    $s = $conn->prepare("SELECT farmland_id FROM farmland WHERE member_id=:m ORDER BY farmland_id");
    $s->bindParam(":m", $u_no); $s->execute();
    $farms = $s->fetchAll(PDO::FETCH_ASSOC);
}

// 查詢某農田本月所有耕種記錄
$selectedFarm = intval($_GET['farm'] ?? ($farms[0]['farmland_id'] ?? 0));
$selYear  = intval($_GET['year']  ?? date('Y'));
$selMonth = intval($_GET['month'] ?? date('m'));

$records = []; // date => water_data
if ($selectedFarm) {
    $ymd_start = sprintf('%04d-%02d-01', $selYear, $selMonth);
    $ymd_end   = date('Y-m-t', strtotime($ymd_start));
    $s = $conn->prepare("SELECT record_date, water_data FROM cultivation_record
                         WHERE farmland_id=:f AND record_date BETWEEN :s AND :e
                         ORDER BY record_date");
    $s->bindParam(":f", $selectedFarm);
    $s->bindParam(":s", $ymd_start);
    $s->bindParam(":e", $ymd_end);
    $s->execute();
    foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $records[$r['record_date']] = $r['water_data'];
    }
}

// 新增耕種記錄
if ($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["add_rec"]) && $u_no) {
    $fid  = intval($_POST["farmland_id"]);
    $date = $_POST["record_date"];
    $wd   = floatval($_POST["water_data"]);
    $s = $conn->prepare("INSERT INTO cultivation_record (farmland_id, record_date, water_data) VALUES (:f,:d,:w)");
    $s->bindParam(":f", $fid); $s->bindParam(":d", $date); $s->bindParam(":w", $wd);
    $s->execute();
    header("Location: search.php?farm=$fid&year=$selYear&month=$selMonth"); exit();
}

// 標準用水量（公升/天，理論值，可自訂）
$STANDARD = 500;

$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selMonth, $selYear);
$firstDow    = date('N', strtotime("$selYear-$selMonth-01")); // 1=Mon 7=Sun
?>
<!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>查詢用水 — 田地節能用水對比系統</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f1f8e9; }
    .cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:4px; }
    .cal-head { background:#2e7d32; color:white; text-align:center; padding:6px; border-radius:4px; font-weight:bold; }
    .cal-cell { background:white; border-radius:8px; min-height:72px; padding:6px;
                box-shadow:0 1px 4px rgba(0,0,0,0.08); cursor:pointer; transition:.2s; }
    .cal-cell:hover { transform:scale(1.03); }
    .cal-cell .day-num { font-weight:bold; color:#333; font-size:0.95rem; }
    .cal-cell.has-data.saving  { background:#e8f5e9; border-left:4px solid #2e7d32; }
    .cal-cell.has-data.wasting { background:#ffebee; border-left:4px solid #c62828; }
    .cal-cell.empty            { background:transparent; box-shadow:none; cursor:default; }
    .cal-cell .water-val { font-size:0.78rem; margin-top:2px; }
    .legend { display:flex; gap:16px; font-size:0.85rem; margin-bottom:12px; }
    .legend span { display:inline-block; width:14px; height:14px; border-radius:3px; margin-right:4px; vertical-align:middle; }
  </style>
</head>
<body>
<?php include('navbar.html'); ?>
<div class="container py-4">
  <h2 class="fw-bold text-success mb-3">🔍 查詢用水記錄</h2>

  <?php if (!$loginStatus): ?>
    <div class="alert alert-warning">請先 <a href="login.php">登入</a> 才能查詢。</div>
  <?php else: ?>

  <!-- 篩選列 -->
  <form method="GET" action="search.php" class="row g-2 mb-3">
    <div class="col-auto">
      <label class="form-label">農田</label>
      <select name="farm" class="form-select">
        <?php foreach ($farms as $f): ?>
          <option value="<?php echo $f['farmland_id']; ?>" <?php if($f['farmland_id']==$selectedFarm) echo 'selected'; ?>>
            農田 #<?php echo $f['farmland_id']; ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label">年份</label>
      <input type="number" name="year" class="form-control" value="<?php echo $selYear; ?>" style="width:90px">
    </div>
    <div class="col-auto">
      <label class="form-label">月份</label>
      <select name="month" class="form-select">
        <?php for($m=1;$m<=12;$m++): ?>
          <option value="<?php echo $m; ?>" <?php if($m==$selMonth) echo 'selected'; ?>><?php echo $m; ?> 月</option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-auto d-flex align-items-end">
      <button type="submit" class="btn btn-success">查詢</button>
    </div>
  </form>

  <!-- 圖例 -->
  <div class="legend">
    <div><span style="background:#2e7d32;"></span>節能（實際 ≤ 標準 <?php echo $STANDARD; ?> 公升）</div>
    <div><span style="background:#c62828;"></span>超用（實際 > 標準）</div>
    <div><span style="background:#ccc;"></span>無記錄</div>
  </div>

  <!-- 月曆 -->
  <div class="cal-grid mb-4">
    <?php foreach(['一','二','三','四','五','六','日'] as $d): ?>
      <div class="cal-head"><?php echo $d; ?></div>
    <?php endforeach; ?>
    <?php for($i=1; $i<$firstDow; $i++): ?>
      <div class="cal-cell empty"></div>
    <?php endfor; ?>
    <?php for($day=1; $day<=$daysInMonth; $day++):
      $dateKey = sprintf('%04d-%02d-%02d', $selYear, $selMonth, $day);
      $hasData = isset($records[$dateKey]);
      $wd = $hasData ? $records[$dateKey] : null;
      $cls = $hasData ? ('has-data ' . ($wd <= $STANDARD ? 'saving' : 'wasting')) : '';
    ?>
      <div class="cal-cell <?php echo $cls; ?>" title="<?php echo $dateKey; ?>">
        <div class="day-num"><?php echo $day; ?></div>
        <?php if ($hasData): ?>
          <div class="water-val <?php echo $wd<=$STANDARD?'text-success':'text-danger'; ?>">
            <?php echo number_format($wd,1); ?> 公升<br>
            <?php $diff = $STANDARD - $wd; echo ($diff>=0?'<span class="text-success">節省 '.number_format($diff,1).'</span>':'<span class="text-danger">超用 '.number_format(abs($diff),1).'</span>'); ?>
          </div>
        <?php else: ?>
          <div class="water-val text-muted">無記錄</div>
        <?php endif; ?>
      </div>
    <?php endfor; ?>
  </div>

  <!-- 新增耕種記錄 -->
  <div class="card">
    <div class="card-header bg-success text-white fw-bold">➕ 新增耕種記錄</div>
    <div class="card-body">
      <form method="POST" action="search.php?farm=<?php echo $selectedFarm; ?>&year=<?php echo $selYear; ?>&month=<?php echo $selMonth; ?>" class="row g-3">
        <input type="hidden" name="farmland_id" value="<?php echo $selectedFarm; ?>">
        <div class="col-md-3">
          <label class="form-label">日期</label>
          <input type="date" class="form-control" name="record_date" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">用水量（公升）</label>
          <input type="number" step="0.01" class="form-control" name="water_data" required>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button type="submit" name="add_rec" class="btn btn-success">新增記錄</button>
        </div>
      </form>
    </div>
  </div>

  <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
