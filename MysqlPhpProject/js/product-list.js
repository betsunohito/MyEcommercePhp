document.addEventListener('DOMContentLoaded', () => {
  const table = document.querySelector('#admin-product-table') || document;

  // Seed dataset.original to detect changes later
  table.querySelectorAll('.price-input, .quantity-input, .discount-input, .original-input').forEach(inp => {
    if (!inp.dataset.original) inp.dataset.original = inp.value || '';
  });

  const showToast = (message, isSuccess = true) => {
    const toast = document.getElementById('shipment-toast');
    if (!toast) return;
    toast.textContent = message;
    toast.className = 'toast-notification ' + (isSuccess ? 'success' : 'error');
    toast.style.opacity = '1';
    setTimeout(() => { toast.style.opacity = '0'; }, 2500);
  };

  // Normalize & validate per type
  function normalize(type, raw) {
    const str = String(raw).replace(',', '.').trim();

    if (type === 'quantity') {
      const n = parseInt(str, 10);
      if (!Number.isFinite(n) || n < 0) return { ok: false };
      return { ok: true, value: n };
    }

    if (type === 'original') {
      // empty -> clear; 0 -> clear; >0 -> set
      if (str === '') return { ok: true, value: 0 };
      const n = parseFloat(str);
      if (!Number.isFinite(n) || n < 0) return { ok: false };
      return { ok: true, value: Math.round(n * 100) / 100 };
    }

    // price or discount: must be > 0
    const n = parseFloat(str);
    if (!Number.isFinite(n) || n <= 0) return { ok: false };
    return { ok: true, value: Math.round(n * 100) / 100 };
  }

  function findRowParts(el) {
    const row = el.closest('tr');
    const priceInput    = row?.querySelector('.price-input');
    const qtyInput      = row?.querySelector('.quantity-input');
    const discountInput = row?.querySelector('.discount-input'); // if you still have one for display
    const originalInput = row?.querySelector('.original-input');

    const priceBtn    = row?.querySelector('.price-btn');
    const qtyBtn      = row?.querySelector('.quantity-btn');
    const discountBtn = row?.querySelector('.discount-btn'); // distinct discount button (optional)
    const originalBtn = row?.querySelector('.original-btn');

    return { row, priceInput, qtyInput, discountInput, originalInput, priceBtn, qtyBtn, discountBtn, originalBtn };
  }

  // Show/hide Apply on input (delegated)
  table.addEventListener('input', (e) => {
    const input = e.target.closest('.price-input, .quantity-input, .discount-input, .original-input');
    if (!input) return;

    const type =
      input.classList.contains('price-input') ? 'price' :
      input.classList.contains('quantity-input') ? 'quantity' :
      input.classList.contains('discount-input') ? 'discount' : 'original';

    const norm = normalize(type, input.value);
    const original = input.dataset.original ?? '';
    const changed = String(input.value).trim() !== String(original).trim();

    const td = input.closest('td');
    const btn = td?.querySelector(`.${type}-btn`);
    if (btn) btn.style.display = (norm.ok && changed) ? 'inline-block' : 'none';

    // Extra: typing in PRICE should also toggle the DISCOUNT button (if you show a separate one)
    if (type === 'price') {
      const discountBtn = td?.querySelector('.discount-btn');
      if (discountBtn) discountBtn.style.display = (norm.ok && changed) ? 'inline-block' : 'none';
    }

    input.classList.add('input-editing');
    input.classList.remove('input-success');
  });

  // Click handler for all buttons (delegated)
  table.addEventListener('click', async (e) => {
    const btn = e.target.closest('.price-btn, .quantity-btn, .discount-btn, .original-btn');
    if (!btn) return;

    const type =
      btn.classList.contains('price-btn') ? 'price' :
      btn.classList.contains('quantity-btn') ? 'quantity' :
      btn.classList.contains('discount-btn') ? 'discount' : 'original';

    const {
      row, priceInput, qtyInput, discountInput, originalInput
    } = findRowParts(btn);

    // For 'discount', read from the price input; otherwise read from the matching input
    const cellInput = (type === 'discount')
      ? row?.querySelector('.price-input')
      : row?.querySelector(`.${type}-input`);

    if (!row || !cellInput) { showToast('Missing inputs in row', false); return; }

    const norm = normalize(type, cellInput.value);
    if (!norm.ok) {
      let msg = 'Enter a valid value';
      if (type === 'quantity') msg = 'Quantity must be 0 or more';
      else if (type === 'price' || type === 'discount') msg = 'Enter a valid amount (> 0)';
      else if (type === 'original') msg = 'Original must be ≥ 0 (0 clears)';
      showToast(msg, false);
      return;
    }

    // IDs (read ONLY from the button datasets, no <tr> data-* needed)
    const productId = Number(btn.dataset.productId || 0);
    const productType = Number(btn.dataset.productType || 0);
    if (!Number.isFinite(productId) || productId <= 0) { showToast('Invalid product id', false); return; }

    // Capture old active price BEFORE request (used for discount flow)
    const oldActivePrice = priceInput ? priceInput.value : '';

    btn.disabled = true;

    try {
      const res = await fetch('action-php/admin_product_update.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          type,
          product_id: productId,
          product_type: productType,
          value: norm.value
        })
      });

      const data = await res.json().catch(() => ({}));
      if (!res.ok || !data?.success) {
        showToast(data?.error || 'Update failed', false);
        return;
      }

      showToast(data.message || 'Updated', true);

      const markSuccess = (inp) => {
        if (!inp) return;
        inp.classList.remove('input-editing');
        inp.classList.add('input-success');
        inp.dataset.original = inp.value; // baseline after success
      };

      switch (data.code) {
        case 1: // price updated
          if (priceInput) {
            priceInput.value = String(norm.value);
            markSuccess(priceInput);
          }
          break;

        case 3: // quantity updated
          if (qtyInput) {
            qtyInput.value = String(norm.value);
            markSuccess(qtyInput);
          }
          break;

        case 4: // discount applied: active = new value, original = old active
          if (priceInput) {
            priceInput.value = String(norm.value);
            markSuccess(priceInput);
          }
          if (originalInput) {
            const serverOld = (typeof data.old_price !== 'undefined') ? data.old_price : null;
            const crossed = serverOld ?? oldActivePrice;
            originalInput.value = String(crossed || '');
            originalInput.dataset.original = originalInput.value;
            originalInput.classList.remove('input-editing');
          }
          break;

        case 2: // original cleared due to inversion after price update
          if (priceInput) markSuccess(priceInput);
          if (originalInput) {
            originalInput.value = '';
            originalInput.dataset.original = '';
            originalInput.classList.remove('input-editing', 'input-success');
          }
          break;

        case 5: // original set or cleared explicitly
          if (originalInput) {
            originalInput.value = norm.value > 0 ? String(norm.value) : '';
            if (norm.value > 0) {
              markSuccess(originalInput);
            } else {
              originalInput.dataset.original = '';
              originalInput.classList.remove('input-editing', 'input-success');
            }
          }
          break;

        case 100: // invalid discount
          if (priceInput) {
            // keep price input in editing state to indicate correction needed
            priceInput.classList.remove('input-success');
            priceInput.classList.add('input-editing');
          }
          break;

        case 101: // invalid original (<= active)
          if (originalInput) {
            originalInput.classList.remove('input-success');
            originalInput.classList.add('input-editing');
          }
          break;

        default:
          break;
      }
    } catch (err) {
      console.error(err);
      showToast('Network/Server error', false);
    } finally {
      btn.disabled = false;
      // Hide Apply if no longer changed
      const changed = String(cellInput.value).trim() !== String(cellInput.dataset.original || '').trim();
      btn.style.display = changed ? 'inline-block' : 'none';
    }
  });
});
