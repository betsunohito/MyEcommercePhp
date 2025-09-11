<?php
session_start();

// Check if admin is logged in by verifying the session variable
if (!isset($_SESSION['admin_id'])) {
  // Not logged in, redirect to login page
  header("Location: login.php");
  exit();
}
$page_js = ["shipment.js"];
$page_css = ["shipment.css"];
// If logged in, you can safely include your header and rest of the page
include 'header.php';

$admin_id = $_SESSION['admin_id'];
?>
<?php
include 'db.php'; // Make sure this defines $pdo
$waitingCount = $inTransitCount = $completedCount = 0;

try {
  $stmt = $pdo->prepare("CALL seller_order_counts(:seller_id)");
  $stmt->bindParam(':seller_id', $admin_id, PDO::PARAM_INT);
  $stmt->execute();

  $seller = $stmt->fetch(PDO::FETCH_ASSOC); // We expect only one row now
  $stmt->closeCursor();

  if ($seller) {
    $waitingCount = $seller['waiting_count'];
    $inTransitCount = $seller['shipped_count'];
    $completedCount = $seller['completed_count'];
  }
} catch (PDOException $e) {
  error_log("Error fetching order counts: " . $e->getMessage());
}

?>
<script>
  const waitingCount = <?= json_encode($waitingCount) ?>;
  const inTransitCount = <?= json_encode($inTransitCount) ?>;
  const completedCount = <?= json_encode($completedCount) ?>;
</script>

<div class="shipment-section">
  <div class="shipment-status-container">

    <a href="shipment-pages/waiting-shipment.php" class="shipment-box waiting">
      <div class="status-icon">🕒</div>
      <h4>Waiting for Shipment</h4>
      <p><?= $waitingCount ?> orders</p>
      <p>Your order is being prepared.</p>
    </a>

    <a href="shipment-pages/on-it-is-way-shipment.php" class="shipment-box in-transit">
      <div class="status-icon">🚚</div>
      <h4>Shipped, Not Received</h4>
      <p><?= $inTransitCount ?> orders</p>
      <p>On its way to the customer.</p>
    </a>

    <a href="shipment-pages/complete-shipment.php" class="shipment-box completed">
      <div class="status-icon">✅</div>
      <h4>Shipment Complete</h4>
      <p><?= $completedCount ?> orders</p>
      <p>Delivered to the customer.</p>
    </a>

  </div>

</div>

<!-- Shipment Search Section -->
<div class="shipment-search-wrapper">

  <!-- Left: Input and button for searching a shipment -->
  <div class="shipment-search-left">
    <label for="order-id-input" class="search-label">
      🔎 Search Your Shipment
    </label>

    <!-- Input box for the user to enter their order ID -->
    <input 
      type="text" 
      id="order-id-input" 
      placeholder="Type your Order ID here..." 
      aria-label="Order ID"
      required
    >

    <!-- Button that initiates the search -->
    <button id="search-shipment-btn">Find Shipment</button>

    <p class="search-hint">You can search by entering the order number from your order list.</p>
  </div>

  <!-- Right: The results will appear here -->
  <div class="shipment-search-right" id="shipment-search-result">
    <!-- Search results will be shown here dynamically -->
  </div>

</div>



<?php include 'footer.php'; ?>