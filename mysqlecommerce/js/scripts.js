document.addEventListener("DOMContentLoaded", function () {
  const nextBtnforproductpreview = document.querySelector(".product-preview .next-btn");
  const previousBtnforproductpreview = document.querySelector(".product-preview .previous-btn");
  const storiesContentforproductpreview = document.querySelector(".product-preview .pg-container");

  if (nextBtnforproductpreview && previousBtnforproductpreview && storiesContentforproductpreview) {
    nextBtnforproductpreview.addEventListener("click", () => {
      storiesContentforproductpreview.scrollLeft += 300;
    });

    previousBtnforproductpreview.addEventListener("click", () => {
      storiesContentforproductpreview.scrollLeft -= 300;
    });

    storiesContentforproductpreview.addEventListener("scroll", () => {
      if (storiesContentforproductpreview.scrollLeft <= 24) {
        previousBtnforproductpreview.classList.remove("active");
      } else {
        previousBtnforproductpreview.classList.add("active");
      }

      let maxScrollValue =
        storiesContentforproductpreview.scrollWidth - storiesContentforproductpreview.clientWidth - 24;

      if (storiesContentforproductpreview.scrollLeft >= maxScrollValue) {
        nextBtnforproductpreview.classList.remove("active");
      } else {
        nextBtnforproductpreview.classList.add("active");
      }
    });
  } else {
    console.warn("Some elements for product preview scrolling are missing.");
  }
});


function showSidebar() {
  const sidebar = document.querySelector('.sidebar');
  sidebar.style.visibility = 'visible';
}

function hideSidebar() {
  const sidebar = document.querySelector('.sidebar');
  sidebar.style.visibility = 'hidden';
}

function toggleFavorite(productId) {
  fetch('tools/action/favorites-toggle-action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'product_id=' + productId
  })
    .then(response => response.json())  // Change .json() to .text() for debugging
    .then(data => {
      if (data.success == true) {
        const iconElements = document.querySelectorAll('.fav-toggle[data-product-id="' + productId + '"] img');

        iconElements.forEach((iconElement) => {
          const currentIcon = iconElement.src.split('/').pop();
          iconElement.src = (currentIcon === 'star.svg') ? 'images/cancel.svg' : 'images/star.svg';
        });

        showToast("Favorite toggled successfully!", false);
      }
      else {
        showToast("Failed to toggle favorite. Please login.", true);
      }

    })
    .catch(error => console.error("Fetch error:", error));
}

function addToCart(productId) {
  const qtyInput = document.getElementById('qty-' + productId);
  const quantity = qtyInput ? parseInt(qtyInput.value) || 1 : 1;

  const addBtn = document.getElementById('add-to-cart-' + productId);
  const sellerId = addBtn?.dataset.sellerId;
  const typeId = parseInt(addBtn?.dataset.typeId || "0");
  const hasTypes = addBtn?.dataset.hasTypes === "1"; // string comparison

  // ✅ Block if type is required but not selected properly
  if (hasTypes && typeId === 0) {
    showToast("Lütfen bir ürün tipi seçiniz.", true);
    return;
  }

  if (!sellerId) {
    showToast("Satıcı bilgisi eksik.", true);
    return;
  }

  const payload = new URLSearchParams({
    product_id: productId,
    quantity: quantity,
    seller_id: sellerId,
    type_id: typeId
  });

  fetch('tools/action/shoppingcart-add-action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: payload.toString()
  })
    .then(response => response.json())
    .then(data => {
      if (data.success === true) {
        showToast("Ürün sepete eklendi!", false);
        updateCartCount();
      } else {
        showToast(data.message || "Sepete ekleme başarısız.", true);
      }
    })
    .catch(error => {
      showToast("Ağ hatası oluştu.", true);
    });
}


