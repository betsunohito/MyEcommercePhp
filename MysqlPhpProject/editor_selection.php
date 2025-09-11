<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$page_css = ['editor-select.css', 'footer.css'];
$page_js = ['toast.js', 'editor_select.js'];
include 'header.php';

$admin_id = $_SESSION['admin_id'];
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0
    ? (int) $_GET['page']
    : 1;
try {
    require_once __DIR__ . '../db.php';
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);



    $stmt = $pdo->prepare("CALL product_editor_selections_full(:page)");
    $stmt->bindValue(':page', $page, PDO::PARAM_INT);
    $stmt->execute();

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
<div class="editor-container">
    <h2 class="editor-heading">📝 All Products (Editor Selection)</h2>

    <table class="editor-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Editor Pick</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $index => $product): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td>
                        <a href="product-detail.php?link=<?= htmlspecialchars($product['product_link']) ?>" target="_blank">
                            <?= htmlspecialchars($product['product_name']) ?>
                        </a>
                    </td>
                    <td><?= $product['is_editor_selected'] ?></td>
                    <td><?= $product['editor_selected_at'] ? date('Y-m-d H:i', strtotime($product['editor_selected_at'])) : '—' ?>
                    </td>
                    <td>
                        <input type="checkbox" onclick="toggleEditorSelection(<?= $product['product_id'] ?>, this.checked)"
                            <?= $product['is_editor_selected'] ? 'checked' : '' ?>>

                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>">« Prev</a>
        <?php endif; ?>
        <a href="?page=<?= $page + 1 ?>">Next »</a>
    </div>

</div>




<?php include 'footer.php'; ?>