<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

// 📁 Generate a safe folder name for image link
function generateFolderName($title)
{
    $safeTitle = preg_replace('/[^a-zA-Z0-9-_]/', '_', strtolower($title));
    return $safeTitle . '_' . time();
}

// 🧼 Sanitize and resize image into 800x600 with white background
function sanitizeAndResizeImage($sourcePath, $targetPath, $maxWidth = 400, $maxHeight = 600)
{
    $imageData = @file_get_contents($sourcePath);
    if ($imageData === false) return false;

    $srcImage = @imagecreatefromstring($imageData);
    if (!$srcImage) return false;

    $origWidth = imagesx($srcImage);
    $origHeight = imagesy($srcImage);

    $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
    $newWidth = (int) ($origWidth * $ratio);
    $newHeight = (int) ($origHeight * $ratio);

    $canvas = imagecreatetruecolor($maxWidth, $maxHeight);
    $white = imagecolorallocate($canvas, 255, 255, 255);
    imagefill($canvas, 0, 0, $white);

    $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($resizedImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

    $dstX = (int) (($maxWidth - $newWidth) / 2);
    $dstY = (int) (($maxHeight - $newHeight) / 2);
    imagecopy($canvas, $resizedImage, $dstX, $dstY, 0, 0, $newWidth, $newHeight);

    $result = imagejpeg($canvas, $targetPath, 85);

    imagedestroy($srcImage);
    imagedestroy($resizedImage);
    imagedestroy($canvas);

    return $result;
}

// 🚫 Reject invalid requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// 🔐 Input validation
$gtin = trim($_POST['productBarcode'] ?? '');
$title = trim($_POST['productTitle'] ?? '');
$desc = trim($_POST['productDescription'] ?? '');
$userId = $_SESSION['admin_id'] ?? 0;
$category = $_POST['productCategory'] ?? '0';
$subcategory = $_POST['productSubCategory'] ?? '0';
$tertiary = $_POST['productTetCategory'] ?? '0';
$brand = $_POST['productBrand'] ?? '0';

if (empty($gtin) || empty($title) || empty($subcategory)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}
if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// 🖼️ Validate and prepare images before inserting product
if (empty($_FILES['img']) || empty($_FILES['img']['name'][0])) {
    echo json_encode(['success' => false, 'message' => 'No image uploaded']);
    exit;
}

$files = $_FILES['img'];
$totalFiles = count($files['name']);
if ($totalFiles > 4) {
    echo json_encode(['success' => false, 'message' => 'Maximum 4 images allowed']);
    exit;
}

$sanitizedImages = [];
for ($i = 0; $i < $totalFiles; $i++) {
    $tmpName = $files['tmp_name'][$i];
    $originalName = $files['name'][$i];

    if (!file_exists($tmpName)) {
        echo json_encode(['success' => false, 'message' => "Temp file missing: $originalName"]);
        exit;
    }

    $mimeType = mime_content_type($tmpName);
    if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'])) {
        echo json_encode(['success' => false, 'message' => "Invalid file type: $originalName"]);
        exit;
    }

    $randomName = bin2hex(random_bytes(8)) . '.jpg';
    $sanitizedImages[] = [
        'tmp' => $tmpName,
        'filename' => $randomName
    ];
}

// 💾 Insert product after images are validated
$productFolder = generateFolderName($title);
$uploadDir = __DIR__ . '/../../uploads/products/' . $productFolder . '/';

try {
    $stmt = $pdo->prepare("CALL product_add(:gtin, :title, :desc, :link, :user,:category, :subcategory, :tertiary, :brand)");
    $stmt->execute([
        ':gtin' => $gtin,
        ':title' => $title,
        ':desc' => $desc,
        ':link' => $productFolder,
        ':user' => $userId,
        ':category' => $category,
        ':subcategory' => $subcategory,
        ':tertiary' => $tertiary,
        ':brand' => $brand
    ]);

    $productResult = $stmt->fetch(PDO::FETCH_ASSOC);
    $product_id = $productResult['product_id'] ?? null;

    if (!$product_id) throw new Exception("Failed to get inserted product ID.");
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

foreach ($sanitizedImages as $i => $img) {
    $targetPath = $uploadDir . $img['filename'];
    $resized = sanitizeAndResizeImage($img['tmp'], $targetPath);
    if (!$resized) {
        echo json_encode(['success' => false, 'message' => "Resize failed for image"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("CALL product_image_add(:pid, :filename, :ord)");
        $stmt->execute([
            ':pid' => $product_id,
            ':filename' => $img['filename'],
            ':ord' => $i + 1
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => "Image DB insert failed: " . $e->getMessage()]);
        exit;
    }
}

echo json_encode(['success' => true, 'message' => '✅ Product and images added successfully']);
