<?php
session_start();
include "connect.php";
$u_account = $_SESSION["u_account"] ?? '';
$loginStatus = !empty($u_account);

// 取得目前登入的 member_id（對應 users 轉 member 的邏輯；這裡用 u_no 作為 member_id）
$u_no = null;
if ($loginStatus) {
    $s = $conn->prepare("SELECT u_no FROM users WHERE u_account = :a");
    $s->bindParam(":a", $u_account);
    $s->execute();
    $row = $s->fetch(PDO::FETCH_ASSOC);
    $u_no = $row['u_no'] ?? null;
}

function ti($d){ return htmlspecialchars(stripslashes(trim($d))); }

// 新增農田
if ($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["add_farm"]) && $u_no) {
    $sql = "INSERT INTO farmland (actual_water_usage, is_energy_saving, member_id)
            VALUES (:w, :e, :m)";
    $stmt = $conn->prepare($sql);
    $w = floatval($_POST["actual_water_usage"]);
    $e = isset($_POST["is_energy_saving"]) ? 1 : 0;
    $stmt->bindParam(":w", $w);
    $stmt->bindParam(":e", $e);
    $stmt->bindParam(":m", $u_no);
    $stmt->execute();

    // 新增農作物（若有填寫）
    if (!empty(trim($_POST["crop_desc"]))) {
        $fid_sql = "SELECT farmland_id FROM farmland WHERE member_id=:m ORDER BY farmland_id DESC LIMIT 1";
        $fs = $conn->prepare($fid_sql);
        $fs->bindParam(":m", $u_no);
        $fs->execute();
        $frow = $fs->fetch(PDO::FETCH_ASSOC);
        $fid  = $frow['farmland_id'];
        $desc = ti($_POST["crop_desc"]);
        $csql = "INSERT INTO crop (farmland_id, sequence_no, description)
                 SELECT :fid, IFNULL(MAX(sequence_no),0)+1, :desc FROM crop WHERE farmland_id=:fid2";
        $cs = $conn->prepare($csql);
        $cs->bindParam(":fid",  $fid);
        $cs->bindParam(":fid2", $fid);
        $cs->bindParam(":desc", $desc);
        $cs->execute();
    }
    header("Location: farmland.php"); exit();
}

// 刪除農田
if ($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["del_farm"])) {
    $fid = intval($_POST["farmland_id"]);
    $stmt = $conn->prepare("DELETE FROM farmland WHERE farmland_id=:f AND member_id=:m");
    $stmt->bindParam(":f", $fid);
    $stmt->bindParam(":m", $u_no);
    $stmt->execute();
    header("Location: farmland.php"); exit();
}

// 修改農田（用水量 / 節能狀態）
if ($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["edit_farm"])) {
    $fid = intval($_POST["farmland_id"]);
    $w   = floatval($_POST["actual_water_usage"]);
    $e   = isset($_POST["is_energy_saving"]) ? 1 : 0;
    $stmt = $conn->prepare("UPDATE farmland SET actual_water_usage=:w, is_energy_saving=:e WHERE farmland_id=:f AND member_id=:m");
    $stmt->bindParam(":w", $w); $stmt->bindParam(":e", $e);
    $stmt->bindParam(":f", $fid); $stmt->bindParam(":m", $u_no);
    $stmt->execute();
    header("Location: farmland.php"); exit();
}

// 查詢農田清單
$farms = [];
if ($u_no) {
    $sql = "SELECT f.farmland_id, f.actual_water_usage, f.is_energy_saving,
                   (SELECT GROUP_CONCAT(description SEPARATOR '、') FROM crop WHERE farmland_id=f.farmland_id) AS crops
            FROM farmland f WHERE f.member_id=:m ORDER BY f.farmland_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":m", $u_no);
    $stmt->execute();
    $farms = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>農田管理 — 典藏系統</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>body{background:#f1f8e9;} th,td{text-align:center;vertical-align:middle;}</style>
</head>
<body>
<?php include('navbar.html'); ?>
<div class="container py-4">
  <h2 class="fw-bold text-success mb-4">🌾 農田管理（典藏系統）</h2>

  <?php if (!$loginStatus): ?>
    <div class="alert alert-warning">請先 <a href="login.php">登入</a> 才能管理農田。</div>
  <?php else: ?>

  <!-- 新增農田表單 -->
  <div class="card mb-4">
    <div class="card-header bg-success text-white fw-bold">➕ 新增農田</div>
    <div class="card-body">
      <form method="POST" action="farmland.php" class="row g-3">
        <div class="col-md-3">
          <label class="form-label">實際用水量（公升）</label>
          <input type="number" step="0.01" class="form-control" name="actual_water_usage" value="0" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">農作物說明（選填）</label>
          <input type="text" class="form-control" name="crop_desc" placeholder="例：水稻 — 台梗九號">
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_energy_saving" id="esCheck">
            <label class="form-check-label" for="esCheck">是否節能用水</label>
          </div>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button type="submit" name="add_farm" class="btn btn-success w-100">新增</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 農田列表 -->
  <div class="card">
    <div class="card-header bg-success text-white fw-bold">📋 我的農田清單</div>
    <div class="card-body p-0">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>農田編號</th>
            <th>實際用水量（公升）</th>
            <th>是否節能</th>
            <th>農作物</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($farms)): ?>
          <tr><td colspan="5" class="text-muted py-4">尚無農田資料，請新增。</td></tr>
        <?php else: foreach ($farms as $f): ?>
          <tr>
            <td><?php echo $f['farmland_id']; ?></td>
            <td>
              <!-- 行內修改用水量 -->
              <form method="POST" action="farmland.php" class="d-flex gap-1 justify-content-center">
                <input type="hidden" name="farmland_id" value="<?php echo $f['farmland_id']; ?>">
                <input type="number" step="0.01" class="form-control form-control-sm" style="width:100px"
                       name="actual_water_usage" value="<?php echo $f['actual_water_usage']; ?>">
                <input type="hidden" name="is_energy_saving" value="<?php echo $f['is_energy_saving']; ?>">
                <button type="submit" name="edit_farm" class="btn btn-sm btn-outline-primary">更新</button>
              </form>
            </td>
            <td>
              <?php echo $f['is_energy_saving'] ? '<span class="badge bg-success">✅ 節能</span>' : '<span class="badge bg-secondary">❌ 未節能</span>'; ?>
            </td>
            <td><?php echo htmlspecialchars($f['crops'] ?? '（無作物）'); ?></td>
            <td>
              <form method="POST" action="farmland.php" onsubmit="return confirm('確定刪除此農田及其所有記錄？')">
                <input type="hidden" name="farmland_id" value="<?php echo $f['farmland_id']; ?>">
                <button type="submit" name="del_farm" class="btn btn-sm btn-danger">刪除</button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>