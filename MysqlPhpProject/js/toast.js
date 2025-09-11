window.showToast = function(message, isError = false, duration = 3000) {
  let container = document.getElementById("status-container");
  if (!container) {
    container = document.createElement("div");
    container.id = "status-container";
    container.className = "status-container";
    document.body.appendChild(container);
  }

  const toast = document.createElement("div");
  toast.className = "toast" + (isError ? " error" : "");
  toast.setAttribute('role', 'status');
  toast.setAttribute('aria-live', 'polite');
  toast.textContent = message;

  container.appendChild(toast);

  // trigger enter animation
  requestAnimationFrame(() => toast.classList.add('show'));

  // auto-hide
  const remove = () => {
    toast.classList.add('hide');
    toast.addEventListener('transitionend', () => toast.remove(), { once: true });
  };
  const t = setTimeout(remove, duration);

  // close on click (optional)
  toast.addEventListener('click', () => { clearTimeout(t); remove(); });
};
