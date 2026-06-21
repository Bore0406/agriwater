<?php
session_start();
include "connect.php";

$u_account = $_SESSION["u_account"] ?? '';
$isAdmin   = ($u_account === 'admin');

if (!$isAdmin) {
    http_response_code(403);
    echo "權限不足，僅管理員可回覆留言";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["m_id"])) {
    $m_id       = intval($_POST["m_id"]);
    $m_feedback = htmlspecialchars(trim($_POST["m_feedback"] ?? ''));
    $stmt = $conn->prepare("UPDATE messages SET m_feedback = :fb WHERE m_id = :id");
    $stmt->bindParam(":fb", $m_feedback);
    $stmt->bindParam(":id", $m_id);
    $stmt->execute();
    echo "ok";
}
$conn = null;
?>
