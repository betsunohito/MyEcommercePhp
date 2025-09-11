console.log('Register JS loaded');
document.getElementById('register-form').addEventListener('submit', function(e) {
  e.preventDefault(); // Stop form from refreshing the page

  const form = e.target;
  const formData = new FormData(form);
console.log('Form data:', formData);
  fetch('action-php/admin_register.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text()) // PHP returns plain text
  .then(data => {
    document.getElementById('register-message').innerHTML = data;
    form.reset(); // Optional: clear form after success
  })
  .catch(err => {
    document.getElementById('register-message').innerHTML = '❌ Network error. Try again.';
    console.error('Error:', err);
  });
});
