// toast.js
window.showToast = function(message, isError = false) {
  let container = document.getElementById("status-container");

  if (!container) {
    container = document.createElement("div");
    container.id = "status-container";
    container.className = "status-container";
    document.body.appendChild(container);
  }

  const toast = document.createElement("div");
  toast.className = "toast" + (isError ? " error" : "");
  toast.textContent = message;

  container.appendChild(toast);

  setTimeout(() => {
    toast.remove();
  }, 3000);
};
