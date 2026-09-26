// Run with: node scripts/tests/client-carousel.cjs
// Exercise the shipped carousel logic with a clock and a minimal scroll container.
// Native rendering/swipes are checked separately in the browser.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

class Events {
  listeners = {};
  addEventListener(type, callback) { (this.listeners[type] ||= []).push(callback); }
  emit(type) { (this.listeners[type] || []).forEach(callback => callback()); }
}
class Element extends Events {
  children = [];
  dataset = {};
  classes = new Set();
  classList = { add: name => this.classes.add(name), remove: name => this.classes.delete(name) };
  scrollLeft = 0;
  clientWidth = 342;
  constructor(image = null, fragment = false) { super(); this.image = image; this.fragment = fragment; }
  append(...elements) {
    elements.forEach(element => {
      if (element.fragment) return this.append(...[...element.children]);
      element.remove();
      element.parent = this;
      this.children.push(element);
    });
  }
  remove() {
    if (this.parent) this.parent.children.splice(this.parent.children.indexOf(this), 1);
    this.parent = null;
  }
  get offsetLeft() { return 24 + this.parent.children.indexOf(this) * 238; }
  get scrollWidth() { return (this.children[0]?.children.length || 0) * 238 - 4; }
  setAttribute() {}
  querySelectorAll(selector) { return selector === 'img' && this.image ? [this.image] : []; }
  cloneNode() { return new Element({ loading: 'lazy' }); }
}

async function run() {
  let now = 0, nextId = 0, releaseImages;
  const decoding = new Promise(resolve => { releaseImages = resolve; });
  const timers = new Map(), frames = new Map();
  const carousel = new Element();
  for (let index = 0; index < 5; index++) carousel.append(new Element({ loading: 'lazy', decode: () => decoding }));
  const mobile = new Events(), reduced = new Events();
  mobile.matches = true;
  reduced.matches = false;
  const window = Object.assign(new Events(), {
    innerWidth: 390,
    matchMedia: () => mobile,
    requestAnimationFrame: callback => { frames.set(++nextId, callback); return nextId; },
    cancelAnimationFrame: id => frames.delete(id),
    setTimeout: (callback, delay) => { timers.set(++nextId, { callback, at: now + delay }); return nextId; },
    clearTimeout: id => timers.delete(id),
  });
  const document = Object.assign(new Events(), {
    hidden: false,
    querySelector: () => carousel,
    createElement: () => new Element(),
    createDocumentFragment: () => new Element(null, true),
  });
  const source = fs.readFileSync(path.join(__dirname, '../../theme/janogago/assets/js/site.js'), 'utf8');
  vm.runInNewContext(source.slice(source.indexOf('  const clientCarousel ='), source.indexOf('  const faqItems =')), {
    document, window, reducedMotionQuery: reduced,
  });
  const advance = milliseconds => {
    now += milliseconds;
    [...timers].filter(([, timer]) => timer.at <= now).forEach(([id, timer]) => {
      timers.delete(id); timer.callback();
    });
  };
  [...frames].forEach(([id, callback]) => { frames.delete(id); callback(now); });
  const track = carousel.children[0];
  assert.equal(carousel.scrollLeft, 5950, 'page padding must not enter the repeat distance');
  assert.equal(frames.size, 0, 'autoplay waits for image decoding');
  assert(track.children.every(slide => slide.image.loading === 'eager'));
  releaseImages();
  await new Promise(resolve => setImmediate(resolve));
  advance(700);
  assert.equal(frames.size, 1, 'autoplay starts once images are decoded');

  carousel.emit('pointerdown');
  carousel.emit('touchstart');
  carousel.emit('pointercancel');
  advance(1200);
  assert.equal(frames.size, 0, 'pointer cancellation must not restart while touch is held');
  carousel.emit('touchend');
  for (let tick = 0; tick < 8; tick++) { advance(500); carousel.emit('scroll'); }
  advance(699);
  assert.equal(frames.size, 0, 'autoplay stays paused throughout momentum');
  advance(1);
  assert.equal(frames.size, 1, 'autoplay resumes after scrolling settles');

  carousel.emit('wheel');
  const count = track.children.length;
  for (const left of [0, -95.5, carousel.scrollWidth - carousel.clientWidth, 100000, 37.25]) {
    carousel.scrollLeft = left;
    carousel.emit('scroll');
    assert(carousel.scrollLeft >= 2380 && carousel.scrollLeft <= carousel.scrollWidth - carousel.clientWidth - 2380);
    const phase = value => ((value % 1190) + 1190) % 1190;
    assert(Math.abs(phase(left) - phase(carousel.scrollLeft)) < 0.001, 'wrapping preserves the visible logo position');
    assert.equal(track.children.length, count, 'fast swipes never create new unloaded slides');
  }
  window.emit('resize');
  advance(120);
  assert.equal(carousel.children[0], track, 'height-only address-bar resizing must preserve the carousel');
  reduced.matches = true;
  reduced.emit('change');
  assert.equal(carousel.children.length, 5, 'reduced motion restores the five original slides');
  assert.equal(frames.size, 0);
  reduced.matches = false;
  mobile.matches = false;
  mobile.emit('change');
  assert.equal(carousel.children.length, 5, 'desktop retains the original logo grid');
  console.log('Carousel checks passed: eager loading, both edges, swipe momentum, fixed DOM, resize and reduced motion.');
}
run().catch(error => { console.error(error); process.exitCode = 1; });
