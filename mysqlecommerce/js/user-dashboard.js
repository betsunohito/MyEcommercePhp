function toggleDetails(el, orderId) {
  const item = el.closest('.order-item');
  if (!item) return;

  const detailsGrid = item.querySelector('.details-grid');
  if (!detailsGrid) return;

  const isActive = item.classList.contains('active');
  item.classList.toggle('active');

  if (!isActive) {
    detailsGrid.innerHTML = "<p>Yükleniyor...</p>";

    fetch('tools/dashboard-pages/selected_orders_detail.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ order_id: orderId })
    })
      .then(r => r.text())
      .then(text => {
        let data;
        try {
          data = JSON.parse(text);
        } catch (e) {
          console.error("Invalid JSON:", text);
          throw e;
        }

        if (data.status === 'success') {
          const products = data.products || [];
          const orderArr = data.order || [];
          const order = orderArr[0] || {};

          // Group products by seller_id
          const sellers = {};
          for (const p of products) {
            const sid = p.seller_id || 'unknown';
            if (!sellers[sid]) {
              sellers[sid] = {
                seller_name: p.seller_company_name || 'Bilinmeyen Satıcı',
                items: []
              };
            }
            sellers[sid].items.push(p);
          }
          console.log("Sellers grouped:", sellers);
          // Build HTML per seller
          const sellerSections = Object.entries(sellers).map(([sellerId, group]) => {
            const productHTML = group.items.map(p => `
  <li class="product-item">
    <a href="product-detail.php?id=${p.product_id || ''}" class="product-link">
      <img src="/uploads/products/${p.product_link || ''}/${p.image_filename || ''}" 
           alt="${p.product_name || ''}" 
           class="product-image-small">
    </a>
    <div class="product-info-meta">
      <a href="product-detail.php?id=${p.product_id || ''}" class="product-link">
        <span class="product-name">${p.brand_name || ''} ${p.product_name || ''}</span>
      </a>
      <div class="product-meta">
        <span class="product-quantity">Quantity: ${p.product_quantity || 0}</span>
        <span class="product-type">${p.product_type_name ? `Type: ${p.product_type_name}` : ''}</span>
        <span class="product-total">₺${((p.order_product_price || 0) * (p.product_quantity || 0)).toFixed(2)}</span>
      </div>
    </div>
  </li>
`).join('');


            const etaDate = group.items[0]?.shipping_eta ? new Date(group.items[0].shipping_eta) : null;
            const formattedDate = etaDate
              ? etaDate.toLocaleDateString('tr-TR', { day: '2-digit', month: 'long', year: 'numeric' })
              : '—';
            const status = group.items[0]?.shipping_status || '';
            const statusClass = getStatusClass(status);
            return `
            <div class="seller-section">
              <h3>🛒 Satıcı: ${group.seller_name}</h3>
              <div class="order-section">
                <h4>🛍️ Ürünler</h4>
                <ul>${productHTML}</ul>
              </div>
              <div class="order-section">
                <h4>📦 Kargo Bilgisi</h4>
                <p>Firma: <strong>${group.items[0]?.shipping_company || '—'}</strong></p>
                <p>Takip: <a href="#">${group.items[0]?.shipping_tracking_code || '—'}</a></p>
                <p>ETA: <strong>${formattedDate}</strong></p>
                  <p>Status: <span class="status-label ${statusClass}">${status}</span></p>
              </div>
            </div>
          `;
          }).join('');

          const fullHTML = `
          <div class="seller-columns">${sellerSections}</div>
          <div class="order-columns">
          <div class="order-panel">
            <h4>💳 Ödeme Bilgisi</h4>
            <p>Yöntem: <strong>${order.payment_method || '—'}</strong></p>
            <p>Durum: <span class="paid">${order.payment_status || '—'}</span></p>
          </div>
          <div class="order-panel">
            <h4>🏠 Teslimat Adresi</h4>
            <p>${order.order_name || ''} ${order.order_surname || ''}</p>
            <p>${order.shipping_district_name || ''} ${order.shipping_neighborhood_name || ''}</p>
            <p>${order.order_address_note || ''}</p>
            <p>${order.shipping_province_name || ''}</p>
            <p>${order.order_phone_number || ''}</p>
          </div>
          </div>
        `;

          detailsGrid.innerHTML = fullHTML;
        } else {
          detailsGrid.innerHTML = `<p style="color:red">${data.message || "Detaylar alınamadı."}</p>`;
        }
      })
      .catch(err => {
        console.error("Fetch/parsing error:", err);
        detailsGrid.innerHTML = "<p style='color:red'>Detaylar alınamadı.</p>";
      });

    // Smooth scroll highlight
    setTimeout(() => {
      item.scrollIntoView({ behavior: 'smooth', block: 'start' });
      item.classList.add('highlight');
      setTimeout(() => item.classList.remove('highlight'), 1000);
    }, 200);
  } else {
    detailsGrid.innerHTML = '';
  }
}
function getStatusClass(status) {
  switch (status) {
    case 'waiting': return 'status-waiting';
    case 'shipped': return 'status-shipped';
    case 'completed': return 'status-completed';
    default: return '';
  }
}

