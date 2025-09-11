document.addEventListener('DOMContentLoaded', () => {
  /* ===============================
   * Helpers
   * =============================== */
  const $ = (sel) => document.querySelector(sel);
  const form = $('#productAddForm');
  const messageBox = $('#formMessage');
  const uploadBtn = $('.file-upload-button');
  const fileInput = $('#file-upload');
  const previewWrap = $('#preview-images');

  const catSel = $('#productCategory');
  const subSel = $('#productSubCategory');
  const tetSel = $('#productTetCategory');
  const brandSel = $('#productBrand');

  // files kept in UI order (shared across handlers)
  let selectedFiles = [];

  function showMessage(text, type = 'info') {
    if (!messageBox) return console.warn('messageBox missing:', text);
    const map = { success: 'alert-success', error: 'alert-danger', info: 'alert-info' };
    messageBox.textContent = text;
    messageBox.className = 'alert ' + (map[type] || map.info);
    messageBox.style.display = 'block';
  }
  const safeJSON = (t) => { try { return JSON.parse(t); } catch { return null; } };

  if (!form) { console.error('❌ #productAddForm not found.'); return; }

  /* ===============================
   * FORM SUBMIT
   * =============================== */
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const fd = new FormData(form);
    const title = (fd.get('productTitle') || '').toString().trim();
    if (!title) return showMessage('Product title is required.', 'error');

    const files = fd.getAll('img[]').filter(Boolean);
    if (files.length > 4) return showMessage('You can only upload up to 4 images.', 'error');
    if (files.some(f => !(f instanceof File) || !f.type.startsWith('image/'))) {
      return showMessage('All selected files must be images.', 'error');
    }

    try {
      const res = await fetch('action-php/admin_product_add.php', { method: 'POST', body: fd });
      const raw = await res.text();

      const json = safeJSON(raw);
      if (!json) return showMessage('Invalid server response. Check console.', 'error');

      if (json.success) {
        showMessage('✅ Product added successfully!', 'success');
        form.reset();
        selectedFiles = [];
        if (previewWrap) previewWrap.innerHTML = '';
      } else {
        console.warn('Server error payload:', json);
        showMessage(json.message || 'Submission failed.', 'error');
      }
    } catch (err) {
      console.error(err);
      showMessage('❌ An error occurred while submitting the form.', 'error');
    }
  });

  /* ===============================
   * IMAGE PREVIEW + REORDER (wrap)
   * =============================== */
  if (uploadBtn && fileInput && previewWrap) {
    uploadBtn.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', () => {
      const files = Array.from(fileInput.files || []);
      const imagesOnly = files.filter(f => f.type.startsWith('image/'));
      if (imagesOnly.length !== files.length) showMessage('Only image files are allowed.', 'error');

      if (imagesOnly.length > 4) {
        showMessage('You can only select up to 4 images.', 'error');
        fileInput.value = '';
        previewWrap.innerHTML = '';
        selectedFiles = [];
        return;
      }

      selectedFiles = imagesOnly;
      renderPreviews();
      syncInput();
    });

    // SINGLE delegated click handler → no stale idx
    previewWrap.addEventListener('click', (e) => {
      const leftBtn  = e.target.closest('button.preview-nav.left');
      const rightBtn = e.target.closest('button.preview-nav.right');
      const delBtn   = e.target.closest('button.preview-del');
      const cell = e.target.closest('.preview-wrapper');
      if (!cell) return;

      const idx = Number(cell.dataset.idx);
      if (Number.isNaN(idx)) return;

      if (leftBtn)  { moveLeft(idx);  return; }
      if (rightBtn) { moveRight(idx); return; }
      if (delBtn)   { removeAt(idx);  return; }
    });

    function renderPreviews() {
      previewWrap.innerHTML = '';
      selectedFiles.forEach((file, idx) => {
        const reader = new FileReader();
        reader.onload = () => {
          const wrapper = document.createElement('div');
          wrapper.className = 'preview-wrapper';
          wrapper.dataset.idx = String(idx);

          const img = document.createElement('img');
          img.className = 'preview-thumb';
          img.src = reader.result;
          img.alt = file.name;

          const label = document.createElement('div');
          label.className = 'preview-label';
          label.textContent = file.name.length > 18 ? file.name.slice(0, 18) + '…' : file.name;
          label.title = file.name;

          const left = document.createElement('button');
          left.type = 'button';
          left.className = 'preview-nav left';
          left.textContent = '◀';

          const right = document.createElement('button');
          right.type = 'button';
          right.className = 'preview-nav right';
          right.textContent = '▶';

          const del = document.createElement('button');
          del.type = 'button';
          del.className = 'preview-del';
          del.textContent = '✕';
          del.title = 'Remove';

          // HOVER POPUP — always in viewport
          let popup = null;
          let follow = null;
          const margin = 10;
          const cursorOffset = 14;
          const clamp = (v, min, max) => Math.max(min, Math.min(v, max));

          function placePopupAt(x, y) {
            if (!popup) return;
            const { width: pw, height: ph } = popup.getBoundingClientRect();
            let left = x + cursorOffset;
            let top  = y + cursorOffset;
            const maxLeft = window.innerWidth  - pw - margin;
            const maxTop  = window.innerHeight - ph - margin;
            if (left > maxLeft) left = x - pw - cursorOffset;
            left = clamp(left, margin, maxLeft);
            top  = clamp(top,  margin, maxTop);
            popup.style.left = left + 'px';
            popup.style.top  = top  + 'px';
          }

          img.addEventListener('mouseenter', (ev) => {
            popup = document.createElement('div');
            popup.className = 'preview-popup-global';
            popup.style.position = 'fixed';
            popup.style.left = '-9999px';
            popup.style.top = '-9999px';
            popup.style.zIndex = '9999';
            popup.style.pointerEvents = 'none';

            const big = document.createElement('img');
            big.src = img.src;
            popup.appendChild(big);
            document.body.appendChild(popup);

            const applyInitialPos = () => {
              const clientX = ev.clientX ?? (img.getBoundingClientRect().right);
              const clientY = ev.clientY ?? (img.getBoundingClientRect().top);
              placePopupAt(clientX, clientY);
            };
            if (big.complete) requestAnimationFrame(applyInitialPos);
            else big.addEventListener('load', () => requestAnimationFrame(applyInitialPos), { once: true });

            follow = (moveEv) => placePopupAt(moveEv.clientX, moveEv.clientY);
            document.addEventListener('mousemove', follow);

            const keepClamped = () => {
              const r = img.getBoundingClientRect();
              placePopupAt(r.right, r.top);
            };
            popup._keepClampedResize = keepClamped;
            window.addEventListener('resize', keepClamped);
            window.addEventListener('scroll', keepClamped, true);
          });

          img.addEventListener('mouseleave', () => {
            if (follow) document.removeEventListener('mousemove', follow);
            if (popup) {
              window.removeEventListener('resize', popup._keepClampedResize);
              window.removeEventListener('scroll', popup._keepClampedResize, true);
              popup.remove();
            }
            popup = null;
            follow = null;
          });

          wrapper.append(left, img, right, label, del);
          previewWrap.appendChild(wrapper);
        };
        reader.readAsDataURL(file);
      });
    }

    // === Arrow logic (robust + wrap-around) ===
    function moveLeft(i) {
      const n = selectedFiles.length;
      if (n <= 1) return;

      if (i === 0) {
        // rotate: first -> end
        selectedFiles.push(selectedFiles.shift());
      } else {
        // swap with previous
        [selectedFiles[i - 1], selectedFiles[i]] = [selectedFiles[i], selectedFiles[i - 1]];
      }
      renderPreviews();
      syncInput();
    }

    function moveRight(i) {
      const n = selectedFiles.length;
      if (n <= 1) return;

      if (i === n - 1) {
        // rotate: last -> start
        selectedFiles.unshift(selectedFiles.pop());
      } else {
        // swap with next
        [selectedFiles[i + 1], selectedFiles[i]] = [selectedFiles[i], selectedFiles[i + 1]];
      }
      renderPreviews();
      syncInput();
    }

    function removeAt(idx) {
      selectedFiles.splice(idx, 1);
      renderPreviews();
      syncInput();
    }

    // Keep <input type="file"> order in sync with UI
    function syncInput() {
      const dt = new DataTransfer();
      selectedFiles.forEach(f => dt.items.add(f));
      fileInput.files = dt.files;
    }
  }

  /* ===============================
   * CATEGORIES → SUB → TET → BRAND
   * =============================== */
  if (catSel && subSel && tetSel && brandSel) {
    fetch('action-php/get_categories.php')
      .then(r => r.json())
      .then(list => {
        if (!Array.isArray(list)) return console.error('Unexpected categories payload:', list);
        for (const c of list) {
          const opt = document.createElement('option');
          opt.value = c.category_id;
          opt.textContent = c.category_name;
          catSel.appendChild(opt);
        }
      })
      .catch(err => console.error('Error loading categories:', err));

    catSel.addEventListener('change', () => {
      const catId = catSel.value;
      subSel.innerHTML = '<option value="">-- Select Subcategory --</option>';
      tetSel.innerHTML = '<option value="">-- Select Tertiary Category --</option>';
      brandSel.innerHTML = '<option value="">-- Select Brand --</option>';
      if (!catId) return;

      fetch(`action-php/get_subcategories.php?cat_id=${encodeURIComponent(catId)}`)
        .then(r => r.json())
        .then(data => {
          const subs = Array.isArray(data?.subcategories) ? data.subcategories : [];
          const brands = Array.isArray(data?.brands) ? data.brands : [];

          for (const s of subs) {
            const opt = document.createElement('option');
            opt.value = s.product_sub_category_id;
            opt.textContent = s.product_sub_category_name;
            subSel.appendChild(opt);
          }
          for (const b of brands) {
            const opt = document.createElement('option');
            opt.value = b.brand_id;
            opt.textContent = b.brand_name;
            brandSel.appendChild(opt);
          }
        })
        .catch(err => console.error('Error loading subcategories/brands:', err));
    });

    subSel.addEventListener('change', () => {
      const subId = subSel.value;
      tetSel.innerHTML = '<option value="">-- Select Tertiary Category --</option>';
      if (!subId) return;

      fetch(`action-php/get_tetcategories.php?sub_id=${encodeURIComponent(subId)}`)
        .then(r => r.json())
        .then(list => {
          if (!Array.isArray(list)) {
            console.error('Expected array of tertiaries, got:', list);
            return;
          }
          for (const t of list) {
            const opt = document.createElement('option');
            opt.value = t.product_tertiary_category_id;
            opt.textContent = t.product_tertiary_category_name;
            tetSel.appendChild(opt);
          }
        })
        .catch(err => console.error('Error loading tertiary categories:', err));
    });
  }
});
