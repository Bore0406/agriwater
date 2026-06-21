<?php
session_start();
include "connect.php";

$u_account = $_SESSION["u_account"] ?? '';
$isAdmin   = ($u_account === 'admin');

if (!$isAdmin) {
    http_response_code(403);
    echo "權限不足，僅管理員可刪除留言";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["m_id"])) {
    $m_id = intval($_POST["m_id"]);
    $stmt = $conn->prepare("DELETE FROM messages WHERE m_id = :id");
    $stmt->bindParam(":id", $m_id);
    $stmt->execute();
    echo "ok";
}
$conn = null;
?>
