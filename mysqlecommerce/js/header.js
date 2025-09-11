document.addEventListener('DOMContentLoaded', function () {
  const input = document.getElementById('searchInput');
  const resultBox = document.getElementById('searchResults');

  let timeout = null;

  const openResults = () => {
    resultBox.style.display = 'block';
    input.setAttribute('aria-expanded', 'true');
  };

  const closeResults = () => {
    resultBox.style.display = 'none';
    input.setAttribute('aria-expanded', 'false');
    // Optional: clear items when closing
    // resultBox.innerHTML = '';
  };

  // Hide when clicking/tapping outside
  document.addEventListener('pointerdown', (e) => {
    if (!resultBox.contains(e.target) && e.target !== input) {
      closeResults();
    }
  });

  // Hide on Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeResults();
  });

  // Show again when focusing the input (if there are items)
  input.addEventListener('focus', () => {
    if (resultBox.innerHTML.trim()) openResults();
  });

  input.addEventListener('input', function () {
    const query = input.value.trim();

    clearTimeout(timeout);

    if (query.length < 2) {
      // Too short: just close it
      closeResults();
      resultBox.innerHTML = '';
      return;
    }

    timeout = setTimeout(() => {
      fetch(`tools/action/search-autocomplete.php?term=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
          resultBox.innerHTML = '';

          if (!Array.isArray(data) || data.length === 0) {
            resultBox.innerHTML = '<div class="result-item">No results found</div>';
            openResults();
            return;
          }

          data.forEach(item => {
            const div = document.createElement('div');
            div.className = 'result-item';
            div.textContent = item.label;

            div.addEventListener('click', () => {
              let url = '/mysqlecommerce/';

              switch (item.type) {
                case 'product':
                  url += `product-detail.php?id=${item.id}`;
                  break;
                case 'category':
                  url += `product-grid.php?category=${item.id}`;
                  break;
                case 'sub-category':
                  url += `product-grid.php?sub_category_ids[]=${item.id}`;
                  break;
                case 'tet-category':
                  url += `product-grid.php?tertiary_category_ids[]=${item.id}`;
                  break;
                default:
                  return;
              }

              // Close before navigating (optional)
              closeResults();
              window.location.href = url;
            });

            resultBox.appendChild(div);
          });

          openResults();
        })
        .catch(err => {
          console.error('Fetch error:', err);
          closeResults();
        });
    }, 300); // debounce
  });
});
