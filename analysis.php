<?php
session_start();
include "connect.php";
$u_account   = $_SESSION["u_account"] ?? '';
$loginStatus = !empty($u_account);

$u_no = null;
if ($loginStatus) {
    $s = $conn->prepare("SELECT u_no FROM users WHERE u_account=:a");
    $s->bindParam(":a", $u_account); $s->execute();
    $row = $s->fetch(PDO::FETCH_ASSOC);
    $u_no = $row['u_no'] ?? null;
}

$STANDARD = 500; // 每筆記錄理論用水量（公升）

// 各農田節能量統計
$farmStats = [];
if ($u_no) {
    $sql = "SELECT f.farmland_id,
                   SUM(cr.water_data)          AS total_actual,
                   COUNT(cr.record_id) * :std  AS total_expected,
                   COUNT(cr.record_id) * :std2 - SUM(cr.water_data) AS saved
            FROM farmland f
            LEFT JOIN cultivation_record cr ON f.farmland_id = cr.farmland_id
            WHERE f.member_id = :m
            GROUP BY f.farmland_id
            ORDER BY saved DESC";
    $s = $conn->prepare($sql);
    $s->bindParam(":std",  $STANDARD);
    $s->bindParam(":std2", $STANDARD);
    $s->bindParam(":m",    $u_no);
    $s->execute();
    $farmStats = $s->fetchAll(PDO::FETCH_ASSOC);
}

// 趨勢預測：過去 7 天耕種記錄
$trend = [];
if ($u_no) {
    $sql = "SELECT cr.record_date, SUM(cr.water_data) AS daily_total
            FROM cultivation_record cr
            JOIN farmland f ON cr.farmland_id = f.farmland_id
            WHERE f.member_id = :m
              AND cr.record_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY cr.record_date
            ORDER BY cr.record_date";
    $s = $conn->prepare($sql);
    $s->bindParam(":m", $u_no);
    $s->execute();
    $trend = $s->fetchAll(PDO::FETCH_ASSOC);
}

// 計算平均趨勢預測
$avgDaily = 0;
if (!empty($trend)) {
    $avgDaily = array_sum(array_column($trend, 'daily_total')) / count($trend);
}

// JSON for Chart.js
$barLabels  = json_encode(array_map(fn($r) => '農田#'.$r['farmland_id'], $farmStats));
$barSaved   = json_encode(array_map(fn($r) => round(floatval($r['saved']),1), $farmStats));
$barColors  = json_encode(array_map(fn($r) => floatval($r['saved']) >= 0 ? 'rgba(46,125,50,0.7)' : 'rgba(198,40,40,0.7)', $farmStats));

$lineLabels = json_encode(array_column($trend, 'record_date'));
$lineData   = json_encode(array_map(fn($r) => round(floatval($r['daily_total']),1), $trend));
?>
<!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>數據分析 — 田地節能用水對比系統</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { background:#f1f8e9; }
    .stat-card { background:white; border-radius:14px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,0.08); text-align:center; }
    .stat-card .val { font-size:2rem; font-weight:bold; }
    .chart-box { background:white; border-radius:14px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,0.08); margin-bottom:24px; }
  </style>