document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".qty-toggle").forEach(btn => {
    btn.addEventListener("click", function () {
      let input = this.parentElement.querySelector("input");
      let currentValue = parseInt(input.value) || 1;

      if (this.classList.contains("inc")) {
        input.value = currentValue + 1;
      } else if (this.classList.contains("dec") && currentValue > 1) {
        input.value = currentValue - 1;
      }
    });
  });
});

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".cart-item").forEach(item => {
    const cartId = item.getAttribute("data-cart-id");

    const qtyInput = item.querySelector("input");
    const decBtn = item.querySelector(".dec");
    const incBtn = item.querySelector(".inc");

    // Decrease quantity
    decBtn.addEventListener("click", () => {
      const newQty = parseInt(qtyInput.value) - 1;
      if (newQty > 0) {
        updateQuantity(cartId, newQty, item);
      }
    });

    // Increase quantity
    incBtn.addEventListener("click", () => {
      const newQty = parseInt(qtyInput.value) + 1;
      updateQuantity(cartId, newQty, item);
    });

    // Manual input
    qtyInput.addEventListener("input", function () {
      const newQty = parseInt(this.value);
      if (!isNaN(newQty) && newQty > 0) {
        updateQuantity(cartId, newQty, item);
      }
    });
  });

  function updateQuantity(cartId, newQty, item) {
    const formData = new FormData();
    formData.append("cart_id", cartId);
    formData.append("quantity", newQty);

    fetch("tools/action/shoppingcart-update-quantity.php", {
      method: "POST",
      body: formData
    })
      .then(response => response.text())
      .then(data => {
        try {
          const jsonData = JSON.parse(data);

          if (jsonData.success) {
            const newQuantity = jsonData.new_quantity;
            const totalPrice = jsonData.total_price;
            const cartTotal = jsonData.cart_total;

            const inputField = item.querySelector("input");
            const totalField = document.querySelector(`.cart-item-price[data-cart-id="${cartId}"]`);
            const cartTotalField = document.querySelector(".cart-total");

            // ✅ Update quantity input
            if (inputField && typeof newQuantity !== "undefined") {
              inputField.value = newQuantity;
            }

            // ✅ Update per-item total price
            if (totalField && typeof totalPrice !== "undefined") {
              totalField.textContent = "₺" + totalPrice;
            }

            // ✅ Update cart total safely
            if (cartTotalField && typeof cartTotal !== "undefined") {
              cartTotalField.textContent = "₺" + cartTotal;
            }

            // ✅ Call total recalculation if needed
            if (typeof getTotalAmount === "function" && typeof cartTotal === "undefined") {
              // Only recalculate manually if server didn’t return cartTotal
              getTotalAmount();
            }

          } else {
            console.warn("Server error:", jsonData.message);
          }
        } catch (error) {
          console.error("JSON parse error:", error);
        }
      })
      .catch(error => {
        console.error("Fetch failed:", error);
      });
  }
});



function getTotalAmount() {
  fetch("tools/action/shoppingcart-total.php", {
    method: "POST"
  })
    .then(response => response.json())  // Parse the JSON response
    .then(data => {

      if (data.success) {
        // Format the total amount as currency
        const formattedTotal = new Intl.NumberFormat("en-US", {
          style: "currency",
          currency: "USD"
        }).format(data.total);

        // Update the cart total element with the formatted value
        document.querySelector(".cart-total").textContent = formattedTotal;
      } else {
        console.error("API Error Response:", data);
        alert("Failed to get total amount.");
      }
    })
    .catch(error => console.error("Fetch error:", error));
}

function showLoadingOverlay() {
  document.getElementById('loading-overlay').style.display = 'flex';
  console.log('Action in progress...');
  setTimeout(function () {
    document.getElementById('loading-overlay').style.display = 'none';
  }, 500);
}



document.addEventListener('DOMContentLoaded', function () {
  document.body.addEventListener('click', function (e) {
    if (e.target.closest('.trash')) {
      const trashBtn = e.target.closest('.trash');
      const productId = trashBtn.dataset.productId;
      const typeId = trashBtn.dataset.typeId;

      if (!productId || typeId === undefined) {
        showToast("Invalid input.", false);
        return;
      }

      fetch('tools/action/shoppingcart-delete-item.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `product_id=${encodeURIComponent(productId)}&type_id=${encodeURIComponent(typeId)}`
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            reloadCartProducts();
            updateCartCount();

            if (data.coupon_removed) {
              const couponLine = document.getElementById("applied-coupon-line");
              if (couponLine) couponLine.remove();
              showToast("❌ Coupon removed automatically.");
            }
          } else {
            alert(data.message || 'Error deleting item.');
          }
        })
        .catch(error => {
          console.error('Delete failed:', error);
        });
    }
  });
});

