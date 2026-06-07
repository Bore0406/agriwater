<?php
/*
 * connect.php — 資料庫連線設定
 * 適用：MySQL（XAMPP 內建）
 */
try {
    $conn = new PDO("mysql:host=localhost;dbname=agriwater;charset=utf8mb4", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("資料庫連線失敗：" . $e->getMessage());
}
?>
