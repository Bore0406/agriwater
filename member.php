<?php
session_start();
include "connect.php";

$u_account  = $_SESSION["u_account"] ?? '';
$loginStatus = !empty($u_account);
$userData   = [];

if ($loginStatus) {
    $sql  = "SELECT * FROM users WHERE u_account = :u_account";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":u_account", $u_account);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

function test_input($d) {
    return htmlspecialchars(stripslashes(trim($d)));
}

// 修改資料
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    $u_name    = test_input($_POST["u_name"]);
    $u_address = test_input($_POST["u_address"]);
    $u_phone   = test_input($_POST["u_phone"]);
    $sql  = "UPDATE users SET u_name=:u_name, u_address=:u_address, u_phone=:u_phone WHERE u_account=:u_account";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":u_name",    $u_name);
    $stmt->bindParam(":u_address", $u_address);
    $stmt->bindParam(":u_phone",   $u_phone);
    $stmt->bindParam(":u_account", $u_account);
    if ($stmt->execute()) {
        header("Location: member.php"); exit();
    }
}
// 刪除帳號
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    $sql  = "DELETE FROM users WHERE u_account=:u_account";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":u_account", $u_account);
    if ($stmt->execute()) {
        session_destroy();
        echo "<script>alert('帳號已刪除');location.href='login.php';</script>";
        exit();
    }
}
?>
<!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>會員中心</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>body{background:#f1f8e9;} .card{box-shadow:0 4px 12px rgba(0,0,0,0.1);border-radius:1rem;border:none;}</style>
</head>
<body>
<?php include('navbar.html'); ?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="text-center mb-4">
        <h2 class="fw-bold">👤 會員中心</h2>
        <?php if ($loginStatus): ?>
          <span class="badge bg-success fs-6">✅ 已登入</span>
        <?php else: ?>
          <span class="badge bg-danger fs-6">⚠ 尚未登入</span>
        <?php endif; ?>
      </div>
      <div class="card p-4">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
          <div class="mb-3">
            <label class="form-label fw-bold">帳號</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($userData['u_account'] ?? ''); ?>" disabled>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">姓名</label>
            <input type="text" class="form-control" name="u_name" value="<?php echo htmlspecialchars($userData['u_name'] ?? ''); ?>" <?php if(!$loginStatus) echo 'disabled'; ?> required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">地址</label>
            <input type="text" class="form-control" name="u_address" value="<?php echo htmlspecialchars($userData['u_address'] ?? ''); ?>" <?php if(!$loginStatus) echo 'disabled'; ?>>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">電話</label>
            <input type="text" class="form-control" name="u_phone" value="<?php echo htmlspecialchars($userData['u_phone'] ?? ''); ?>" <?php if(!$loginStatus) echo 'disabled'; ?>>
          </div>
          <?php if ($loginStatus): ?>
          <div class="d-grid gap-2">
            <button type="submit" name="update" class="btn btn-success">💾 修改資料</button>
            <a href="web.php" class="btn btn-outline-secondary">🏠 回首頁</a>
            <button type="submit" name="delete" class="btn btn-outline-danger" onclick="return confirm('確定要刪除帳號嗎？此操作無法復原！')">🗑 刪除帳號</button>
            <a href="logout.php" class="btn btn-danger">🚪 登出</a>
          </div>
          <?php else: ?>
          <a href="login.php" class="btn btn-success w-100">前往登入</a>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