function updateCartCount() {
  fetch('tools/action/cart-count-action.php')
    .then(response => response.json())
    .then(data => {
      if (data.count !== undefined) {
        document.querySelector('.cart-count').textContent = data.count;
      } else {
        console.error('Unexpected response:', data);
      }
    })
    .catch(error => {
      console.error('Error fetching cart count:', error);
    });
}
function reloadCartProducts() {
  fetch('tools/action/shoppingcart_product_fill.php')
    .then(response => response.text())
    .then(php => {
      document.querySelector('#cart-products-container').innerHTML = php;
    })
    .catch(error => {
      console.error('Error loading cart products:', error);
    });
}

document.querySelectorAll('.pg-stars').forEach(container => {
  const rating = parseFloat(container.dataset.rating);
  const stars = container.querySelectorAll('.star');

  stars.forEach((star, i) => {
    const index = i + 1;
    if (index <= Math.floor(rating)) {
      star.classList.add('filled');
    } else if (index - rating <= 0.5) {
      star.classList.add('half');
    }
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const slideContainer = document.querySelector('#leftSlider .psc-slides');
  const dotsContainer = document.getElementById('sliderDots');
  let slides = [];
  let currentIndex = 0;
  let autoSlideInterval;

  if (!slideContainer || !dotsContainer) {
    console.warn('Slider elements not found, slider will not initialize.');
    return;
  }

  slides = Array.from(slideContainer.children);
  if (slides.length === 0) {
    console.warn('No slides found inside the container.');
    return;
  }

  function updateSlider() {
    slideContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
    Array.from(dotsContainer.children).forEach((dot, i) => {
      dot.classList.toggle('active', i === currentIndex);
    });
  }

  function restartAutoSlide() {
    clearInterval(autoSlideInterval);
    autoSlideInterval = setInterval(window.nextSlide, 5000);
  }

  window.nextSlide = function () {
    currentIndex = (currentIndex + 1) % slides.length;
    updateSlider();
    restartAutoSlide(); // reset on manual use
  };

  window.prevSlide = function () {
    currentIndex = (currentIndex - 1 + slides.length) % slides.length;
    updateSlider();
    restartAutoSlide(); // reset on manual use
  };

  // Create dots
  slides.forEach((_, i) => {
    const dot = document.createElement('div');
    dot.className = i === 0 ? 'psc-dot active' : 'psc-dot';
    dot.addEventListener('click', () => {
      currentIndex = i;
      updateSlider();
      restartAutoSlide(); // reset on manual use
    });
    dotsContainer.appendChild(dot);
  });

  // Start auto sliding
  autoSlideInterval = setInterval(window.nextSlide, 5000);
});



// Right card scroller logic
const cardContainer = document.getElementById('cardContainer');
if (cardContainer) {
  function nextCard() {
    cardContainer.scrollBy({ left: 220, behavior: 'smooth' });
  }
  function prevCard() {
    cardContainer.scrollBy({ left: -220, behavior: 'smooth' });
  }
  // You can attach nextCard and prevCard to buttons here if needed
} else {
  console.warn('Card container not found, card scroller will not initialize.');
}


function openPreviewModal(cardId, sellerId) {
  const modal = document.getElementById('previewModal');
  const modalBody = document.getElementById('modalBody');
  const closeBtn = modal.querySelector('.close-btn');

  modal.style.display = 'flex';
  modalBody.innerHTML = 'Loading...';

  const url = `tools/product-preview.php?id=${cardId}&seller_id=${sellerId}`;

  fetch(url)
    .then(response => {
      if (!response.ok) {
        throw new Error('Failed to load preview.');
      }
      return response.text();
    })
    .then(html => {
      modalBody.innerHTML = html;

      setTimeout(() => {
        initPopupCarousel(); // ✅ call here, after modal is visible
      }, 0); // Zero delay ensures DOM is updated first
    })
    .catch(error => {
      modalBody.innerHTML = `<p style="color:red;">${error.message}</p>`;
    });

  closeBtn.onclick = () => {
    modal.style.display = 'none';
    modalBody.innerHTML = '';
  };

  window.onclick = null;
  window.onclick = event => {
    if (event.target === modal) {
      modal.style.display = 'none';
      modalBody.innerHTML = '';
    }
  };
}

function refreshPreviewModal(cardId, typeId) {
  const modal = document.getElementById('previewModal');
  const modalBody = document.getElementById('modalBody');
  const closeBtn = modal.querySelector('.close-btn');

  modal.style.display = 'flex';
  modalBody.innerHTML = 'Loading...';

  const url = `tools/product-preview.php?id=${cardId}&type=${typeId}`;

  fetch(url)
    .then(response => {
      if (!response.ok) throw new Error('Failed to load preview.');
      return response.text();
    })
    .then(html => {
      modalBody.innerHTML = html;
      setTimeout(() => {
        initPopupCarousel(); // init after modal content loads
      }, 0);
    })
    .catch(error => {
      modalBody.innerHTML = `<p style="color:red;">${error.message}</p>`;
    });

  closeBtn.onclick = () => {
    modal.style.display = 'none';
    modalBody.innerHTML = '';
  };

  window.onclick = event => {
    if (event.target === modal) {
      modal.style.display = 'none';
      modalBody.innerHTML = '';
    }
  };
}

function initPopupCarousel() {
  const images = Array.from(document.querySelectorAll(".product__details__pic__slider img"));
  const mainImg = document.querySelector(".product__details__pic__item img");
  const prevBtn = document.querySelector(".prev-btn");
  const nextBtn = document.querySelector(".nnext-btn");

  if (!images.length || !mainImg || !prevBtn || !nextBtn) {
    console.warn("Popup elements not found");
    return;
  }

  let currentIndex = 0;

  function showImage(index) {
    if (images.length === 0) return;

    if (index < 0) index = images.length - 1;
    if (index >= images.length) index = 0;

    currentIndex = index;

    const newSrc = images[currentIndex].getAttribute("data-imgbigurl") || images[currentIndex].src;
    mainImg.src = newSrc;

    images.forEach(img => img.classList.remove('active'));
    images[currentIndex].classList.add('active');
  }

  // Button events
  prevBtn.addEventListener("click", () => showImage(currentIndex - 1));
  nextBtn.addEventListener("click", () => showImage(currentIndex + 1));

  // Thumbnail clicks
  images.forEach((img, idx) => {
    img.style.cursor = 'pointer';
    img.addEventListener('click', () => showImage(idx));
  });

  // Initialize
  showImage(0);
}

document.addEventListener("DOMContentLoaded", function () {
  const womenPreview = document.querySelector("#women-preview");
  if (!womenPreview) return;

  const nextBtn  = womenPreview.querySelector(".next-btn-women")  || womenPreview.querySelector(".next-btn");
  const prevBtn  = womenPreview.querySelector(".prev-btn-women")  || womenPreview.querySelector(".previous-btn");
  const scroller = womenPreview.querySelector(".pg-container");

  if (nextBtn && prevBtn && scroller) {
    nextBtn.addEventListener("click", () => {
      scroller.scrollLeft += 300;
    });

    prevBtn.addEventListener("click", () => {
      scroller.scrollLeft -= 300;
    });

    const updateButtons = () => {
      if (scroller.scrollLeft <= 24) {
        prevBtn.classList.remove("active");
      } else {
        prevBtn.classList.add("active");
      }

      const maxScroll = scroller.scrollWidth - scroller.clientWidth - 24;
      if (scroller.scrollLeft >= maxScroll) {
        nextBtn.classList.remove("active");
      } else {
        nextBtn.classList.add("active");
      }
    };

    // initialize + keep in sync
    updateButtons();
    scroller.addEventListener("scroll", updateButtons);
  } else {
    console.warn("Kadın section: buttons or container missing.");
  }
});
