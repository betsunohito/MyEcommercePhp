<div class="select-navigation">
  <!-- FIRST LIST: sidebar (unchanged) -->
  <ul class="sidebar">
    <li onclick="hideSidebar()">
      <a>
        <svg fill="#000" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px">
          <path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z" />
        </svg>
      </a>
    </li>
    <?php
      include 'db.php';  // $pdo (PDO)
      function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

      try {
        $stmt = $pdo->query("CALL categorys_show()");
        $categories_sidebar = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->nextRowset(); // ignore subs
        $stmt->nextRowset(); // ignore tertiary
        $stmt->closeCursor();

        foreach ($categories_sidebar as $row) {
          echo "<li><a id='" . h($row["category_id"]) . "' href='#'>" . h($row["category_name"]) . "</a></li>";
        }
      } catch (PDOException $e) {
        echo "<li>Error loading categories</li>";
      }
    ?>
  </ul>

  <!-- SECOND LIST: top-level tabs -->
  <ul class="hover-menu">
    <?php
      try {
        // Fetch only categories for the tabs (no subs/ters here)
        $stmt = $pdo->query("CALL categorys_show()");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // advance result sets and close (we won't use them here)
        $stmt->nextRowset(); // subs (ignored)
        $stmt->nextRowset(); // ters (ignored)
        $stmt->closeCursor();

        foreach ($categories as $row) {
          $cid   = (int)$row['category_id'];
          $cname = h($row['category_name']);
          $catUrl = "/mysqlecommerce/product-grid.php?" . http_build_query(['category' => $cid]);

          // data-cid is enough for AJAX
          echo "<li class='hover-item hideOnMobile' data-cid='{$cid}'>
                  <a id='cat_{$cid}' href='" . h($catUrl) . "'>{$cname}</a>
                </li>";
        }
      } catch (PDOException $e) {
        echo "<li>Error loading categories</li>";
      }
    ?>
  </ul>

  <!-- ONE SHARED FLYOUT: starts empty, filled on hover -->
  <div class="flyout" role="menu" aria-label="Category menu"></div>
</div>

<script>
(function () {
  const nav = document.querySelector('.select-navigation');
  if (!nav) return;

  const flyout = nav.querySelector('.flyout');
  const links  = nav.querySelectorAll('.hover-menu > li.hover-item > a');

  let lastRequestedCid = null;
  let controller = null;
  let closeTimer = null;
  let openTimer = null; // <-- new timer for delayed opening

  const ENDPOINT = "tools/action/get-subcategories.php";

  async function loadCategory(cid) {
    if (controller) controller.abort();
    controller = new AbortController();

    lastRequestedCid = cid;
    flyout.innerHTML = '<div style="padding:10px">Loading…</div>';

    try {
      const res = await fetch(ENDPOINT + "?category_id=" + encodeURIComponent(cid), {
        signal: controller.signal,
        headers: { "X-Requested-With": "XMLHttpRequest" }
      });
      const html = await res.text();
      if (cid === lastRequestedCid) {
        flyout.innerHTML = html || '<div style="padding:10px">No items</div>';
      }
    } catch (err) {
      if (err.name === 'AbortError') return;
      flyout.innerHTML = '<div style="padding:10px;color:#b91c1c">Error loading</div>';
      console.error(err);
    }
  }

  function openFlyout(cid) {
    if (!cid) return;
    clearTimeout(closeTimer);
    nav.classList.add('open');
    loadCategory(cid);
  }

  function scheduleClose() {
    clearTimeout(closeTimer);
    closeTimer = setTimeout(() => nav.classList.remove('open'), 120);
    clearTimeout(openTimer); // cancel pending opens when leaving
  }

  links.forEach(a => {
    const li  = a.closest('li.hover-item');
    const cid = li && li.getAttribute('data-cid');

    a.addEventListener('mouseenter', () => {
      clearTimeout(openTimer);
      openTimer = setTimeout(() => openFlyout(cid), 500); // 1 sec delay
    });

    a.addEventListener('mouseleave', () => {
      clearTimeout(openTimer); // cancel if user leaves early
    });

    a.addEventListener('focusin', () => openFlyout(cid)); // keyboard focus opens instantly
  });

  nav.addEventListener('mouseleave', scheduleClose);
  flyout.addEventListener('mouseenter', () => clearTimeout(closeTimer));
  flyout.addEventListener('mouseleave', scheduleClose);
})();

</script>
