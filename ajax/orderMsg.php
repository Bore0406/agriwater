<?php
include "../connect.php";

$order = ($_GET["order"] ?? "DESC") === "ASC" ? "ASC" : "DESC";

$sql  = "SELECT m_id, m_name, m_content, m_date, m_feedback
         FROM messages ORDER BY m_date $order";
$stmt = $conn->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo '<div class="text-center text-muted py-4">目前尚無留言，來第一個發言吧！</div>';
} else {
    foreach ($rows as $r) {
        $name     = htmlspecialchars($r['m_name']);
        $content  = htmlspecialchars($r['m_content']);
        $date     = htmlspecialchars($r['m_date']);
        $feedback = $r['m_feedback'] ?? '';
        echo "
<div style='background:white;border-radius:10px;padding:16px 20px;margin-bottom:12px;box-shadow:0 1px 6px rgba(0,0,0,0.08);'>
  <div style='font-weight:bold;font-size:1rem;'>👤 {$name}</div>
  <div style='font-size:0.8rem;color:#999;margin-bottom:6px;'>{$date}</div>
  <div>{$content}</div>";
        if (!empty($feedback)) {
            echo "
  <div style='background:#e8f5e9;border-left:4px solid #2e7d32;padding:10px 14px;border-radius:0 8px 8px 0;margin-top:8px;font-size:0.9rem;'>
    <strong>🌱 管理員回覆：</strong>" . htmlspecialchars($feedback) . "
  </div>";
        }
        echo "</div>";
    }
}
$conn = null;
?>
