<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db.php'; // yolu gerekirse düzeltin

$user_id = $_SESSION['id'] ?? 0;

$activeCoupons = [];
try {
    $stmt = $pdo->prepare("CALL coupon_available($user_id)");
    $stmt->execute();
    $activeCoupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
} catch (PDOException $e) {
    error_log("❌ Error fetching coupons: " . $e->getMessage());
}

// Helpers
function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function formatMoneyTRY($amount) {
    // Always show 2 decimals, use Turkish thousands separators
    return number_format((float)$amount, 2, ',', '.');
}
function formatDate($dateStr) {
    if (empty($dateStr)) return '∞';
    try {
        $dt = new DateTime($dateStr);
        // Example: 15 Ağustos 2025
        return $dt->format('d M Y');
    } catch (Exception $e) {
        return h($dateStr);
    }
}
?>


<div class="coupon-list-section">
  <h2 class="coupon-list-title">
    🎁 Open Coupons
    <span class="badge-count"><?php echo count($activeCoupons); ?></span>
  </h2>

  <div class="coupon-list">
    <?php if (empty($activeCoupons)): ?>
      <div class="empty-state">
        No active coupons right now. Check back soon!
      </div>
    <?php else: ?>
      <?php foreach ($activeCoupons as $coupon): ?>
        <?php
          $code   = h($coupon['coupon_code']);
          $amount = formatMoneyTRY($coupon['discount_amount'] ?? 0);
          $targetType = !empty($coupon['target_type']) ? ucfirst($coupon['target_type']) : '';
          $targetName = $coupon['target_name'] ?? '';
          $endDate    = formatDate($coupon['end_date'] ?? '');
          $userLabel  = !empty($coupon['user_id']) ? 'User #'.(int)$coupon['user_id'] : 'All Users';
        ?>
        <div class="coupon-card-list">
          <div class="coupon-inner">
            <div class="coupon-header">
              <div class="coupon-code" data-code="<?php echo $code; ?>">
                <?php echo $code; ?>
              </div>
              <div class="coupon-header-actions" style="display:flex; gap:8px; align-items:center;">
                <button class="copy-btn" onclick="copyCoupon('<?php echo $code; ?>', this)">Copy</button>
                <span class="coupon-amount">₺<?php echo $amount; ?> OFF</span>
              </div>
            </div>

            <div class="coupon-details">
              <?php if ($targetType && $targetName): ?>
                <p class="field">
                  <span class="badge-target" title="Target">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 8v4l3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <strong><?php echo h($targetType); ?>:</strong>&nbsp;<?php echo h($targetName); ?>
                  </span>
                </p>
              <?php endif; ?>

              <p class="field">
                <span class="badge-expiry" title="Valid Until">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 0 0 2-2V7H3v12a2 2 0 0 0 2 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                  Valid Until: <?php echo h($endDate); ?>
                </span>
              </p>

              <p class="field">
                <span class="badge-user" title="Assigned To">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 21a8 8 0 1 0-16 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/></svg>
                  Assigned To: <?php echo h($userLabel); ?>
                </span>
              </p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script>
function copyCoupon(code, btn) {
  if (!navigator.clipboard) {
    // Fallback
    const ta = document.createElement('textarea');
    ta.value = code;
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
  } else {
    navigator.clipboard.writeText(code);
  }
  const original = btn.textContent;
  btn.textContent = 'Copied!';
  btn.disabled = true;
  setTimeout(() => { btn.textContent = original; btn.disabled = false; }, 1200);
}
</script>
