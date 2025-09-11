function showToast(message, isError = false) {
  const toast = document.getElementById('shipment-toast');
  toast.textContent = message;
  toast.className = 'toast-notification' + (isError ? ' error' : '');
  toast.style.display = 'block';

  setTimeout(() => {
    toast.style.display = 'none';
  }, 3000);
}

document.querySelectorAll('.simulate-btn').forEach(button => {
  button.addEventListener('click', () => {
    const shipmentId = button.dataset.shipmentId;
    const currentStatus = button.dataset.currentStatus;
    const card = button.closest('.shipment-card');

    fetch('../shipment-pages/simulate-shipment-status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ shipment_id: shipmentId, current_status: currentStatus })
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Animate and remove the card
          if (card) {
            card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.95)';
            setTimeout(() => {
              card.remove();

              // Check if all cards are gone
              const grid = document.querySelector('.shipment-grid');
              if (grid && grid.children.length === 0) {
                // Reload just the shipment cards via AJAX
                fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                  .then(res => res.text())
                  .then(html => {
                    const temp = document.createElement('div');
                    temp.innerHTML = html;
                    const newCards = temp.querySelectorAll('.shipment-card');
                    if (newCards.length > 0) {
                      newCards.forEach(c => grid.appendChild(c));
                    } else {
                      grid.insertAdjacentHTML('afterend', '<p>No waiting shipments found.</p>');
                    }
                  })
                  .catch(err => {
                    console.error(err);
                    grid.insertAdjacentHTML('afterend', '<p>Failed to reload shipments.</p>');
                  });
              }
            }, 300);
          }

          showToast('Shipment status simulated!');
        }
        else {
          showToast('Failed: ' + (data.message || 'Unknown error'), true);
        }
      })
      .catch(err => {
        console.error(err);
        showToast('Error sending request', true);
      });
  });
});

document.addEventListener('DOMContentLoaded', () => {
  const popup = document.getElementById('simulate-hover-popup');

  if (!popup) return; // stop if popup not found

  document.querySelectorAll('.simulate-btn').forEach(button => {
    button.addEventListener('mouseenter', () => {
      popup.textContent = button.getAttribute('title') || 'Simulate';
      popup.classList.add('visible');
      popup.style.display = 'block';
    });

    button.addEventListener('mouseleave', () => {
      popup.classList.remove('visible');
      setTimeout(() => {
        popup.style.display = 'none';
      }, 200);
    });
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const searchBtn = document.getElementById('search-shipment-btn');
  const input = document.getElementById('order-id-input');
  const resultDiv = document.getElementById('shipment-search-result');

  // Only attach listener if elements exist on this page
  if (searchBtn && input && resultDiv) {
    searchBtn.addEventListener('click', function () {
      const orderId = input.value.trim();
      if (!orderId) return;

      fetch('shipment-pages/search-shipment-by-order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_id: orderId })
      })
        .then(res => res.json())
        .then(data => {
          console.log(data);
          if (data.success && data.shipments && data.shipments.length > 0) {
            resultDiv.innerHTML = '';

            data.shipments.forEach(order => {
              const productHtml = order.products.map(product => `
                <div class="product-detail">
                  <p><strong>${product.brand_name} - ${product.product_name}</strong></p>
                  <p>GTIN: ${product.product_GTIN}</p>
                  <p>Quantity: ${product.product_quantity}</p>
                  <p>Total: ₺${parseFloat(product.product_total_paid).toFixed(2)}</p>
                  ${product.image_filename ? `
                    <img src="/uploads/products/${product.product_link}/${product.image_filename}" class="product-thumb" style="max-width: 100px;">
                  ` : '<div class="product-thumb placeholder">No Image</div>'}
                </div>
              `).join('');

              resultDiv.innerHTML += `
                <div class="order-result-box">
                  <h4>Order ID: #${order.order_id}</h4>
                  <p>Tracking Number: ${order.tracking_number}</p>
                  <p>Status: ${order.shipping_status}</p>
                  <p>Company: ${order.shipping_company || '—'}</p>
                  <p>Date: ${order.order_created_at}</p>
                  ${productHtml}
                </div>
              `;
            });

          } else {
            resultDiv.innerHTML = `<p class="not-found">Order not found or doesn't belong to you.</p>`;
          }
        })
        .catch(() => {
          resultDiv.innerHTML = `<p class="error">Error fetching data.</p>`;
        });
    });
  }
});
