function setupAutocomplete(inputSelector, suggestionsSelector, type) {
  // Accept "#id" | ".class" | "id"
  const input =
    inputSelector.startsWith('#') || inputSelector.startsWith('.')
      ? document.querySelector(inputSelector)
      : document.getElementById(inputSelector);

  const suggestionsBox = document.querySelector(suggestionsSelector);

  if (!input || !suggestionsBox) {
    console.warn('setupAutocomplete: missing input or suggestions element for', inputSelector, suggestionsSelector);
    return;
  }

  const wrapper = input.closest('.autocomplete-wrapper') || input.parentElement;

  // Build hidden input id from the REAL input id (fallback to cleaned selector)
  const base = input.id || inputSelector.replace(/^[#.]/, '');
  const hiddenId = `${base}_id`;            // e.g. "category1-input_id"
  const hiddenName = `${type}_id`;          // e.g. "category_id"

  // Find or create the hidden input
  let hiddenInput = wrapper.querySelector(`#${CSS.escape(hiddenId)}`);
  if (!hiddenInput) {
    hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.id = hiddenId;              // IMPORTANT: set id
    hiddenInput.name = hiddenName;          // useful for form posts
    wrapper.appendChild(hiddenInput);
  }

  let currentIndex = -1;

  function clearSuggestions() {
    suggestionsBox.innerHTML = '';
    suggestionsBox.classList.remove('show');
    currentIndex = -1;
  }

  function highlightSuggestion(index) {
    const items = suggestionsBox.querySelectorAll('.suggestion-item');
    items.forEach((el, i) => el.classList.toggle('highlighted', i === index));
  }

  function showSuggestions(val, parentId = null) {
    clearSuggestions();
    if (!val) return;

    let url = `action-php/get_suggestion.php?type=${encodeURIComponent(type)}&q=${encodeURIComponent(val)}`;
    if (parentId !== null && parentId !== undefined) {
      url += `&parent_id=${encodeURIComponent(parentId)}`;
    }


    fetch(url)
      .then(async (response) => {
        if (!response.ok) {
          console.error('❌ Response not OK:', await response.text());
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then((list) => {
        if (!Array.isArray(list) || list.length === 0) return;

        list.forEach(({ id, name }) => {
          const div = document.createElement('div');
          div.textContent = name;
          div.setAttribute('role', 'option');
          div.classList.add('suggestion-item');
          div.dataset.id = id;
          div.dataset.name = name;

          div.addEventListener('mousedown', (e) => {
            e.preventDefault();
            input.value = name;
            hiddenInput.value = id;
            clearSuggestions();
          });

          suggestionsBox.appendChild(div);
        });

        suggestionsBox.classList.add('show');
        currentIndex = 0;
        highlightSuggestion(currentIndex);
      })
      .catch((error) => {
        console.error('⚠️ Fetch error:', error);
        clearSuggestions();
      });
  }

  input.addEventListener('input', () => {
    hiddenInput.value = ''; // clear any previous ID
    let parentId = null;
    if (type === 'subcategory') {
      parentId = document.getElementById('category1-input_id')?.value || null;
    } else if (type === 'tertiary') {
      parentId = document.getElementById('category2-input_id')?.value || null;
    }
    showSuggestions(input.value.trim(), parentId);
  });

  input.addEventListener('keydown', (e) => {
    const items = suggestionsBox.querySelectorAll('.suggestion-item');
    if (!items.length) return;

    if (e.key === 'ArrowDown' || e.key === 'Tab') {
      e.preventDefault();
      currentIndex = (currentIndex + 1) % items.length;
      highlightSuggestion(currentIndex);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      currentIndex = (currentIndex - 1 + items.length) % items.length;
      highlightSuggestion(currentIndex);
    } else if (e.key === 'Enter') {
      e.preventDefault();
      if (currentIndex >= 0 && items[currentIndex]) {
        const selected = items[currentIndex];
        input.value = selected.dataset.name;
        hiddenInput.value = selected.dataset.id;
        clearSuggestions();
      }
    }
  });

  input.addEventListener('blur', () => setTimeout(clearSuggestions, 150));
}

// You can keep your existing calls (raw ids are fine now)
window.addEventListener('DOMContentLoaded', () => {
  setupAutocomplete('category1-input', '.category1-suggestions', 'category');
  setupAutocomplete('category2-input', '.category2-suggestions', 'subcategory');
  setupAutocomplete('category3-input', '.category3-suggestions', 'tertiary');
  setupAutocomplete('category-main-input', '.category-main-suggestions', 'category');
  setupAutocomplete('type-category-input', '.type-category-suggestions', 'category');
});


document.querySelector('button.add').addEventListener('click', async (e) => {
  e.preventDefault();

  const catInput = document.querySelector('.category1-input').value.trim();
  const subInput = document.querySelector('.category2-input').value.trim();
  const terInput = document.querySelector('.category3-input').value.trim();
  const catId = document.querySelector('#category1-input_id').value.trim();
  const subId = document.querySelector('#category2-input_id').value.trim();
  const terId = document.querySelector('#category3-input_id').value.trim();


  const payload = {
    category: catId !== '' ? catId : catInput,
    subcategory: subId !== '' ? subId : subInput,
    tertiary: terId !== '' ? terId : terInput
  };

  const messageBox = document.getElementById('category-save-message');
  messageBox.textContent = ''; // Clear previous message
  messageBox.className = '';   // Reset class

  if (payload.category === '') {
    messageBox.textContent = '⚠️ Category is required.';
    messageBox.classList.add('text-danger');
    return;
  }

  try {
    const response = await fetch('action-php/add_category_auto.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
      throw new Error(result.message || 'Unknown error');
    }

    if (result.category_id) {
      document.querySelector('#category1-input_id').value = result.category_id;
    }
    if (result.subcategory_id) {
      document.querySelector('#category2-input_id').value = result.subcategory_id;
    }
    messageBox.textContent = result.message;
    messageBox.classList.remove('text-success', 'text-danger');
    messageBox.classList.add('text-success'); // Or 'text-danger' if failure


  } catch (err) {
    console.error('Error saving categories:', err);
    messageBox.textContent = '⚠️ Save failed: ' + err.message;
    messageBox.classList.add('text-danger');
  }
});

document.querySelector('.type-add-btn').addEventListener('click', () => {
  const categoryId = document.querySelector('.type-category-hidden-id').value.trim();
  const typeName = document.querySelector('.type-input').value.trim();

  if (!categoryId || !typeName) {
    alert("Both category and type name are required.");
    return;
  }

  fetch('action-php/add_type.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: `category_id=${encodeURIComponent(categoryId)}&type_name=${encodeURIComponent(typeName)}`
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Type added successfully!');
        document.querySelector('.type-input').value = '';
      } else {
        alert('Error: ' + (data.message || 'Could not add type.'));
      }
    })
    .catch(error => {
      console.error('Request failed:', error);
      alert('Something went wrong.');
    });
});

document.getElementById('product_category').addEventListener('input', function () {
  const value = this.value.trim();
  const productList = document.querySelector('.product-list');

  if (value.length < 5) {
    productList.innerHTML = '';
    return;
  }

  // Show loading message immediately (no fade to avoid delay)
  // Show skeleton loaders (3 placeholder cards)
  productList.innerHTML = `
  <div class="product-item skeleton">
    <div class="image-placeholder"></div>
    <div class="info-placeholder">
      <div class="title-placeholder"></div>
      <div class="text-placeholder"></div>
      <div class="controls-placeholder"></div>
    </div>
  </div>
  <div class="product-item skeleton">
    <div class="image-placeholder"></div>
    <div class="info-placeholder">
      <div class="title-placeholder"></div>
      <div class="text-placeholder"></div>
      <div class="controls-placeholder"></div>
    </div>
  </div>
  <div class="product-item skeleton">
    <div class="image-placeholder"></div>
    <div class="info-placeholder">
      <div class="title-placeholder"></div>
      <div class="text-placeholder"></div>
      <div class="controls-placeholder"></div>
    </div>
  </div>
`;


  fetch('action-php/search_from_catalog.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'term=' + encodeURIComponent(value)
  })
    .then(res => res.json())
    .then(products => {
      // Start fade out
      productList.classList.add('fade-out');

      setTimeout(() => {
        productList.innerHTML = '';

        if (!Array.isArray(products) || !products.length) {
          productList.innerHTML = '<div class="no-results">No products found.</div>';
        } else {
          products.forEach(p => {
            const productId = p.product_id || '';
            const productName = p.product_name || 'Unnamed';
            const productBrand = p.product_brand || 'Unnamed';
            const productGTIN = p.product_GTIN || 'N/A';
            const productLink = p.product_link || 'default';
            const imageFile = p.image_filename || 'default.jpg';
            const types = Array.isArray(p.available_types) ? p.available_types : [];

            const optionsHtml = types.length > 0
              ? types.map(type =>
                `<option value="${type.type_id}">${type.type_name}</option>`
              ).join('')
              : '';
            const item = document.createElement('div');
            item.className = 'product-item';
            item.setAttribute('data-product-id', productId);

            item.innerHTML = `
              <img src="/uploads/products/${productLink}/${imageFile}" alt="Product Image">
              <div class="product-info">
                <h4 class="product-title">${productBrand} ${productName}</h4>
                <p>GTIN: ${productGTIN}</p>
                <div class="product-controls">
                  <select class="product-type">
                    <option value="0">Select type</option>
                    ${optionsHtml}
                  </select>
                  <input type="number" class="product-qty" min="1" value="1" placeholder="Qty">
                  <input type="number" class="product-price" min="0" step="0.01" value="" placeholder="Price">
                  <button class="add-product-btn">Add</button>
                </div>
              </div>
            `;

            productList.appendChild(item);
          });
        }

        // Fade back in
        productList.classList.remove('fade-out');
      }, 500); // match this to your CSS transition duration

    })
    .catch(err => {
      console.error('Search error:', err);
      productList.innerHTML = '<div class="error-msg">Error loading products.</div>';
    });
});



document.querySelector('.product-list').addEventListener('click', function (e) {
  if (e.target && e.target.classList.contains('add-product-btn')) {
    const productItem = e.target.closest('.product-item');
    const productId = productItem.getAttribute('data-product-id');
    const typeId = productItem.querySelector('.product-type').value || '';
    const quantity = productItem.querySelector('.product-qty').value.trim();
    const price = productItem.querySelector('.product-price').value.trim();

    // Optional: validation
    if (!productId || !quantity || !price) {
      showToast('Please enter quantity and price.', true);
      return;
    }

    const postData = new URLSearchParams();
    postData.append('product_id', productId);
    postData.append('product_type', typeId);
    postData.append('product_quantity', quantity);
    postData.append('product_price', price);

    fetch('action-php/add_product_price.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: postData.toString()
    })
      .then(res => res.text())   // Get raw response text first
      .then(text => {   // Debug: see exactly what server sent
        return JSON.parse(text);               // Parse JSON manually
      })
      .then(data => {
        if (data.success) {
          showToast('Price added successfully!', false);
        } else {
          showToast(data.message || 'Something went wrong', true);
        }
      })
      .catch(err => {
        showToast('Fetch or JSON parse error: ' + err.message, true);
      });
  }
});



document.querySelector('.brand-add-btn').addEventListener('click', () => {
  const categoryId = document.querySelector('.category-main-hidden-id').value;
  const brandName = document.querySelector('.brand-input').value;
  const responseBox = document.getElementById('brand-response');

  if (!categoryId || !brandName.trim()) {
    responseBox.textContent = "Please select a category and enter a brand name.";
    responseBox.className = "alert alert-warning mt-3";
    responseBox.classList.remove("d-none");
    return;
  }

  const formData = new FormData();
  formData.append("category_main_id", categoryId);
  formData.append("brand_name", brandName.trim());

  fetch('action-php/add_brand.php', {
    method: 'POST',
    body: formData
  })
    .then(response => response.text())
    .then(text => {
      responseBox.textContent = text;
      if (text.includes("successfully")) {
        responseBox.className = "alert alert-success mt-3";
      } else {
        responseBox.className = "alert alert-danger mt-3";
      }
      responseBox.classList.remove("d-none");
    })
    .catch(error => {
      responseBox.textContent = "An unexpected error occurred.";
      responseBox.className = "alert alert-danger mt-3";
      responseBox.classList.remove("d-none");
    });
});
