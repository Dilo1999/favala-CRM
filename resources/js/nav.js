/**
 * Mobile navigation overlay + Solutions accordion.
 * Mirrors the React Header UX using Tailwind classes.
 */
function initNav() {
  const toggleBtn = document.querySelector('[data-mobile-menu-toggle]');
  const overlay = document.querySelector('[data-mobile-menu-overlay]');
  if (!toggleBtn || !overlay) return;

  const iconOpen = toggleBtn.querySelector('[data-mobile-menu-icon="open"]');
  const iconClose = toggleBtn.querySelector('[data-mobile-menu-icon="close"]');

  const solutionsToggle = overlay.querySelector('[data-mobile-solutions-toggle]');
  const solutionsChevron = overlay.querySelector('[data-mobile-solutions-chevron]');
  const solutionsPanel = overlay.querySelector('[data-mobile-solutions-panel]');

  const closeLinks = overlay.querySelectorAll('[data-mobile-menu-close]');

  function setOverlayOpen(isOpen) {
    toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

    if (iconOpen) iconOpen.classList.toggle('hidden', isOpen);
    if (iconClose) iconClose.classList.toggle('hidden', !isOpen);

    overlay.classList.toggle('translate-x-full', !isOpen);
    overlay.classList.toggle('opacity-0', !isOpen);
    overlay.classList.toggle('pointer-events-none', !isOpen);

    // Lock background scroll when open
    document.body.classList.toggle('overflow-hidden', isOpen);

    // Reset accordion on open (match React behavior)
    if (isOpen) setSolutionsExpanded(false);
  }

  function setSolutionsExpanded(expanded) {
    if (!solutionsToggle || !solutionsPanel) return;

    solutionsToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    if (solutionsChevron) solutionsChevron.classList.toggle('rotate-180', expanded);

    solutionsPanel.classList.toggle('max-h-0', !expanded);
    solutionsPanel.classList.toggle('opacity-0', !expanded);
    solutionsPanel.classList.toggle('max-h-[500px]', expanded);
    solutionsPanel.classList.toggle('opacity-100', expanded);
  }

  toggleBtn.addEventListener('click', () => {
    const isOpen = toggleBtn.getAttribute('aria-expanded') === 'true';
    setOverlayOpen(!isOpen);
  });

  // Click outside nav to close
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) setOverlayOpen(false);
  });

  // Escape closes
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && toggleBtn.getAttribute('aria-expanded') === 'true') {
      setOverlayOpen(false);
    }
  });

  if (solutionsToggle) {
    solutionsToggle.addEventListener('click', () => {
      const expanded = solutionsToggle.getAttribute('aria-expanded') === 'true';
      setSolutionsExpanded(!expanded);
    });
  }

  closeLinks.forEach((link) => {
    link.addEventListener('click', () => setOverlayOpen(false));
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initNav);
} else {
  initNav();
}

