<?php
/*
 * connect.php — InfinityFree 版（給同學用）
 * 使用時：把這個檔案內容複製到 connect.php
 */
try {
    $conn = new PDO("mysql:host=sql207.infinityfree.com;dbname=if0_42120496_agriwater;charset=utf8mb4", "if0_42120496", "Bore126977026");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("資料庫連線失敗：" . $e->getMessage());
}
?>