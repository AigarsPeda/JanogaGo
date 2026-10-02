/* A slow front-facing turn; direct interaction hands the camera to the visitor. */
document.addEventListener('DOMContentLoaded', () => {
  const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
  document.querySelectorAll('.jg-hero-model').forEach(wrapper => {
    const viewer = wrapper.querySelector('model-viewer');
    const fallback = wrapper.querySelector('.jg-model-fallback');
    const image = fallback.querySelector('img');
    const controls = wrapper.querySelector('.jg-model-controls');
    const button = wrapper.querySelector('.jg-model-motion');
    let loaded = false;
    let visible = false;
    let rotating = !reducedMotion.matches;
    let frame = 0;
    let elapsed = 0;
    let last = 0;
    let revealFrame = 0;
    let revealTimer = 0;
    const tick = now => {
      elapsed += Math.min(now - last, 100);
      last = now;
      const angle = 15 * Math.sin(elapsed / 32000 * Math.PI * 2);
      viewer.cameraOrbit = `${angle}deg 90deg 4.74m`;
      frame = requestAnimationFrame(tick);
    };
    const update = () => {
      cancelAnimationFrame(frame);
      const running = loaded && visible && rotating && !document.hidden;
      wrapper.dataset.modelMotion = running ? 'running' : 'paused';
      const label = rotating ? button.dataset.pause : button.dataset.resume;
      button.dataset.motion = rotating ? 'running' : 'paused';
      button.setAttribute('aria-label', label);
      button.setAttribute('title', label);
      if (running) { last = performance.now(); frame = requestAnimationFrame(tick); }
    };
    const stop = () => { rotating = false; update(); };
    const restorePhoto = () => {
      if (!wrapper.hasAttribute('data-model-poster')) return;
      for (const key of ['srcset', 'sizes', 'width', 'height', 'class', 'loading', 'fetchpriority', 'src']) {
        const value = image.getAttribute(`data-model-photo-${key}`);
        if (value) image.setAttribute(key, value);
        else image.removeAttribute(key);
      }
      wrapper.removeAttribute('data-model-poster');
    };
    if (image) {
      image.addEventListener('error', restorePhoto);
      if (image.complete && !image.naturalWidth) restorePhoto();
    }
    const finishReveal = () => {
      loaded = true;
      wrapper.dataset.modelState = 'ready';
      controls.hidden = false;
      update();
    };
    const reveal = () => {
      clearTimeout(revealTimer);
      cancelAnimationFrame(revealFrame);
      // Allow the loaded model to paint before blending it with the photo.
      revealFrame = requestAnimationFrame(() => {
        revealFrame = requestAnimationFrame(() => {
          wrapper.dataset.modelState = 'revealing';
          viewer.removeAttribute('aria-hidden');
          fallback.setAttribute('aria-hidden', 'true');
          // Rotation starts only after the fade, from the same frontal pose.
          if (reducedMotion.matches) { finishReveal(); }
          else { revealTimer = setTimeout(finishReveal, 320); }
        });
      });
    };
    viewer.addEventListener('load', reveal);
    viewer.addEventListener('error', () => {
      clearTimeout(revealTimer);
      cancelAnimationFrame(revealFrame);
      loaded = false;
      restorePhoto();
      wrapper.dataset.modelState = 'error';
      viewer.setAttribute('aria-hidden', 'true');
      fallback.removeAttribute('aria-hidden');
      controls.hidden = true;
      update();
    });
    button.addEventListener('click', () => { rotating = !rotating; update(); });
    viewer.addEventListener('pointerdown', stop);
    viewer.addEventListener('keydown', stop);
    viewer.addEventListener('camera-change', event => { if (event.detail.source === 'user-interaction') stop(); });
    reducedMotion.addEventListener('change', () => { if (reducedMotion.matches) stop(); });
    document.addEventListener('visibilitychange', update);
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(entries => {
        visible = entries[0].isIntersecting;
        update();
      }, { threshold: .05 }).observe(wrapper);
    } else { visible = true; }
    update();
    // A cached model may finish before DOMContentLoaded attaches the listener.
    if (viewer.loaded) { reveal(); }
  });
});