</head>
<body>
<?php include('navbar.html'); ?>
<div class="container py-4">
  <h2 class="fw-bold text-success mb-4">📊 數據分析中心</h2>

  <?php if (!$loginStatus): ?>
    <div class="alert alert-warning">請先 <a href="login.php">登入</a> 才能查看分析。</div>
  <?php else: ?>

  <!-- 摘要卡片 -->
  <?php
    $totalSaved  = array_sum(array_column($farmStats, 'saved'));
    $totalActual = array_sum(array_column($farmStats, 'total_actual'));
    $totalExp    = array_sum(array_column($farmStats, 'total_expected'));
    $saveRate    = $totalExp > 0 ? round($totalSaved / $totalExp * 100, 1) : 0;
  ?>
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="stat-card">
        <div class="val <?php echo $totalSaved>=0?'text-success':'text-danger'; ?>">
          <?php echo number_format($totalSaved, 1); ?>
        </div>
        <div class="text-muted">總節省水量（公升）</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card">
        <div class="val text-primary"><?php echo number_format($totalActual, 1); ?></div>
        <div class="text-muted">實際總用水量（公升）</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card">
        <div class="val text-secondary"><?php echo number_format($totalExp, 1); ?></div>
        <div class="text-muted">理論標準總量（公升）</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card">
        <div class="val <?php echo $saveRate>=0?'text-success':'text-danger'; ?>">
          <?php echo $saveRate; ?>%
        </div>
        <div class="text-muted">整體節能率</div>
      </div>
    </div>
  </div>

  <!-- 指標一：各農田節能量長條圖 -->
  <div class="chart-box">
    <h5 class="fw-bold text-success mb-3">📊 指標一：各農田節能量排行（正值 = 節能 ✅，負值 = 超用 ❌）</h5>
    <canvas id="barChart" height="100"></canvas>
  </div>

  <!-- 指標二：用水趨勢折線圖 -->
  <div class="chart-box">
    <h5 class="fw-bold text-success mb-3">📈 指標二：過去 7 天用水趨勢 ＆ 預測</h5>
    <?php if (!empty($trend)): ?>
      <p class="text-muted">依過去 7 天平均值預測，下週每日約需 <strong class="text-success"><?php echo number_format($avgDaily, 1); ?> 公升</strong>（氣候相同情況下）。</p>
    <?php else: ?>
      <p class="text-muted">目前無足夠數據進行趨勢預測。</p>
    <?php endif; ?>
    <canvas id="lineChart" height="100"></canvas>
  </div>

  <!-- 農田節能明細表 -->
  <div class="card">
    <div class="card-header bg-success text-white fw-bold">📋 農田節能明細</div>
    <div class="card-body p-0">
      <table class="table table-hover mb-0 text-center">
        <thead class="table-light">
          <tr>
            <th>農田編號</th>
            <th>實際總用水量</th>
            <th>理論標準總量</th>
            <th>節省量</th>
            <th>狀態</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($farmStats)): ?>
          <tr><td colspan="5" class="text-muted py-3">尚無耕種記錄</td></tr>
        <?php else: foreach ($farmStats as $r):
          $saved = floatval($r['saved']);
        ?>
          <tr>
            <td>農田 #<?php echo $r['farmland_id']; ?></td>
            <td><?php echo number_format(floatval($r['total_actual']),1); ?> 公升</td>
            <td><?php echo number_format(floatval($r['total_expected']),1); ?> 公升</td>
            <td class="<?php echo $saved>=0?'text-success fw-bold':'text-danger fw-bold'; ?>">
              <?php echo ($saved>=0?'+':'').number_format($saved,1); ?> 公升
            </td>
            <td>
              <?php echo $saved>=0
                ? '<span class="badge bg-success">✅ 節能</span>'
                : '<span class="badge bg-danger">❌ 超用</span>'; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php endif; ?>
</div>

<script>
// 長條圖
new Chart(document.getElementById('barChart'), {
  type: 'bar',
  data: {
    labels: <?php echo $barLabels; ?>,
    datasets: [{
      label: '節省量（公升）',
      data: <?php echo $barSaved; ?>,
      backgroundColor: <?php echo $barColors; ?>,
      borderRadius: 6,
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: {
      y: { title: { display: true, text: '公升' } },
      x: { title: { display: true, text: '農田' } }
    }
  }
});

// 折線圖
const labels = <?php echo $lineLabels; ?>;
const data   = <?php echo $lineData; ?>;
const avg    = <?php echo round($avgDaily, 1); ?>;

// 加入 7 天預測點
const forecastLabels = [...labels];
const forecastData   = [...data];
for (let i = 1; i <= 7; i++) {
  forecastLabels.push('預測 +' + i + '天');
  forecastData.push(avg);
}

new Chart(document.getElementById('lineChart'), {
  type: 'line',
  data: {
    labels: forecastLabels,
    datasets: [
      {
        label: '實際用水量（公升）',
        data: [...data, ...Array(7).fill(null)],
        borderColor: '#2e7d32',
        backgroundColor: 'rgba(46,125,50,0.1)',
        fill: true,
        tension: 0.3,
      },
      {
        label: '預測用水量（公升）',
        data: [...Array(data.length).fill(null), ...Array(7).fill(avg)],
        borderColor: '#f57c00',
        borderDash: [6, 3],
        tension: 0.3,
      }
    ]
  },
  options: {
    scales: { y: { title: { display: true, text: '公升' } } }
  }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
