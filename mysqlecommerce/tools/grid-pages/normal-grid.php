<?php
// grid-pages/normal-grid.php
?>
<div class="pg-wrapper">
  <div class="pg-layout">
    <aside class="pg-sidebar">
      <h2>Filters</h2>

      <form method="get" id="filter-form">
        <input type="hidden" name="page" value="<?= htmlspecialchars($page_number) ?>">
        <?php if (!empty($category_id)): ?>
          <input type="hidden" name="category" value="<?= htmlspecialchars($category_id) ?>">
        <?php endif; ?>

        <!-- Sub-Category -->
        <div class="pg-filter-group">
          <strong>Sub-Category</strong>
          <?php foreach ($subcategories as $sub): ?>
            <label>
              <input type="checkbox" name="sub_category_ids[]" value="<?= (int) $sub['sub_category_id'] ?>"
                <?= in_array($sub['sub_category_id'], (array) $selected_subs) ? 'checked' : '' ?>>
              <?= htmlspecialchars($sub['sub_category_name']) ?>
            </label>
          <?php endforeach; ?>
        </div>

        <!-- Tertiary -->
        <div class="pg-filter-group">
          <strong>Tertiary</strong>
          <?php foreach ($tertiaries as $ter): ?>
            <label>
              <input type="checkbox" name="tertiary_category_ids[]" value="<?= (int) $ter['tertiary_id'] ?>"
                <?= in_array($ter['tertiary_id'], (array) $selected_tertiaries) ? 'checked' : '' ?>>
              <?= htmlspecialchars($ter['tertiary_name']) ?>
            </label>
          <?php endforeach; ?>
        </div>

        <!-- Price -->
        <div class="pg-filter-group">
          <strong>Price</strong>
          <div class="pg-price-slider-wrapper">
            <div class="pg-price-range-labels">
              $<span id="minPriceLabel"><?= (float) $selected_min ?></span> –
              $<span id="maxPriceLabel"><?= (float) $selected_max ?></span>
            </div>
            <div class="pg-price-range-inputs">
              <input type="range" id="minPrice" name="min_price" class="pg-price-range-thumb pg-price-range-thumb--min"
                min="<?= (float) $min_price ?>" max="<?= (float) $max_price ?>" value="<?= (float) $selected_min ?>">
              <input type="range" id="maxPrice" name="max_price" class="pg-price-range-thumb pg-price-range-thumb--max"
                min="<?= (float) $min_price ?>" max="<?= (float) $max_price ?>" value="<?= (float) $selected_max ?>">
            </div>
          </div>
        </div>

        <!-- Brands -->
        <div class="pg-filter-group">
          <strong>Brand</strong>
          <?php foreach ($brands as $brand): ?>
            <label>
              <input type="checkbox" name="brand_ids[]" value="<?= (int) $brand['brand_id'] ?>"
                <?= in_array($brand['brand_id'], (array) $selected_brands) ? 'checked' : '' ?>>
              <?= htmlspecialchars($brand['brand_name']) ?>
            </label>
          <?php endforeach; ?>
        </div>
    </aside>

    <main class="pg-main">
      <div class="pg-filter-sort-wrapper">
        <label for="sort-select" class="pg-sort-label">Sort by:</label>
        <select id="sort-select" name="sort_by" class="pg-sort-select">
          <option value="most_selled" <?= $sort_mode === 'most_selled' ? 'selected' : ''; ?>>Most Selled</option>
          <option value="lowest_price" <?= $sort_mode === 'lowest_price' ? 'selected' : ''; ?>>Most Lowest</option>
          <option value="highest_price" <?= $sort_mode === 'highest_price' ? 'selected' : ''; ?>>Most Highest</option>
          <option value="most_rated" <?= $sort_mode === 'most_rated' ? 'selected' : ''; ?>>Most Rated</option>
        </select>
      </div>
      </form>

      <div class="pg-grid">
        <?php foreach ($products as $product):
          $card_id = $product['product_id'];
          $card_link = $product['product_link'];
          $card_name = $product['product_name'];
          $seller_id = $product['seller_id'] ?? 0;
          $card_price = $product['product_price'];
          $card_original_price = $product['original_price'] ?? null;
          $card_brand = $product['brand_name'] ?? '';
          $card_desc = $product['product_desc'] ?? '';
          $product_images = $images_by_product[$card_id] ?? [];
          $fav_icon = ($product['favored'] == 0) ? 'star.svg' : 'cancel.svg';
          $product_rating = $product['average_rating'] ?? 0;
          $product_review_count = $product['review_count'] ?? 0;

          include 'tools/product-card.php';
        endforeach; ?>
      </div>

      <!-- Sentinel & Loader for infinite scroll -->
      <div id="pgSentinel" aria-hidden="true"></div>
      <div id="pgLoader" class="pg-loader" hidden>Loading…</div>
    </main>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById('filter-form');
    const select = document.getElementById('sort-select');

    // Preserve special/q/barcode on submit
    const urlParams = new URLSearchParams(window.location.search);
    ['special', 'q', 'barcode'].forEach(function (k) {
      const v = urlParams.get(k);
      if (v !== null) {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = k;
        inp.value = v;
        form.appendChild(inp);
      }
    });

    if (form && select) {
      select.addEventListener('change', function () {
        // reset to first page on sort change
        const pageInput = form.querySelector('input[name="page"]');
        if (pageInput) pageInput.value = '1';

        const formData = new FormData(form);
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
          params.append(key, value);
        }
        params.set('sort_by', select.value);
        window.location.search = params.toString();
      });
      const currentSort = new URLSearchParams(window.location.search).get('sort_by');
      if (currentSort) select.value = currentSort;
    }

    // Filters: reset to first page when changed
    const minInput = form.querySelector('#minPrice');
    const maxInput = form.querySelector('#maxPrice');
    const minLabel = form.querySelector('#minPriceLabel');
    const maxLabel = form.querySelector('#maxPriceLabel');
    let debounce;

    const defaultMin = <?= json_encode($min_price) ?>;
    const defaultMax = <?= json_encode($max_price) ?>;

    function updateLabels() {
      minLabel.textContent = minInput.value;
      maxLabel.textContent = maxInput.value;
    }
    function updateMinMaxNameAttrs() {
      if (+minInput.value === defaultMin && +maxInput.value === defaultMax) {
        minInput.removeAttribute('name');
        maxInput.removeAttribute('name');
      } else {
        minInput.setAttribute('name', 'min_price');
        maxInput.setAttribute('name', 'max_price');
      }
    }
    function syncAndSubmit() {
      updateLabels();
      updateMinMaxNameAttrs();
      clearTimeout(debounce);
      debounce = setTimeout(() => {
        // reset to first page when changing filters
        const pageInput = form.querySelector('input[name="page"]');
        if (pageInput) pageInput.value = '1';
        form.action = window.location.pathname;
        form.submit();
      }, 2000);
    }

    minInput.addEventListener('input', syncAndSubmit);
    maxInput.addEventListener('input', syncAndSubmit);

    form.querySelectorAll('input[type="checkbox"]').forEach(checkbox =>
      checkbox.addEventListener('change', () => {
        minInput.removeAttribute('name');
        maxInput.removeAttribute('name');
        minLabel.textContent = defaultMin;
        maxLabel.textContent = defaultMax;
        // reset to first page when changing filters
        const pageInput = form.querySelector('input[name="page"]');
        if (pageInput) pageInput.value = '1';
        form.action = window.location.pathname;
        form.submit();
      })
    );

    // Programmatically move sliders to given values
    window.setPriceSlider = function (newMin, newMax) {
      minInput.value = Math.min(newMin, newMax - 1);
      maxInput.value = Math.max(newMax, newMin + 1);
      updateLabels();
      updateMinMaxNameAttrs();
    };

    // Initial state
    updateLabels();
    updateMinMaxNameAttrs();
  });
