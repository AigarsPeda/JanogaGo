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
    const centreBatch = 5;
    const batchCount = centreBatch * 2 + 1;
    let track;
    let loopWidth = 0;
    let carouselFrame;
    let lastFrameTime = 0;
    let scrollRemainder = 0;
    let resizeTimer;
    let resumeTimer;
    let pointerActive = false;
    let touchActive = false;
    let imagesReady = false;
    let viewportWidth = window.innerWidth;

    const canAutoplay = () => track && loopWidth && imagesReady && !pointerActive && !touchActive && mobileCarouselQuery.matches && !reducedMotionQuery.matches && !document.hidden;
    const stopCarousel = () => {
      if (carouselFrame !== undefined) window.cancelAnimationFrame(carouselFrame);
      carouselFrame = undefined;
      lastFrameTime = 0;
      scrollRemainder = 0;
    };
    const createCloneBatch = () => {
      const batch = document.createDocumentFragment();
      originalSlides.forEach(slide => {
        const clone = slide.cloneNode(true);
        clone.dataset.jgCarouselClone = 'true';
        clone.setAttribute('aria-hidden', 'true');
        clone.querySelectorAll('img').forEach(image => { image.loading = 'eager'; });
        batch.append(clone);
      });
      return batch;
    };
    const recenterCarousel = (force = false) => {
      if (!track || !loopWidth) return;
      const left = clientCarousel.scrollLeft;
      const remaining = clientCarousel.scrollWidth - left - clientCarousel.clientWidth;
      if (!force && left >= loopWidth * 2 && remaining >= loopWidth * 2) return;
      // Whole repeats preserve the visible logos without inserting DOM during a swipe.
      const phase = ((left % loopWidth) + loopWidth) % loopWidth;
      clientCarousel.scrollLeft = centreBatch * loopWidth + phase;
    };
    const moveCarousel = timestamp => {
      carouselFrame = undefined;
      if (!canAutoplay()) return;
      if (!lastFrameTime) lastFrameTime = timestamp;
      const elapsed = Math.min(timestamp - lastFrameTime, 48);
      lastFrameTime = timestamp;
      scrollRemainder += elapsed * 0.04;
      const scrollBy = Math.floor(scrollRemainder);
      scrollRemainder -= scrollBy;
      if (scrollBy) clientCarousel.scrollLeft += scrollBy;
      recenterCarousel();
      carouselFrame = window.requestAnimationFrame(moveCarousel);
    };
    const startCarousel = () => {
      if (!canAutoplay() || carouselFrame !== undefined) return;
      lastFrameTime = 0;
      carouselFrame = window.requestAnimationFrame(moveCarousel);
    };
    const resumeCarousel = () => {
      window.clearTimeout(resumeTimer);
      if (pointerActive || touchActive) return;
      resumeTimer = window.setTimeout(startCarousel, 700);
    };
    const pauseCarousel = () => {
      window.clearTimeout(resumeTimer);
      stopCarousel();
    };
    const initialiseCarousel = (mountedTrack, attempt = 0) => {
      if (track !== mountedTrack) return;
      loopWidth = mountedTrack.children[originalSlides.length].offsetLeft - mountedTrack.children[0].offsetLeft;
      if (!loopWidth && attempt < 12) {
        window.requestAnimationFrame(() => initialiseCarousel(mountedTrack, attempt + 1));
        return;
      }
      if (!loopWidth) return;
      clientCarousel.scrollLeft = loopWidth * centreBatch;
      clientCarousel.classList.add('is-carousel');
      startCarousel();
    };

    const mountCarousel = () => {
      if (track) return;
      track = document.createElement('div');
      track.className = 'jg-client-track';
      originalSlides.forEach(slide => slide.querySelectorAll('img').forEach(image => { image.loading = 'eager'; }));
      for (let batch = 0; batch < batchCount; batch += 1) {
        if (batch === centreBatch) originalSlides.forEach(slide => track.append(slide));
        else track.append(createCloneBatch());
      }
      clientCarousel.append(track);
      const mountedTrack = track;
      const images = originalSlides.flatMap(slide => Array.from(slide.querySelectorAll('img')));
      Promise.all(images.map(image => image.decode().catch(() => {}))).then(() => {
        if (track !== mountedTrack) return;
        imagesReady = true;
        resumeCarousel();
      });
      window.requestAnimationFrame(() => initialiseCarousel(mountedTrack));
    };
    const unmountCarousel = () => {
      stopCarousel();
      window.clearTimeout(resumeTimer);
      clientCarousel.classList.remove('is-carousel');
      if (!track) return;
      originalSlides.forEach(slide => clientCarousel.append(slide));
      track.remove();
      track = undefined;
      loopWidth = 0;
      imagesReady = false;
      pointerActive = false;
      touchActive = false;
      clientCarousel.scrollLeft = 0;
    };
    const syncCarousel = () => {
      viewportWidth = window.innerWidth;
      unmountCarousel();
      if (mobileCarouselQuery.matches && !reducedMotionQuery.matches) mountCarousel();
    };
    clientCarousel.addEventListener('scroll', () => {
      recenterCarousel();
      // Momentum keeps producing scroll events after the finger has lifted.
      if (carouselFrame === undefined) resumeCarousel();
    }, { passive: true });
    clientCarousel.addEventListener('pointerdown', () => {
      pointerActive = true;
      pauseCarousel();
      recenterCarousel(true);
    }, { passive: true });
    const releasePointer = () => { pointerActive = false; resumeCarousel(); };
    window.addEventListener('pointerup', releasePointer, { passive: true });
    clientCarousel.addEventListener('pointercancel', releasePointer, { passive: true });
    clientCarousel.addEventListener('touchstart', () => {
      touchActive = true;
      pauseCarousel();
      recenterCarousel(true);
    }, { passive: true });
    const releaseTouch = () => { touchActive = false; resumeCarousel(); };
    clientCarousel.addEventListener('touchend', releaseTouch, { passive: true });
    clientCarousel.addEventListener('touchcancel', releaseTouch, { passive: true });
    clientCarousel.addEventListener('wheel', () => { pauseCarousel(); resumeCarousel(); }, { passive: true });
    document.addEventListener('visibilitychange', () => document.hidden ? pauseCarousel() : resumeCarousel());
    syncCarousel();
    mobileCarouselQuery.addEventListener('change', syncCarousel);
    reducedMotionQuery.addEventListener('change', syncCarousel);
    window.addEventListener('resize', () => {
      // Safari's collapsing address bar changes height, not the logo layout.
      if (window.innerWidth === viewportWidth) return;
      window.clearTimeout(resizeTimer);
      resizeTimer = window.setTimeout(syncCarousel, 120);
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
    const result = form.querySelector('#jg-enquiry-result');
    if (result) {
      // Consume the submission URL without changing this visit's scroll position.
      // A later reload/open should not replay the notice or jump to the form.
      const url = new URL(location.href);
      url.searchParams.delete('enquiry');
      if (url.hash === '#pieteikties') url.hash = '';
      history.replaceState(history.state, '', url);
      const finishDismiss = () => {
        if (result.contains(document.activeElement)) {
          form.querySelector('input:not([type="hidden"])')?.focus({preventScroll: true});
        }
        result.hidden = true;
      };
      const dismiss = () => {
        if (result.hidden || result.classList.contains('jg-notice-closing')) return;
        if (matchMedia('(prefers-reduced-motion: reduce)').matches) finishDismiss();
        else result.classList.add('jg-notice-closing');
      };
      result.querySelector('.jg-notice-close')?.addEventListener('click', dismiss);
      result.addEventListener('animationend', event => {
        if (event.target === result && result.classList.contains('jg-notice-closing')) finishDismiss();
      });
      document.addEventListener('keydown', event => {
        if (event.key === 'Escape') dismiss();
      });
    }
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
        return;
      }
      form.setAttribute('aria-busy', 'true');
      const submit = form.querySelector('[type="submit"]');
      if (submit) submit.disabled = true;
    });
  });
  window.addEventListener('pageshow', () => {
    document.querySelectorAll('.jg-enquiry-form').forEach(form => {
      form.removeAttribute('aria-busy');
      const submit = form.querySelector('[type="submit"]');
      if (submit) submit.disabled = false;
    });
  });
});