// ⭐ Star rating logic
document.addEventListener('click', function (e) {
  if (e.target.classList.contains('star')) {
    const parent = e.target.parentElement;
    const value = parseInt(e.target.dataset.value);
    parent.querySelectorAll('.star').forEach((star, index) => {
      star.textContent = index < value ? '★' : '☆';
    });
    parent.setAttribute('data-rating', value);
  }
});

// ✅ Submit review
document.querySelectorAll('.submit-review-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    const orderDetailId = this.dataset.id;
    const text = document.querySelector(`#review-text-${orderDetailId}`).value;
    const rating = document.querySelector(`.star-rating[data-id="${orderDetailId}"]`).getAttribute('data-rating');

    if (!rating) {
      showToast("Lütfen yıldız puanlaması yapınız.",true);
      return;
    }

    // Send with AJAX (you can customize endpoint)
    fetch('tools/action/review-submit.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `order_detail_id=${orderDetailId}&rating=${rating}&comment=${encodeURIComponent(text)}`
    })
      .then(res => res.text())
      .then(data => {
        showToast("Review submitted");
        // Optionally remove the review UI
        this.closest('.review-container').remove();
      });
  });
});


// Star selection (optional)
document.querySelectorAll('.star-rating').forEach(group => {
  group.addEventListener('click', e => {
    if (!e.target.matches('.star')) return;
    const val = parseInt(e.target.dataset.value);
    group.querySelectorAll('.star').forEach((s, i) => {
      s.textContent = i < val ? '★' : '☆';
    });
  });
});


const input   = document.getElementById('order-search-input');
const list    = document.querySelector('.order-list');
const overlay = document.querySelector('.order-list .huge-blank');

if (input && list && overlay) {
  // ensure the overlay can anchor to the list
  if (getComputedStyle(list).position === 'static') list.style.position = 'relative';

  const open  = () => { overlay.classList.add('is-open'); document.body.classList.add('overlay-open'); };
  const close = () => { overlay.classList.remove('is-open'); document.body.classList.remove('overlay-open'); };
  const isOpen = () => overlay.classList.contains('is-open');

  // open when you type / focus / change; close if input cleared
  input.addEventListener('input', () => (input.value.trim() ? open() : close()));
  input.addEventListener('focus', open);
  input.addEventListener('change', open);

  // click outside → close
  document.addEventListener('click', (e) => {
    if (!isOpen()) return;
    if (overlay.contains(e.target) || input.contains(e.target)) return;
    close();
  });

  // Esc → close
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
}

document.addEventListener('DOMContentLoaded', () => {
  const hugeBlank = document.querySelector('.order-list .huge-blank');
  if (!hugeBlank) return;

  // Stable container inside .huge-blank
  let listBox = hugeBlank.querySelector('.js-orders');
  if (!listBox) {
    listBox = document.createElement('div');
    listBox.className = 'js-orders';
    hugeBlank.appendChild(listBox);
  }

  const params = new URLSearchParams(location.search);
  const statusParam = (params.get('status') || '').trim();
  const MIN_Q = 3;

  // ✅ Correct input id
  const search = document.getElementById('order-search-input');
  let q = (search?.value || (params.get('q') || '')).trim();
  if (search) search.value = q;

  function formatDate(d) {
    const x = new Date(d);
    return isNaN(x) ? d : x.toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' }).replace(',', '');
  }
  function formatTL(n) {
    const num = Number(n);
    return '₺' + (Number.isFinite(num) ? num.toFixed(2) : n);
  }

  function render(orders) {
    listBox.innerHTML = (orders || []).map(o => `
      <div class="order-item">
        <div class="order-summary" onclick="toggleDetails(this, ${o.order_id})">
          <div class="summary-left">
            ${(o.images || []).map(src => `
              <img src="${src}" width="50" height="50"
                   style="object-fit: contain; border-radius: 4px; border: 1px solid #ccc; background: #f9f9f9;">
            `).join('')}
          </div>
          <div class="summary-center">
            <span class="custom-message">Sipariş no: <strong>${o.order_id}</strong></span>
          </div>
          <div class="summary-right">
            <small class="summary-date">${formatDate(o.created_at)}</small>
            <span class="total">${formatTL(o.order_total_price)}</span>
            <span class="arrow">▼</span>
          </div>
        </div>
        <div class="order-details"><div class="details-grid"></div></div>
      </div>
    `).join('');
  }

  let currentAbort = null;
  function loadOrders() {
    const qParam = (q && q.length >= MIN_Q) ? q : '';
    const url = `tools/dashboard-pages/order-query.php?status=${encodeURIComponent(statusParam)}&q=${encodeURIComponent(qParam)}&t=${Date.now()}`;

    if (currentAbort) currentAbort.abort();
    currentAbort = new AbortController();

    fetch(url, { credentials: 'same-origin', cache: 'no-store', signal: currentAbort.signal })
      .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
      .then(data => {
        if (data.error) throw new Error(data.message || 'Hata');
        render(data.orders || []);
      })
      .catch(err => {
        if (err.name === 'AbortError') return;
        listBox.innerHTML = `<div class="error">Yüklenemedi: ${err.message}</div>`;
        console.error('order-query failed:', err);
      });
  }

  const debounce = (fn, ms) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };

  if (search) {
    const run = () => { q = search.value.trim(); loadOrders(); };
    search.addEventListener('input', debounce(run, 250));
    search.addEventListener('keyup', e => { if (e.key === 'Enter') run(); });
  }

});

