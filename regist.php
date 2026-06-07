<!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>註冊 — 田地節能用水對比系統</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>body { background:#f1f8e9; }</style>
</head>
<body>
<?php
// 永遠不相信使用者輸入，先驗證再使用
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["regist"])) {
    include "connect.php";
    // 檢查帳號是否已存在
    $sql  = "SELECT u_account FROM users WHERE u_account = :u_account";
    $stmt = $conn->prepare($sql);
    $test = test_input($_POST["u_account"]);
    $stmt->bindParam(":u_account", $test);
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $u_account = test_input($_POST["u_account"]);
        $u_password = test_input($_POST["u_password"]);
        $u_name    = test_input($_POST["u_name"]);
        $u_address = test_input($_POST["u_address"]);
        $u_phone   = test_input($_POST["u_phone"]);
        $sql  = "INSERT INTO users (u_account, u_password, u_name, u_address, u_phone)
                 VALUES (:u_account, :u_password, :u_name, :u_address, :u_phone)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":u_account",  $u_account);
        $stmt->bindParam(":u_password", $u_password);
        $stmt->bindParam(":u_name",     $u_name);
        $stmt->bindParam(":u_address",  $u_address);
        $stmt->bindParam(":u_phone",    $u_phone);
        $result = $stmt->execute();
        if ($result) {
            echo "<script>alert('註冊成功！');location.href='login.php';</script>";
        } else {
            $msg = '<div class="alert alert-danger mt-2">因不明原因，註冊失敗</div>';
        }
        $conn = null;
    } else {
        $msg = '<div class="alert alert-danger mt-2">帳號已存在，請換一個！</div>';
    }
}
?>
<div class="container">
  <div class="row vh-100 justify-content-center align-items-center">
    <div class="card" style="width:24rem;">
      <div class="card-body p-4">
        <h5 class="card-title fw-bold fs-3 mb-3">🌾 會員註冊</h5>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
          <div class="mb-3">
            <label class="form-label">帳號</label>
            <input type="text" class="form-control" name="u_account" required>
          </div>
          <div class="mb-3">
            <label class="form-label">密碼</label>
            <input type="password" class="form-control" name="u_password" required>
          </div>
          <div class="mb-3">
            <label class="form-label">姓名</label>
            <input type="text" class="form-control" name="u_name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">地址</label>
            <input type="text" class="form-control" name="u_address">
          </div>
          <div class="mb-3">
            <label class="form-label">電話</label>
            <input type="text" class="form-control" name="u_phone">
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success flex-fill" name="regist">註冊</button>
            <a href="login.php" class="btn btn-outline-secondary flex-fill">返回登入</a>
          </div>
        </form>
        <?php echo $msg ?? ''; ?>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
