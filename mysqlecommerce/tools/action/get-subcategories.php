<?php
include '../../db.php';
function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$cid = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

try {
  $stmt = $pdo->prepare("CALL categorys_show()");
  $stmt->execute();

  $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $subs = [];
  if ($stmt->nextRowset()) {
    $subs = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  $ters = [];
  if ($stmt->nextRowset()) {
    $ters = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  $stmt->closeCursor();

  $subsByCat = [];
  foreach ($subs as $s) {
    if ((int)$s['product_sub_category_status'] !== 1) continue;
    $subsByCat[(int)$s['product_category_id']][] = $s;
  }

  $tersBySub = [];
  foreach ($ters as $t) {
    if ((int)$t['product_tertiary_category_status'] !== 1) continue;
    $tersBySub[(int)$t['product_sub_category_id']][] = $t;
  }

  // Output only the requested category’s markup
  if (!empty($subsByCat[$cid])) {
    echo "<ul>";
    foreach ($subsByCat[$cid] as $s) {
      $sid   = (int)$s['product_sub_category_id'];
      $sname = h($s['product_sub_category_name']);
      $subUrl = "/mysqlecommerce/product-grid.php?" . http_build_query([
        'category' => $cid,
        'sub_category_ids[]' => $sid,
      ]);
      echo "<li class='sub'><a href='".h($subUrl)."'><strong>{$sname}</strong></a>";

      if (!empty($tersBySub[$sid])) {
        echo "<ul class='ter'>";
        foreach ($tersBySub[$sid] as $t) {
          $tid = (int)$t['product_tertiary_category_id'];
          $tname = h($t['product_tertiary_category_name']);
          $terUrl = "/mysqlecommerce/product-grid.php?" . http_build_query([
            'category' => $cid,
            'tertiary_category_ids[]' => $tid,
          ]);
          echo "<li><a href='".h($terUrl)."'>{$tname}</a></li>";
        }
        echo "</ul>";
      }
      echo "</li>";
    }
    echo "</ul>";
  } else {
    $catUrl = "/mysqlecommerce/product-grid.php?" . http_build_query(['category' => $cid]);
    echo "<ul><li class='sub'><a href='".h($catUrl)."'><strong>View all</strong></a></li></ul>";
  }
} catch (PDOException $e) {
  echo "<p>Error loading subcategories</p>";
}
