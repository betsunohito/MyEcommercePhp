document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll('.tab');
  const loginForm = document.getElementById('login-form');
  const signupForm = document.getElementById('signup-form');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      // Remove active state from all tabs and hide forms
      tabs.forEach(t => t.classList.remove('active'));
      loginForm.classList.remove('active');
      signupForm.classList.remove('active');

      // Activate the selected tab and corresponding form
      tab.classList.add('active');
      if (tab.dataset.tab === "login") {
        loginForm.classList.add('active');
      } else {
        signupForm.classList.add('active');
      }
    });
  });

  if (activeTab == "signup") {
    document.querySelector(".tab[data-tab='signup']").click();
  } else {
    document.querySelector(".tab[data-tab='login']").click();
  }



});