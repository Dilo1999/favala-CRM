/**
 * Scroll-triggered and load animations (Framer Motion–style).
 * Add .animate-on-scroll + data-animate="fadeInUp" (or fadeIn, slideLeft, slideRight, scaleIn).
 * Optional: data-delay="0.2" for stagger. JS adds .in-view when element enters viewport.
 * For above-the-fold content use .animate-now + data-animate; .played is added after a tick.
 */
function initAnimations() {
  const scrollElements = document.querySelectorAll('.animate-on-scroll');
  const nowElements = document.querySelectorAll('.animate-now');

  // Apply data-delay as CSS var for animation-delay
  function applyDelay(el) {
    const delay = el.getAttribute('data-delay');
    if (delay != null) el.style.setProperty('--delay', delay);
  }

  scrollElements.forEach(applyDelay);
  nowElements.forEach(applyDelay);

  // Run "animate now" (hero, etc.) after a short tick
  requestAnimationFrame(() => {
    nowElements.forEach((el) => el.classList.add('played'));
  });

  // Intersection Observer for scroll-triggered animations (once)
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('in-view');
        }
      });
    },
    {
      threshold: 0.1,
      rootMargin: '0px 0px -40px 0px',
    }
  );

  scrollElements.forEach((el) => observer.observe(el));
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initAnimations);
} else {
  initAnimations();
}