</script>

<!-- Infinite scroll with optional "Load previous" for deep links -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const grid     = document.querySelector('.pg-grid');
  const sentinel = document.getElementById('pgSentinel');
  const loader   = document.getElementById('pgLoader');
  const form     = document.getElementById('filter-form');
  if (!grid || !sentinel || !form) return;

  const pageInput = form.querySelector('input[name="page"]');
  const urlParams = new URLSearchParams(location.search);

  // Determine current (landing) page
  let currentPage = Math.max(1, parseInt(pageInput?.value || urlParams.get('page') || '1', 10));
  // Track page range currently in DOM
  let lowestPage  = currentPage;
  let highestPage = currentPage;

  let loadingNext = false;
  let loadingPrev = false;
  let done        = false; // no more next pages

  function showLoader()  { if (loader) loader.style.display = 'flex'; }
  function hideLoader()  { if (loader) loader.style.display = 'none'; }

  function buildUrlForPage(p) {
    const p2 = new URLSearchParams(location.search);
    p2.set('page', String(p));
    return location.pathname + '?' + p2.toString();
  }

  // APPEND: load next page and append its cards
  async function loadNextPage() {
    if (loadingNext || done) return;
    loadingNext = true; showLoader();
    try {
      const next = highestPage + 1;
      const res  = await fetch(buildUrlForPage(next), { credentials: 'same-origin' });
      if (!res.ok) throw new Error('Network error');
      const html = await res.text();

      const doc      = new DOMParser().parseFromString(html, 'text/html');
      const newCards = doc.querySelectorAll('.pg-grid > *');
      if (!newCards.length) {
        done = true;
        sentinel.remove();
        hideLoader();
        return;
      }

      newCards.forEach(node => grid.appendChild(node));

      highestPage = next;
      currentPage = highestPage;                 // bottom-most drives URL
      const qp = new URLSearchParams(location.search);
      qp.set('page', String(currentPage));
      history.pushState({ page: currentPage }, '', location.pathname + '?' + qp.toString());
      if (pageInput) pageInput.value = String(currentPage);
    } catch (e) {
      console.error(e);
    } finally {
      loadingNext = false; hideLoader();
    }
  }

  // PREPEND: load previous page and prepend its cards (if landed on page>1)
  async function loadPreviousPage() {
    if (loadingPrev || lowestPage <= 1) return;
    loadingPrev = true; showLoader();
    try {
      const prev = lowestPage - 1;
      const res  = await fetch(buildUrlForPage(prev), { credentials: 'same-origin' });
      if (!res.ok) throw new Error('Network error');
      const html = await res.text();

      const doc   = new DOMParser().parseFromString(html, 'text/html');
      const wrap  = doc.querySelector('.pg-grid');
      if (!wrap) return;

      const newCards = Array.from(wrap.children);
      if (!newCards.length) {
        lowestPage = 1;
        updatePrevUI();
        return;
      }

      // Keep scroll position stable while prepending
      const firstBefore = grid.firstElementChild;
      const beforeTop   = firstBefore ? firstBefore.getBoundingClientRect().top : 0;

      // Prepend in original order
      for (let i = newCards.length - 1; i >= 0; i--) {
        grid.insertBefore(newCards[i], grid.firstChild);
      }

      if (firstBefore) {
        const afterTop = firstBefore.getBoundingClientRect().top;
        window.scrollBy(0, afterTop - beforeTop);
      }

      lowestPage = prev;
      updatePrevUI();
      // Keep hidden input aligned with bottom-most page for later form submits
      if (pageInput) pageInput.value = String(highestPage);
    } catch (e) {
      console.error(e);
    } finally {
      loadingPrev = false; hideLoader();
    }
  }

  // Auto-load next on scroll
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      if (entries[0].isIntersecting) loadNextPage();
    }, { rootMargin: '800px 0px' });
    io.observe(sentinel);
  }

  // Back/forward: simplest—reload to reflect URL
  window.addEventListener('popstate', () => location.reload());

  // ---- Inject "Load previous" button if landing on page > 1 ----
  let prevWrap = null, prevBtn = null;
  function ensurePrevUI() {
    if (prevWrap) return;
    prevWrap = document.createElement('div');
    prevWrap.style.display = 'flex';
    prevWrap.style.justifyContent = 'center';
    prevWrap.style.margin = '12px 0 8px';
    prevBtn = document.createElement('button');
    prevBtn.type = 'button';
    prevBtn.textContent = 'Load previous products';
    prevBtn.style.padding = '8px 14px';
    prevBtn.style.border = '1px solid #ddd';
    prevBtn.style.background = '#fff';
    prevBtn.style.borderRadius = '6px';
    prevBtn.style.cursor = 'pointer';
    prevBtn.addEventListener('click', loadPreviousPage);
    prevWrap.appendChild(prevBtn);
    grid.parentNode.insertBefore(prevWrap, grid); // place above the grid
  }

  function updatePrevUI() {
    if (lowestPage > 1) {
      ensurePrevUI();
      prevWrap.style.display = 'flex';
      prevBtn.disabled = false;
    } else if (prevWrap) {
      prevWrap.style.display = 'none';
    }
  }

  updatePrevUI(); // show if landed on deep page
});
</script>
