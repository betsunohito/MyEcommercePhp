<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db.php'; // yolu gerekirse düzeltin

$user_id = $_SESSION['id'] ?? 0;
if (!$user_id) { header('Location: /Index.php'); exit; }

/* --- Aktif sekme tespiti (dashboard routing) --- */
$page = $_GET['page'] ?? 'reviews_history';
$current_tab = ($page === 'reviews') ? 'pending' : 'done'; // reviews = bekleyenler, reviews_history = değerlendirilenler

/* --- Verileri çek (DEĞERLENDİRDİKLERİM) --- */
$stmt = $pdo->prepare("CALL reviews_get_user(:user_id)");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();
?>
<div class="fullscreen-review">
<div class="review-tabs">
        <a class="tab" href="user-dashboard.php?page=reviews-make">🕒 Değerlendirme Bekleyenler</a>
        <a class="tab active" href="user-dashboard.php?page=reviews-show">✅ Değerlendirdiklerim</a>
    </div>


  <div class="review-content" id="done">
    <?php if (empty($reviews)): ?>
      <p>Henüz bir değerlendirme yapmadınız.</p>
    <?php else: ?>
      <?php foreach ($reviews as $r): ?>
        <div class="review-card">
          <h3><?= htmlspecialchars($r['product_full_name']) ?></h3>
          <p>Değerlendirme: <?= (int)$r['rating'] ?>/5 ⭐</p>
          <?php if (!empty($r['comment'])): ?>
            <p><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
          <?php endif; ?>
          <small><?= !empty($r['created_at']) ? date("d F Y H:i", strtotime($r['created_at'])) : '' ?></small>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
