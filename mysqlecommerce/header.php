<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    include_once 'base-link.php';
    $base = BASE_PATH;
    ?>
    <link rel="stylesheet" href="<?= $base ?>/css/style.css">
    <link rel="stylesheet" href="<?= $base ?>/css/header.css">
    <link rel="stylesheet" href="<?= $base ?>/css/footer.css">
    <script src="<?php echo BASE_PATH . '/js/header.js'; ?>"></script>
    <link rel="icon" href="<?= $base ?>/favicon.ico" type="image/x-icon">
    <?php if (session_status() === PHP_SESSION_NONE) {
        session_start();
    } ?>
    <?php if (isset($page_css) && is_array($page_css)): ?>
        <?php foreach ($page_css as $css): ?>
            <link rel="stylesheet" href="<?= $base ?>/css/<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <title><?php echo htmlspecialchars('Modaway'); ?></title>
</head>
<?php
require_once 'db.php'; // $conn must be a PDO object

$p_user_id = $_SESSION['id'] ?? 0;
$cartCount = 0;


if ($p_user_id > 0) {
    try {
        $stmt = $pdo->prepare("CALL shoppingcart_items_count(:user_id)");
        $stmt->bindParam(':user_id', $p_user_id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result && isset($result['item_count'])) {
            $cartCount = $result['item_count'];
        }

        $stmt->closeCursor(); // required to use the same connection for further queries
    } catch (PDOException $e) {
        // Optional: log or show error
        // echo "Error: " . $e->getMessage();
    }
}
?>


<body>
    <div class="topright-align">
        <a class="topright" href="user-dashboard.php?page=coupons">My Coupons</a>
        <a class="topright" href="/MysqlPhpProject/login.php">Be a Seller</a>
        <a class="topright" href="about-us.php">About Us</a>
        <br>
    </div>
    <nav class="topnav">
        <ul>

            <li><a href="<?= $base ?>/index.php"><img src="<?= $base ?>/images/weblogo.svg" alt="Modaway"></a></li>
            <li class="menubutton" onclick=showSidebar()><a>
                    <svg fill="#000" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                        width="24px">
                        <path d="M120-240v-80h720v80H120Zm0-200v-80h720v80H120Zm0-200v-80h720v80H120Z" />
                    </svg>

                </a>
            </li>
            <li>
                <div class="search-container">
                    <div style="position: relative; width: 300px;">
                        <input type="text" placeholder="Search.." name="search" id="searchInput" autocomplete="off"
                            style="width: 100%;">
                        <button type="button" id="searchBtn">
                            <img src="<?= $base ?>/images/search.svg" alt="Search" style="width: 18px; height: 18px;">
                        </button>
                        <div class="searchResults" id="searchResults"></div>
                    </div>
                </div>

                <script>
                    document.getElementById('searchBtn').addEventListener('click', function () {
                        const query = document.getElementById('searchInput').value.trim();
                        if (query !== '') {
                            // Redirect to product grid with search query
                            window.location.href = '<?= $base ?>/product-grid.php?q=' + encodeURIComponent(query);
                        }
                    });

                    // Optional: press Enter to trigger search
                    document.getElementById('searchInput').addEventListener('keydown', function (e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            document.getElementById('searchBtn').click();
                        }
                    });
                </script>

            </li>
            <li class="account-item">
                <?php if (isset($_SESSION['id'])): ?>
                    <a href="<?= $base ?>/user-dashboard.php?page=all_orders">Hesabım</a>
                <?php else: ?>
                    <a href="<?= $base ?>/login.php">Hesabım</a>
                <?php endif; ?>

                <div class="hover-content">
                    <p>
                        <?php
                        if (isset($_SESSION["mail"])) {
                            $email = htmlspecialchars($_SESSION["mail"]);
                            echo (mb_strlen($email) > 19) ? mb_substr($email, 0, 16) . "..." : $email;
                        } else {
                            echo "Not Available";
                        }
                        ?>
                    </p>
                    <ul>
                        <li><a href="<?= $base ?>/user-dashboard.php?page=all_orders">Hesabım</a></li>
                        <li><a href="<?= $base ?>/tools/action/logout.php">Çıkış</a></li>
                    </ul>
                </div>
            </li>

            <li><a href="<?= $base ?>/favorites.php">Favorilerim</a></li>
            <li class="cart-link">
                <a href="<?= $base ?>/shoping-cart.php">
                    Sepetim
                    <span class="cart-count"><?= $cartCount ?></span>
                </a>
            </li>


        </ul>
    </nav>