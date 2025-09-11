// One-time setup (works for existing + future cards)
const grid = document.querySelector('.pg-grid');
if (grid) {
  // Move/hover: pick image by horizontal segment
  grid.addEventListener('mousemove', (e) => {
    const container = e.target.closest('.pg-image-container');
    if (!container || !grid.contains(container)) return;

    const images = container.querySelectorAll('.hover-image');
    const dots = container.closest('.pg-card')?.querySelectorAll('.pg-image-indicators .dot') || [];
    const count = images.length || dots.length;
    if (!count) return;

    const rect = container.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const segmentWidth = rect.width / count || rect.width; // avoid /0
    let index = Math.floor(x / segmentWidth);
    if (index < 0) index = 0;
    if (index > count - 1) index = count - 1;

    images.forEach((img, i) => img.classList.toggle('active', i === index));
    dots.forEach((dot, i) => dot.classList.toggle('active', i === index));
  });

  // Leave a card: reset to first image
  grid.addEventListener('mouseout', (e) => {
    const container = e.target.closest('.pg-image-container');
    if (!container || !grid.contains(container)) return;
    // Only fire when the pointer actually leaves the container (not moving between children)
    if (container.contains(e.relatedTarget)) return;

    const images = container.querySelectorAll('.hover-image');
    const dots = container.closest('.pg-card')?.querySelectorAll('.pg-image-indicators .dot') || [];
    images.forEach((img, i) => img.classList.toggle('active', i === 0));
    dots.forEach((dot, i) => dot.classList.toggle('active', i === 0));
  });
}
