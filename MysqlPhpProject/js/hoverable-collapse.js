document.addEventListener('DOMContentLoaded', function () {
  const body = document.body;
  const navItems = document.querySelectorAll('.sidebar .nav-item');
  const toggleBtn = document.querySelector('[data-toggle="minimize"]');

  // ✅ Restore sidebar state
  const savedState = localStorage.getItem('sidebarState');
  if (savedState === 'collapsed') {
    body.classList.add('sidebar-icon-only');
  }

  // ✅ Hook into existing toggle button
  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      setTimeout(() => {
        const isCollapsed = body.classList.contains('sidebar-icon-only');
        localStorage.setItem('sidebarState', isCollapsed ? 'collapsed' : 'expanded');
      }, 50); // slight delay to let the UI update before reading class
    });
  }

  // ✅ Hover behavior for desktop only
  if (!('ontouchstart' in window)) {
    navItems.forEach(item => {
      item.addEventListener('mouseenter', () => {
        if (body.classList.contains('sidebar-icon-only')) {
          if (body.classList.contains('sidebar-fixed')) {
            body.classList.remove('sidebar-icon-only');
          } else {
            item.classList.add('hover-open');
          }
        }
      });
      item.addEventListener('mouseleave', () => {
        item.classList.remove('hover-open');
      });
    });
  }
});
