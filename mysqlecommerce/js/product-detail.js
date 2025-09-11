const images = Array.from(document.querySelectorAll(".product__details__pic__slider img"));
const mainImg = document.querySelector(".product__details__pic__item img");
const prevBtn = document.querySelector(".previous-btn");
const nextBtn = document.querySelector(".next-btn");

let currentIndex = 0;

function showImage(index) {
  if (images.length === 0) return;

  if (index < 0) index = images.length - 1;
  if (index >= images.length) index = 0;

  currentIndex = index;

  const newSrc = images[currentIndex].getAttribute("data-imgbigurl") || images[currentIndex].src;
  mainImg.src = newSrc;

  // Optional: visually mark active thumbnail (add 'active' class)
  images.forEach(img => img.classList.remove('active'));
  images[currentIndex].classList.add('active');
}

// Button events
prevBtn.addEventListener("click", () => showImage(currentIndex - 1));
nextBtn.addEventListener("click", () => showImage(currentIndex + 1));

// Add click event to thumbnails
images.forEach((img, idx) => {
  img.style.cursor = 'pointer';
  img.addEventListener('click', () => showImage(idx));
});

// Initialize carousel display
showImage(0);


let reviewsLoaded = false;

document.querySelectorAll('.nav-link').forEach(tab => {
  tab.addEventListener('click', function (e) {
    e.preventDefault();

    // Switch active classes
    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));

    this.classList.add('active');
    const targetSelector = this.getAttribute('href');
    const pane = document.querySelector(targetSelector);
    pane.classList.add('active');

    // If it's the reviews tab, load once
    if (targetSelector === '#tabs-3' && !reviewsLoaded) {
      loadReviews();
      reviewsLoaded = true;
    }

  });
});

function loadReviews() {
  const list = document.getElementById('reviews-list');
  const pid = document.getElementById('product-data').dataset.productId;
  list.innerHTML = '<p class="reviews-loading">Yorumlar yükleniyor…</p>';

  fetch(`tools/action/get_product_reviews.php?product_id=${pid}`)
    .then(response => {
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      return response.json().catch(err => {
        console.error('Invalid JSON:', err);
        return [];
      });
    })
    .then(data => {
      if (!Array.isArray(data) || data.length === 0) {
        list.innerHTML = '<p class="no-reviews">Bu ürün için henüz değerlendirme yapılmamış.</p>';
        return;
      }
      list.innerHTML = '';
      data.forEach(r => {
        const rating = parseFloat(r.rating);
        const rounded = Math.round(rating * 2) / 2;
        let stars = '';
        for (let i = 1; i <= 5; i++) {
          if (rounded >= i) stars += '★';
          else if (rounded + 0.5 === i) stars += '☆';
          else stars += '☆';
        }
        const entry = document.createElement('div');
        entry.className = 'review-entry';
        entry.innerHTML = `
            <div class="review-rating">${stars}
              <span class="review-score">(${rating} puan)</span>
            </div>
            <p class="review-comment">${r.comment}</p>
            <div class="review-meta">– ${r.mail}, ${new Date(r.created_at).toLocaleDateString('tr-TR')}</div>
          `;
        list.appendChild(entry);
      });
    })
    .catch(err => {
      console.error('Fetch error:', err);
      list.innerHTML = '<p class="reviews-error">Yorumlar alınamadı.</p>';
    });
}

document.querySelector('.product__details__rating').addEventListener('click', () => {
  // Find the Reviews tab link by href or text
  const reviewsTabLink = document.querySelector('a.nav-link[href="#tabs-3"]');
  if (!reviewsTabLink) return;

  // Activate the Reviews tab link
  // Remove 'active' from all tab links and panes
  document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
  document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));

  reviewsTabLink.classList.add('active'); // Activate the Reviews tab link

  // Activate the Reviews tab content pane
  const reviewsPane = document.querySelector('#tabs-3');
  if (reviewsPane) reviewsPane.classList.add('active');

  // Optionally, scroll to the reviews tab content
  reviewsPane.scrollIntoView({ behavior: 'smooth' });

  // If you have a function to load reviews dynamically, call it here
  if (typeof loadReviews === 'function') {
    loadReviews();
  }
});

document.querySelectorAll('.size-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    // You can also do something here like update price or product variant based on selected size
  });
});


document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.coupon-button').forEach(btn => {
    btn.addEventListener('click', () => {
      const couponId = btn.dataset.couponId;

      fetch('tools/action/coupon-add-to-user.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `coupon_id=${encodeURIComponent(couponId)}`
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            btn.textContent = 'Added ✔';
            btn.disabled = true;
            showToast('Coupon added successfully');
          } else {
            showToast(data.message || 'An error occurred.',true);
          }
        })
        .catch(err => showToast('Add coupon error:'+err,true));
    });
  });
});
