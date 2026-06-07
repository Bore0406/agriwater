<?php
session_start();
$_SESSION['u_account'] = '';
?>
<!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>登入 — 田地節能用水對比系統</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f1f8e9; font-family:'Segoe UI',sans-serif; }
    .card { border:none; box-shadow:0 4px 16px rgba(0,0,0,0.12); border-radius:1rem; }
  </style>
</head>
<body>
<?php
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    $fc = empty($_POST["account"]);
    $fp = empty($_POST["password"]);
    if ($fc || $fp) {
        $errMsg = '<div class="alert alert-danger">未輸入帳號或密碼！</div>';
    } else {
        $account  = test_input($_POST["account"]);
        $password = test_input($_POST["password"]);
        include "connect.php";
        $sql  = "SELECT u_password FROM users WHERE u_account = :u_account";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":u_account", $account);
        $stmt->execute();
        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($password === $row["u_password"]) {
                $_SESSION["u_account"] = $account;
                header("Location: member.php");
                exit();
            } else {
                $errMsg = '<div class="alert alert-danger fw-bold">帳號或密碼錯誤，請重新輸入！</div>';
            }
        } else {
            $errMsg = '<div class="alert alert-danger fw-bold">帳號或密碼錯誤，請重新輸入！</div>';
        }
        $conn = null; $stmt = null;
    }
}
?>
<div class="container">
  <div class="row vh-100 align-items-center justify-content-center">
    <div class="card mb-3" style="max-width:520px;">
      <div class="row g-0">
        <div class="col-md-4 d-flex flex-column align-items-center justify-content-center p-3 bg-success rounded-start" style="background:#2e7d32!important;">
          <div style="font-size:3.5rem;">🌾</div>
          <p class="text-white text-center mt-2" style="font-size:0.85rem;">田地節能<br>用水對比系統</p>
          <a href="regist.php" class="text-white text-decoration-underline mt-2" style="font-size:0.8rem;">沒有帳號？點我註冊</a>
        </div>
        <div class="col-md-8">
          <div class="card-body p-4">
            <h5 class="card-title fw-bold fs-4 mb-3">會員登入</h5>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
              <div class="form-floating mb-3">
                <input type="text" class="form-control" id="acc" name="account" placeholder="帳號" required>
                <label for="acc">帳號</label>
              </div>
              <div class="form-floating mb-3">
                <input type="password" class="form-control" id="pwd" name="password" placeholder="密碼" required>
                <label for="pwd">密碼</label>
              </div>
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success flex-fill" name="submit">登入</button>
                <a href="web.php" class="btn btn-outline-secondary flex-fill">回首頁</a>
              </div>
            </form>
            <?php echo $errMsg ?? ''; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
