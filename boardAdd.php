<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["btn"])) {
    include "connect.php";
    $m_name    = htmlspecialchars(trim($_POST["m_name"]));
    $m_content = htmlspecialchars(trim($_POST["m_content"]));
    // MS SQL Server 用 GETDATE()
    $sql  = "INSERT INTO messages (m_name, m_content) VALUES (:m_name, :m_content)";
    $exec = $conn->prepare($sql);
    $exec->bindParam(":m_name",    $m_name);
    $exec->bindParam(":m_content", $m_content);
    $result = $exec->execute();
    if ($result) {
        header("Location: board.php");
    } else {
        echo "<script>alert('新增留言失敗');history.back();</script>";
    }
    $conn = null;
}
?>
