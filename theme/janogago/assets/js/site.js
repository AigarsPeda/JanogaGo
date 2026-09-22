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

  const clientCarousel = document.querySelector('.jg-client-grid');
  const mobileCarouselQuery = window.matchMedia('(max-width: 700px)');
  if (clientCarousel) {
    const originalSlides = Array.from(clientCarousel.children);
    let carouselTimer;
    let lastFrameTime = 0;
    let loopWidth = 0;

    const clearCarousel = () => {
      window.clearInterval(carouselTimer);
      carouselTimer = undefined;
      lastFrameTime = 0;
    };
    const cloneSlides = () => {
      if (clientCarousel.querySelector('[data-jg-carousel-clone]')) return;
      originalSlides.forEach(slide => {
        const clone = slide.cloneNode(true);
        clone.dataset.jgCarouselClone = 'true';
        clone.setAttribute('aria-hidden', 'true');
        clientCarousel.append(clone);
      });
      loopWidth = clientCarousel.children[originalSlides.length].offsetLeft;
    };
    const removeClones = () => {
      clientCarousel.querySelectorAll('[data-jg-carousel-clone]').forEach(clone => clone.remove());
      clientCarousel.scrollLeft = 0;
      loopWidth = 0;
    };
    const canMove = () => mobileCarouselQuery.matches && !reducedMotionQuery.matches && !document.hidden && loopWidth > 0;
    const moveCarousel = () => {
      if (!canMove()) return;
      const timestamp = performance.now();
      if (!lastFrameTime) lastFrameTime = timestamp;
      const elapsed = Math.min(timestamp - lastFrameTime, 64);
      lastFrameTime = timestamp;
      clientCarousel.scrollLeft += elapsed * 0.026;
      if (clientCarousel.scrollLeft >= loopWidth) clientCarousel.scrollLeft -= loopWidth;
    };
    const startCarousel = () => {
      clearCarousel();
      if (!mobileCarouselQuery.matches || reducedMotionQuery.matches) {
        removeClones();
        return;
      }
      cloneSlides();
      if (canMove()) carouselTimer = window.setInterval(moveCarousel, 16);
    };
    startCarousel();
    mobileCarouselQuery.addEventListener('change', startCarousel);
    reducedMotionQuery.addEventListener('change', startCarousel);
    document.addEventListener('visibilitychange', startCarousel);
    window.addEventListener('resize', () => {
      if (mobileCarouselQuery.matches && !reducedMotionQuery.matches) {
        loopWidth = clientCarousel.children[originalSlides.length]?.offsetLeft || 0;
      }
    });
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
        item.classList.remove('is-closing');
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
        item.classList.add('is-closing');
        const endHeight = `${summary.offsetHeight}px`;
        item.faqAnimation = item.animate(
          { height: [startHeight, endHeight] },
          { duration: 220, easing: 'cubic-bezier(.4,0,1,1)' }
        );
      }

      item.faqAnimation.onfinish = () => {
        if (isOpen) {
          item.open = false;
        }
        item.style.height = '';
        item.style.overflow = '';
        item.style.willChange = '';
        item.classList.remove('is-closing');
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

  document.querySelectorAll('.jg-enquiry-form').forEach(form => {
    const phone = form.elements.phone;
    const validatePhone = () => {
      if (!phone) return;
      const digits = phone.value.replace(/\D/g, '');
      const valid = /^\+?[0-9().\s-]+$/.test(phone.value) && digits.length >= 7 && digits.length <= 15;
      phone.setCustomValidity(phone.value && !valid ? form.dataset.phoneInvalidMessage : '');
    };

    form.addEventListener('invalid', event => {
      if (event.target.validity.valueMissing) event.target.setCustomValidity(form.dataset.requiredMessage);
    }, true);
    form.addEventListener('input', event => {
      if (event.target === phone) validatePhone();
      else event.target.setCustomValidity('');
    });
    form.addEventListener('submit', event => {
      form.querySelectorAll('[required]').forEach(field => {
        if (field.validity.valueMissing) field.setCustomValidity(form.dataset.requiredMessage);
      });
      validatePhone();
      if (!form.checkValidity()) {
        event.preventDefault();
        form.querySelector(':invalid').reportValidity();
      }
    });
  });
});
