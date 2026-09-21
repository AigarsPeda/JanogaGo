document.addEventListener('DOMContentLoaded', () => {
  const header = document.querySelector('.site-header');
  const toggle = document.querySelector('.menu-toggle');
  const setMenuOpen = (open) => {
    header.classList.toggle('nav-open', open);
    if (!toggle) return;
    toggle.setAttribute('aria-expanded', open);
    toggle.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
  };
  if (toggle) toggle.addEventListener('click', () => setMenuOpen(!header.classList.contains('nav-open')));
  document.querySelectorAll('a[href^="#"]').forEach(link => link.addEventListener('click', () => setMenuOpen(false)));
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && header.classList.contains('nav-open')) setMenuOpen(false);
  });
  const observer = new IntersectionObserver(entries => entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('in-view'); }), { threshold: .12 });
  document.querySelectorAll('.section, .contact, .jg-block-section').forEach(section => observer.observe(section));
  const hero = document.querySelector('.hero');
  const desktopQuery = window.matchMedia('(min-width: 851px)');
  const reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
  if (hero && 'IntersectionObserver' in window) {
    let heroVisible = false;
    const updateScrollCue = () => hero.classList.toggle('scroll-cue-active', heroVisible && desktopQuery.matches && !reducedMotionQuery.matches);
    new IntersectionObserver(entries => {
      heroVisible = entries[0].isIntersecting;
      updateScrollCue();
    }, { threshold: .1 }).observe(hero);
    desktopQuery.addEventListener('change', updateScrollCue);
    reducedMotionQuery.addEventListener('change', updateScrollCue);
  }

  const faqItems = document.querySelectorAll('.jg-faq-item');
  const reduceFaqMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  faqItems.forEach(item => {
    const summary = item.querySelector('summary');
    const answer = item.querySelector('p');
    if (!summary || !answer) return;

    summary.addEventListener('click', event => {
      if (reduceFaqMotion.matches) return;
      event.preventDefault();
      if (item.faqAnimation) item.faqAnimation.cancel();

      const isOpen = item.open;
      const startHeight = `${item.offsetHeight}px`;
      item.style.overflow = 'hidden';
      item.style.willChange = 'height';

      if (!isOpen) {
        item.open = true;
        const endHeight = `${item.scrollHeight}px`;
        answer.animate(
          [{ opacity: 0, transform: 'translateY(-8px)' }, { opacity: 1, transform: 'translateY(0)' }],
          { duration: 260, delay: 70, easing: 'cubic-bezier(.16,1,.3,1)', fill: 'both' }
        );
        item.faqAnimation = item.animate(
          { height: [startHeight, endHeight] },
          { duration: 340, easing: 'cubic-bezier(.16,1,.3,1)' }
        );
      } else {
        const endHeight = `${summary.offsetHeight}px`;
        item.faqAnimation = item.animate(
          { height: [startHeight, endHeight] },
          { duration: 220, easing: 'cubic-bezier(.4,0,1,1)' }
        );
      }

      item.faqAnimation.onfinish = () => {
        if (isOpen) item.open = false;
        item.style.height = '';
        item.style.overflow = '';
        item.style.willChange = '';
        item.faqAnimation = null;
      };
      item.faqAnimation.oncancel = () => {
        item.style.height = '';
        item.style.overflow = '';
        item.style.willChange = '';
        item.faqAnimation = null;
      };
    });
  });

  const enquiryInterest = document.querySelector('.jg-enquiry-form input[name="service_interest"]');
  const serviceInterests = ['full-service', 'equipment-lease'];
  document.querySelectorAll('.jg-model-card').forEach((card, index) => {
    const link = card.querySelector('.jg-card-link a');
    if (!link || !enquiryInterest || !serviceInterests[index]) return;
    link.addEventListener('click', () => {
      enquiryInterest.value = serviceInterests[index];
    });
  });
});
