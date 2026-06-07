<?php session_start(); ?>
<!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>留言板 — 田地節能用水對比系統</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f1f8e9; }
    .msg-card { background:white; border-radius:10px; padding:16px 20px; margin-bottom:12px;
                box-shadow:0 1px 6px rgba(0,0,0,0.08); }
    .msg-card .meta { font-size:0.8rem; color:#999; }
    .feedback-box { background:#e8f5e9; border-left:4px solid #2e7d32; padding:10px 14px;
                    border-radius:0 8px 8px 0; margin-top:8px; font-size:0.9rem; }
  </style>
</head>
<body onload="loadMsgs()">
<?php include('navbar.html'); ?>
<div class="container py-4">
  <h2 class="fw-bold text-success mb-4">💬 留言板（客服系統）</h2>

  <!-- 新增留言表單 -->
  <div class="card mb-4">
    <div class="card-header bg-success text-white fw-bold">✏ 發表留言</div>
    <div class="card-body">
      <form method="POST" action="boardAdd.php">
        <div class="mb-2">
          <input class="form-control" name="m_name" placeholder="您的暱稱" maxlength="30" required>
        </div>
        <div class="mb-2">
          <textarea class="form-control" name="m_content" rows="3" placeholder="有任何問題或申請新增作物，請留言..." required style="resize:none"></textarea>
        </div>
        <div class="d-flex gap-2 align-items-center">
          <button type="submit" name="btn" class="btn btn-success">送出留言</button>
          <div class="btn-group">
            <input type="radio" class="btn-check" name="dummy" id="r1" value="DESC" onclick="loadMsgs('DESC')" checked autocomplete="off">
            <label class="btn btn-sm btn-outline-secondary" for="r1">新→舊</label>
            <input type="radio" class="btn-check" name="dummy" id="r2" value="ASC" onclick="loadMsgs('ASC')" autocomplete="off">
            <label class="btn btn-sm btn-outline-secondary" for="r2">舊→新</label>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- 留言列表（透過 AJAX 從 ajax/orderMsg.php 載入） -->
  <div id="msgList">
    <div class="text-center text-muted py-4">載入中...</div>
  </div>
</div>

<script>
function loadMsgs(order) {
  order = order || 'DESC';
  const list = document.getElementById('msgList');
  list.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-success"></div></div>';
  fetch('ajax/orderMsg.php?order=' + order)
    .then(r => r.text())
    .then(html => { list.innerHTML = html; })
    .catch(() => { list.innerHTML = '<div class="alert alert-danger">載入失敗</div>'; });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
