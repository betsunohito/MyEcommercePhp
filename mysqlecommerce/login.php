<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (isset($_SESSION["id"])) {
  header("Location: index.php");
  exit;
}
$page_css = ["login.css"];
$page_js = ["login.js"];
include 'header.php';

$formData = $_SESSION['formData'] ?? [
  'email' => '',
  'regulations' => '',
  'LoginFailError' => '',
  'errors' => []
];
unset($_SESSION['formData']);

$activeTab = $activeTab = $_SESSION['activeTab'] ?? 'login'; // Default to 'login'
unset($_SESSION['activeTab']);

?>
<script>
  var activeTab = "<?= htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8') ?>"; // Embed PHP value safely
</script>

<div class="body-login">
  <h2>Merhaba,</h2>
  <p>Modaway'e giriş yap veya hesap oluştur, indirimleri kaçırma!</p>

  <div class="login-container">
    <!-- Tabs -->
    <div class="tabs">
      <button class="tab active" data-tab="login" aria-selected="true">Giriş Yap</button>
      <button class="tab" data-tab="signup" aria-selected="false">Üye Ol</button>
    </div>

    <!-- Forms Wrapper -->
    <div class="forms">
      <!-- Login Form -->
      <form id="login-form" method="POST" action="tools\action\login-action.php" class="form active">
        <label for="email">E-Posta</label>
        <input type="email" id="email" name="email" placeholder="E-Posta" required autocomplete="email">
        <small class="error"><?= $formData['LoginFailError'] ?? '' ?></small>

        <label for="password">Şifre</label>
        <input type="password" id="password" name="password" placeholder="Şifre" required
          autocomplete="current-password">

        <a href="#" class="forgot-password">Şifremi Unuttum</a>
        <button type="submit" class="login-button">GİRİŞ YAP</button>
      </form>

      <!-- Signup Form (Initially Hidden) -->
      <form id="signup-form" method="POST" action="tools/action/signup-action.php" class="form">

        <label for="signup-email">E-Posta</label>
        <input type="email" id="signup-email" name="email" placeholder="E-Posta" required autocomplete="email">
        <small class="error"><?= $formData['errors']['email'] ?? '' ?></small>

        <label for="signup-password">Şifre</label>
        <input type="password" id="signup-password" name="password" placeholder="Şifre" required
          autocomplete="new-password">
        <small class="error"><?= $formData['errors']['password'] ?? '' ?></small>

        <div class="checkbox-container">
          <input type="checkbox" id="regulations" name="regulations" required>
          <label for="regulations">Kuralları ve şartları kabul ediyorum</label>
          <small class="error"><?= $formData['errors']['regulations'] ?? '' ?></small>
        </div>

        <button type="submit" class="signup-button">ÜYE OL</button>
      </form>
    </div>
  </div>
</div>

<!-- Style (Fixes Layout Issue) -->
<style>
  .form {
    display: none;
  }

  .form.active {
    display: block;
  }

  .tab.active {
    font-weight: bold;
  }
</style>




<?php
include 'footer.php';
?>