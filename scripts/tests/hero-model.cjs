const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(require('node:path').join(__dirname, '../../theme/janogago/assets/js/hero-model.js'), 'utf8');

function setup({ reduced = false, cached = false, poster = false } = {}) {
  const raf = new Map(), timers = new Map();
  let sequence = 0;
  const element = (extra = {}) => ({
    dataset: {}, attrs: {}, handlers: {}, hidden: true,
    addEventListener(name, handler) { this.handlers[name] = handler; },
    setAttribute(name, value) { this.attrs[name] = value; },
    removeAttribute(name) { delete this.attrs[name]; },
    getAttribute(name) { return this.attrs[name] ?? null; },
    hasAttribute(name) { return name in this.attrs; },
    ...extra,
  });
  const viewer = element({ loaded: cached, cameraOrbit: '0deg 90deg 4.74m' });
  const image = element({ complete: false, naturalWidth: 0, attrs: poster ? {
    src: 'poster.png', srcset: 'poster-768.png 768w', class: 'jg-model-poster-image',
    'data-model-photo-src': 'original.png', 'data-model-photo-srcset': 'original-768.png 768w',
    'data-model-photo-sizes': '(max-width: 737px) 100vw, 737px', 'data-model-photo-width': '737',
    'data-model-photo-height': '1024', 'data-model-photo-class': 'wp-image-134',
    'data-model-photo-loading': 'eager', 'data-model-photo-fetchpriority': '',
  } : {} });
  const fallback = element({ querySelector: () => image }), controls = element();
  const button = element({ dataset: { pause: 'Pause rotation', resume: 'Resume rotation' } });
  const children = { 'model-viewer': viewer, '.jg-model-fallback': fallback, '.jg-model-controls': controls, '.jg-model-motion': button };
  const wrapper = element({ dataset: { modelState: 'loading' }, querySelector: selector => children[selector] });
  if (poster) wrapper.attrs['data-model-poster'] = 'true';
  const preference = element({ matches: reduced });
  const document = element({ hidden: false, querySelectorAll: () => [wrapper] });
  let intersection;
  vm.runInNewContext(source, {
    document, matchMedia: () => preference, window: { IntersectionObserver: true },
    IntersectionObserver: class { constructor(callback) { intersection = callback; } observe() {} },
    performance: { now: () => 0 },
    requestAnimationFrame: callback => { const id = ++sequence; raf.set(id, callback); return id; },
    cancelAnimationFrame: id => raf.delete(id),
    setTimeout: callback => { const id = ++sequence; timers.set(id, callback); return id; },
    clearTimeout: id => timers.delete(id),
  });
  document.handlers.DOMContentLoaded();
  intersection([{ isIntersecting: true }]);
  const paint = (time = 0) => { const pending = [...raf.values()]; raf.clear(); pending.forEach(callback => callback(time)); };
  const finishFade = () => { const pending = [...timers.values()]; timers.clear(); pending.forEach(callback => callback()); };
  return { viewer, image, fallback, controls, button, wrapper, document, preference, raf, timers, paint, finishFade, intersection };
}

const normal = setup();
normal.viewer.handlers.load();
assert.equal(normal.wrapper.dataset.modelState, 'loading');
normal.paint(); normal.paint();
assert.equal(normal.wrapper.dataset.modelState, 'revealing');
assert.equal(normal.wrapper.dataset.modelMotion, 'paused', 'Rotation must wait for the photo/model fade.');
assert.equal(normal.controls.hidden, true);
assert.equal(normal.viewer.cameraOrbit, '0deg 90deg 4.74m');
normal.finishFade(); normal.paint();
assert.equal(normal.wrapper.dataset.modelState, 'ready');
assert.equal(normal.controls.hidden, false);
assert.equal(normal.viewer.cameraOrbit, '0deg 90deg 4.74m', 'The first animation frame must retain the frontal pose.');
normal.viewer.handlers.pointerdown();
assert.equal(normal.raf.size, 0, 'Direct interaction must stop rotation.');
normal.viewer.handlers.error();
assert.equal(normal.wrapper.dataset.modelState, 'error');
assert.equal(normal.fallback.attrs['aria-hidden'], undefined);
assert.equal(normal.controls.hidden, true);

for (const failAt of ['before-paint', 'during-fade']) {
  const failure = setup(); failure.viewer.handlers.load();
  if (failAt === 'during-fade') { failure.paint(); failure.paint(); }
  failure.viewer.handlers.error(); failure.paint(); failure.finishFade();
  assert.equal(failure.wrapper.dataset.modelState, 'error', 'A pending reveal must not hide the fallback after failure.');
  assert.equal(failure.raf.size, 0); assert.equal(failure.timers.size, 0);
}
const reduced = setup({ reduced: true });
reduced.viewer.handlers.load(); reduced.paint(); reduced.paint();
assert.equal(reduced.wrapper.dataset.modelState, 'ready');
assert.equal(reduced.timers.size, 0, 'Reduced motion must bypass the fade delay.');
assert.equal(reduced.raf.size, 0, 'Reduced motion must not autoplay.');
const cached = setup({ cached: true }); cached.paint(); cached.paint(); cached.finishFade();
assert.equal(cached.wrapper.dataset.modelState, 'ready', 'Cached models must reveal even if load fired before DOMContentLoaded.');
for (const failureType of ['image', 'model']) {
  const preview = setup({ poster: true });
  if (failureType === 'image') preview.image.handlers.error(); else preview.viewer.handlers.error();
  assert.equal(preview.image.attrs.src, 'original.png', 'Failure must restore the original photo.');
  assert.equal(preview.image.attrs.srcset, 'original-768.png 768w');
  assert.equal(preview.image.attrs.class, 'wp-image-134');
  assert.equal(preview.image.attrs.width, '737');
  assert.equal(preview.wrapper.hasAttribute('data-model-poster'), false, 'Photo framing must replace poster framing on failure.');
}
console.log('Hero model checks passed: frontal reveal, delayed rotation, interaction, original-photo recovery from model/poster failures, cached loading and reduced motion.');
