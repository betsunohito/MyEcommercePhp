<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$page_css = ["user-dashboard.css"];
$page_js = ["toast.js","user-dashboard.js"];
include 'header.php';
?>


<div class="user-dashboard">
  <?php
  $current_page = basename($_SERVER['PHP_SELF']);
  $current_query = $_GET['page'] ?? '';
  ?>

  <div class="sidebar-panel">
    <div class="sidebar-menu">

      <!-- Orders Section -->
      <div class="sidebar-section-title">📦 Siparişler</div>
      <a href="user-dashboard.php?page=all_orders" class="sidebar-link <?php if ($current_page === 'user-dashboard.php' && $current_query === 'all_orders')
        echo 'active'; ?>">
        🛍️ Tüm Siparişler
      </a>

      <a href="user-dashboard.php?page=waiting_orders" class="sidebar-link <?php if ($current_page === 'user-dashboard.php' && $current_query === 'waiting_orders')
        echo 'active'; ?>">
        ⏳ Hazırlanıyor
      </a>

      <a href="user-dashboard.php?page=shipped_orders" class="sidebar-link <?php if ($current_page === 'user-dashboard.php' && $current_query === 'shipped_orders')
        echo 'active'; ?>">
        📦 Kargoda
      </a>

      <a href="user-dashboard.php?page=delivered_orders" class="sidebar-link <?php if ($current_page === 'user-dashboard.php' && $current_query === 'delivered_orders')
        echo 'active'; ?>">
        ✅ Teslim
      </a>

      <!-- Reviews Section -->
      <div class="sidebar-section-title">⭐ Değerlendirmeler</div>
      <a href="user-dashboard.php?page=reviews-make" class="sidebar-link <?php if ($current_page === 'user-dashboard.php' && $current_query === 'reviews-make')
        echo 'active'; ?>">
        📝 Yapılacaklar
      </a>
      <a href="user-dashboard.php?page=reviews-show" class="sidebar-link <?php if ($current_page === 'user-dashboard.php' && $current_query === 'reviews-show')
        echo 'active'; ?>">
        📜 Geçmiş Değerlendirmeler
      </a>

      <!-- Communication Section -->
      <div class="sidebar-section-title">🎟️ Kuponlar</div>

      <a href="user-dashboard.php?page=coupons"
        class="sidebar-link <?php if ($current_page === 'user-dashboard.php' && $current_query === 'coupons')
          echo 'active'; ?>">
        🎟️ Tüm Kuponlar
      </a>


      <!-- Account Section -->
      <div class="sidebar-section-title">⚙️ Hesap</div>
      <a href="user-dashboard.php?page=account" class="sidebar-link <?php if ($current_page === 'user-dashboard.php' && $current_query === 'account')
        echo 'active'; ?>">
        👤 Hesap Bilgileri
      </a>
      <a href="tools/action/logout.php" class="sidebar-link">🔑 Çıkış Yap</a>

    </div>
  </div>


  <!-- content-of-panel continues here... -->



  <div class="content-of-panel">
    <?php
    if (isset($_GET['page'])) {
      $page = $_GET['page'];
      $file = "tools/dashboard-pages/$page.php";

      // Basic security check to prevent directory traversal
      if (preg_match('/^[a-zA-Z0-9_-]+$/', $page) && file_exists($file)) {
        include($file);
      } else {
        echo "<p>Sayfa bulunamadı.</p>";
      }
    }
    ?>
  </div>
</div>


<?php
include 'footer.php';
?>