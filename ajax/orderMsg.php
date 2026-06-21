<?php
session_start();
include "../connect.php";

$u_account = $_SESSION["u_account"] ?? '';
$isAdmin   = ($u_account === 'admin');

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
        $id       = (int)$r['m_id'];
        $name     = htmlspecialchars($r['m_name']);
        $content  = htmlspecialchars($r['m_content']);
        $date     = htmlspecialchars($r['m_date']);
        $feedback = $r['m_feedback'] ?? '';
        $isAdminPoster = ($r['m_name'] === 'admin');

        echo "<div style='background:white;border-radius:10px;padding:16px 20px;margin-bottom:12px;box-shadow:0 1px 6px rgba(0,0,0,0.08);'>";
        echo "<div style='display:flex;justify-content:space-between;align-items:center;'>";
        echo "<div style='font-weight:bold;font-size:1rem;'>👤 {$name}" .
             ($isAdminPoster ? " <span style='background:#f57c00;color:#fff;font-size:0.7rem;padding:2px 8px;border-radius:10px;'>管理員</span>" : "") .
             "</div>";

        // 僅管理員看得到刪除按鈕
        if ($isAdmin) {
            echo "<button onclick=\"deleteMsg({$id})\" style='background:#e53935;color:#fff;border:none;border-radius:6px;padding:4px 10px;font-size:0.8rem;cursor:pointer;'>🗑 刪除</button>";
        }
        echo "</div>";

        echo "<div style='font-size:0.8rem;color:#999;margin-bottom:6px;'>{$date}</div>";
        echo "<div>{$content}</div>";

        if (!empty($feedback)) {
            echo "<div style='background:#e8f5e9;border-left:4px solid #2e7d32;padding:10px 14px;border-radius:0 8px 8px 0;margin-top:8px;font-size:0.9rem;'>";
            echo "<strong>🌱 管理員回覆：</strong>" . htmlspecialchars($feedback);
            echo "</div>";
        } elseif ($isAdmin) {
            // 管理員專屬：回覆輸入框
            echo "<div style='margin-top:10px;display:flex;gap:6px;'>";
            echo "<input id='fb-input-{$id}' type='text' placeholder='輸入管理員回覆...' style='flex:1;padding:6px 10px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;'>";
            echo "<button onclick=\"submitFeedback({$id})\" style='background:#2e7d32;color:#fff;border:none;border-radius:6px;padding:6px 14px;font-size:0.85rem;cursor:pointer;'>回覆</button>";
            echo "</div>";
        }

        echo "</div>";
    }
}
$conn = null;
?>
