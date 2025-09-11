function setupAutocomplete(inputSelector, suggestionsSelector, type, parentType = null) {
  const input = document.querySelector(inputSelector);
  const suggestionsBox = document.querySelector(suggestionsSelector);

  if (!input || !suggestionsBox) return;

  const hiddenInput = input.parentElement.querySelector(`input[name="${type}_id"]`);
  if (!hiddenInput) return;

  let currentIndex = -1;
  let fetchToken = 0;

  function clearSuggestions() {
    suggestionsBox.innerHTML = '';
    suggestionsBox.classList.remove('show');
    currentIndex = -1;
  }

  function highlight(index) {
    const items = suggestionsBox.querySelectorAll('.suggestion-item');
    items.forEach((el, i) => el.classList.toggle('highlighted', i === index));
  }

  function showSuggestions(val) {
    clearSuggestions();
    if (!val) return;

    const currentFetch = ++fetchToken;

    let parentId = '';
    if (parentType) {
      const parentInput = document.querySelector(`input[name="${parentType}_id"]`);
      parentId = parentInput?.value || '';
    }

    const url = `action-php/get_suggestion.php?type=${type}&q=${encodeURIComponent(val)}&parent_id=${parentId}`;

    fetch(url)
      .then(res => res.json())
      .then(list => {
        if (currentFetch !== fetchToken) return;
        if (!Array.isArray(list) || list.length === 0) return;

        list.forEach(item => {
          const div = document.createElement('div');
          div.textContent = item.name;
          div.classList.add('suggestion-item');
          div.dataset.id = item.id;
          div.dataset.name = item.name;

          div.addEventListener('mousedown', () => {
            input.value = item.name;
            hiddenInput.value = item.id;
            clearSuggestions();

            // ✅ Clear child fields using your logic
            if (type === 'category') {
              const subIdInput = document.querySelector('input[name="subcategory_id"]');
              if (subIdInput) subIdInput.value = '';

              const terIdInput = document.querySelector('input[name="tertiary_id"]');
              if (terIdInput) terIdInput.value = '';

              const subTextInput = document.getElementById('coupon-subcategory');
              if (subTextInput) subTextInput.value = '';

              const terTextInput = document.getElementById('coupon-tertiary');
              if (terTextInput) terTextInput.value = '';
            } else if (type === 'subcategory') {
              const terIdInput = document.querySelector('input[name="tertiary_id"]');
              if (terIdInput) terIdInput.value = '';

              const terTextInput = document.getElementById('coupon-tertiary');
              if (terTextInput) terTextInput.value = '';
            }
          });

          suggestionsBox.appendChild(div);
        });

        suggestionsBox.classList.add('show');
        currentIndex = 0;
        highlight(currentIndex);
      })
      .catch(err => {
        console.error('⚠️ Autocomplete fetch error:', err);
        clearSuggestions();
      });
  }

  input.addEventListener('input', () => {
    hiddenInput.value = '';
    showSuggestions(input.value.trim());
  });

  input.addEventListener('keydown', e => {
    const items = suggestionsBox.querySelectorAll('.suggestion-item');
    if (!items.length) return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      currentIndex = (currentIndex + 1) % items.length;
      highlight(currentIndex);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      currentIndex = (currentIndex - 1 + items.length) % items.length;
      highlight(currentIndex);
    } else if (e.key === 'Enter') {
      e.preventDefault();
      if (items[currentIndex]) {
        const selected = items[currentIndex];
        input.value = selected.dataset.name;
        hiddenInput.value = selected.dataset.id;
        clearSuggestions();
      }
    }
  });

  input.addEventListener('blur', () => {
    setTimeout(() => {
      clearSuggestions();
    }, 100);
  });
}

window.addEventListener('DOMContentLoaded', () => {
  setupAutocomplete('.category-main-input', '.category-main-suggestions', 'category');
  setupAutocomplete('.category-sub-input', '.category-sub-suggestions', 'subcategory', 'category');
  setupAutocomplete('.category-ter-input', '.category-ter-suggestions', 'tertiary', 'subcategory');
});


document.addEventListener('DOMContentLoaded', () => {
  const createBtn = document.getElementById('create-coupon-btn');

  createBtn.addEventListener('click', async () => {
    const code = document.getElementById('coupon-code').value.trim();
    const discount = parseFloat(document.getElementById('coupon-discount').value.trim());
    const expiresIn = parseInt(document.getElementById('coupon-expiration').value.trim()) || null;

    const category_id = document.querySelector('input[name="category_id"]').value || null;
    const subcategory_id = document.querySelector('input[name="subcategory_id"]').value || null;
    const tertiary_id = document.querySelector('input[name="tertiary_id"]').value || null;

    // Validate inputs
    if (!code || isNaN(discount)) {
      alert("Please enter a valid coupon code and discount.");
      return;
    }

    const payload = {
      code,
      discount,
      expiresIn,
      category_id,
      subcategory_id,
      tertiary_id
    };

    try {
      const response = await fetch('action-php/coupon_add.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const result = await response.json();
      if (result.success) {
        alert("✅ Coupon added successfully!");
        location.reload(); // reload or update coupon list
      } else {
        alert("❌ Failed: " + (result.error || "Unknown error"));
      }

    } catch (err) {
      console.error("⚠️ Request failed:", err);
      alert("Something went wrong while adding coupon.");
    }
  });
});
