document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('login-form');

  if (!form) {
    console.warn('⚠️ login-form not found in DOM.');
    return;
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault(); // Prevent default form submission

    const formData = new FormData(form);

    console.log('📤 Submitting login...');
    console.log('Form Data:', Object.fromEntries(formData.entries()));

    fetch('action-php/admin_login.php', {
      method: 'POST',
      body: formData
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          console.log('✅ Login successful:', data);

          // Store login info in sessionStorage
          sessionStorage.setItem('admin', JSON.stringify(data));

          // Redirect to index.php
          window.location.href = './index.php';
        } else {
          console.warn('❌ Login failed:', data.message);
          alert(data.message || 'Login failed. Please try again.');
        }
      })
      .catch(error => {
        console.error('⚠️ Login error:', error);
        alert('An error occurred during login. Please try again.');
      });
  });
});
