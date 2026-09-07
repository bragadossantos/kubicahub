/* ============================================================
   KUBICA HUB — Animation Engine v2.0
   Intersection Observer, number tickers, confetti, OTP, tabs
   ============================================================ */

const KubicaAnimate = (() => {

  // ── Intersection Observer — Reveal on Scroll ─────────────────
  function initReveal() {
    const els = document.querySelectorAll('[data-reveal]');
    if (!els.length) return;

    const obs = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          const el    = e.target;
          const type  = el.dataset.reveal || 'up';
          const delay = el.dataset.delay  || '0';
          el.style.animationDelay = delay + 'ms';
          el.classList.add('reveal-' + type);
          obs.unobserve(el);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    els.forEach(el => {
      // Pre-hide
      el.style.opacity = '0';
      obs.observe(el);
    });
  }

  // ── Number Ticker ─────────────────────────────────────────────
  /**
   * Animates an element's text from 0 to target.
   * @param {Element} el - element with data-target attribute
   * @param {number}  duration - ms
   */
  function tickNumber(el, duration = 1200) {
    const target  = parseFloat(el.dataset.target || el.textContent.replace(/[^0-9.]/g, ''));
    const suffix  = el.dataset.suffix || '';
    const prefix  = el.dataset.prefix || '';
    const decimals= parseInt(el.dataset.decimals || '0');
    const start   = performance.now();

    function step(now) {
      const progress = Math.min((now - start) / duration, 1);
      const eased    = 1 - Math.pow(1 - progress, 3); // ease-out-cubic
      const current  = eased * target;
      el.textContent = prefix + current.toFixed(decimals) + suffix;
      if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }

  function initTickers() {
    const tickers = document.querySelectorAll('[data-ticker]');
    if (!tickers.length) return;

    const obs = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          tickNumber(e.target);
          obs.unobserve(e.target);
        }
      });
    }, { threshold: 0.5 });

    tickers.forEach(el => obs.observe(el));
  }

  // ── Progress Ring ─────────────────────────────────────────────
  /**
   * @param {string} id       - ID do elemento SVG circle.progress-ring__fill
   * @param {number} pct      - 0 a 100
   * @param {number} radius   - raio do círculo (default 44)
   */
  function setProgressRing(id, pct, radius = 44) {
    const el = document.getElementById(id);
    if (!el) return;
    const circ   = 2 * Math.PI * radius;
    const offset = circ - (pct / 100) * circ;
    el.style.strokeDasharray  = circ;
    el.style.strokeDashoffset = offset;
    // Update danger/warning class
    el.classList.remove('danger', 'warning');
    if (pct < 25) el.classList.add('danger');
    else if (pct < 50) el.classList.add('warning');
  }

  // ── Countdown Timer (for MAT-03 Speed Date) ──────────────────
  let _timerInterval = null;

  function startCountdown({ totalSeconds, ringId, labelId, onEnd }) {
    if (_timerInterval) clearInterval(_timerInterval);
    const radius = 44;
    let remaining = totalSeconds;

    function tick() {
      const pct = (remaining / totalSeconds) * 100;
      setProgressRing(ringId, pct, radius);

      const m = Math.floor(remaining / 60).toString().padStart(2, '0');
      const s = (remaining % 60).toString().padStart(2, '0');
      const label = document.getElementById(labelId);
      if (label) label.textContent = `${m}:${s}`;

      if (remaining <= 0) {
        clearInterval(_timerInterval);
        if (typeof onEnd === 'function') onEnd();
        return;
      }
      remaining--;
    }

    tick();
    _timerInterval = setInterval(tick, 1000);
  }

  function stopCountdown() {
    if (_timerInterval) clearInterval(_timerInterval);
  }

  // ── Tabs ──────────────────────────────────────────────────────
  function initTabs(containerSelector = '.k-tabs-container') {
    document.querySelectorAll(containerSelector).forEach(container => {
      const tabs   = container.querySelectorAll('.k-tab');
      const panels = container.querySelectorAll('.k-tab-panel');

      tabs.forEach((tab, i) => {
        tab.addEventListener('click', () => {
          tabs.forEach(t => t.classList.remove('active'));
          panels.forEach(p => p.classList.remove('active'));
          tab.classList.add('active');
          if (panels[i]) panels[i].classList.add('active');
        });
      });

      // Activate first by default
      if (tabs[0] && !container.querySelector('.k-tab.active')) {
        tabs[0].classList.add('active');
        if (panels[0]) panels[0].classList.add('active');
      }
    });
  }

  // ── OTP Input Grid ────────────────────────────────────────────
  function initOTP(containerId, onComplete) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const inputs = container.querySelectorAll('.otp-input');

    inputs.forEach((inp, idx) => {
      inp.addEventListener('input', e => {
        const val = e.target.value.replace(/[^0-9]/g, '');
        inp.value = val.slice(-1);
        inp.classList.toggle('filled', !!inp.value);
        if (inp.value && idx < inputs.length - 1) inputs[idx + 1].focus();
        checkComplete();
      });

      inp.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !inp.value && idx > 0) inputs[idx - 1].focus();
        if (e.key === 'ArrowLeft'  && idx > 0) inputs[idx - 1].focus();
        if (e.key === 'ArrowRight' && idx < inputs.length - 1) inputs[idx + 1].focus();
      });

      inp.addEventListener('paste', e => {
        e.preventDefault();
        const text = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
        [...text].forEach((ch, i) => {
          if (inputs[idx + i]) {
            inputs[idx + i].value = ch;
            inputs[idx + i].classList.add('filled');
          }
        });
        const next = inputs[Math.min(idx + text.length, inputs.length - 1)];
        if (next) next.focus();
        checkComplete();
      });
    });

    function checkComplete() {
      const code = [...inputs].map(i => i.value).join('');
      if (code.length === inputs.length && typeof onComplete === 'function') {
        onComplete(code);
      }
    }
  }

  // ── File Drop Zone ────────────────────────────────────────────
  function initDropZone(dropId, inputId, onFile) {
    const zone  = document.getElementById(dropId);
    const input = document.getElementById(inputId);
    if (!zone) return;

    zone.addEventListener('click', () => input && input.click());
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', ()  => zone.classList.remove('drag-over'));
    zone.addEventListener('drop', e => {
      e.preventDefault();
      zone.classList.remove('drag-over');
      const file = e.dataTransfer.files[0];
      if (file && typeof onFile === 'function') onFile(file);
    });
    if (input) {
      input.addEventListener('change', e => {
        const file = e.target.files[0];
        if (file && typeof onFile === 'function') onFile(file);
      });
    }
  }

  // ── Word Counter ──────────────────────────────────────────────
  function initWordCounter(textareaId, counterId, max = 300) {
    const ta      = document.getElementById(textareaId);
    const counter = document.getElementById(counterId);
    if (!ta || !counter) return;

    function update() {
      const words = ta.value.trim().split(/\s+/).filter(Boolean).length;
      counter.textContent = `${words} / ${max} palavras`;
      counter.className = 'word-counter';
      if (words > max) counter.classList.add('over');
      else if (words > max * 0.85) counter.classList.add('close');
    }

    ta.addEventListener('input', update);
    update();
  }

  // ── Password Toggle ───────────────────────────────────────────
  function initPasswordToggles() {
    document.querySelectorAll('.input-eye-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const targetId = btn.dataset.target;
        const input    = document.getElementById(targetId);
        if (!input) return;
        const isText = input.type === 'text';
        input.type   = isText ? 'password' : 'text';
        btn.setAttribute('aria-label', isText ? 'Mostrar palavra-passe' : 'Ocultar palavra-passe');
        // Swap icon
        const eyeOpen  = btn.querySelector('.icon-eye-open');
        const eyeClose = btn.querySelector('.icon-eye-close');
        if (eyeOpen)  eyeOpen.style.display  = isText ? 'block' : 'none';
        if (eyeClose) eyeClose.style.display  = isText ? 'none'  : 'block';
      });
    });
  }

  // ── Shake Input (on error) ────────────────────────────────────
  function shakeInput(inputId) {
    const el = document.getElementById(inputId);
    if (!el) return;
    el.classList.remove('shake');
    void el.offsetWidth; // reflow to restart animation
    el.classList.add('shake');
    setTimeout(() => el.classList.remove('shake'), 500);
  }

  // ── Confetti (Amber particles) ────────────────────────────────
  function fireConfetti({ count = 60, duration = 3000 } = {}) {
    const colors = ['#ffb442', '#ffc76b', '#fff1e0', '#43392d', '#ffb44288'];

    for (let i = 0; i < count; i++) {
      const el = document.createElement('div');
      el.className = 'confetti-particle';

      const size  = 4 + Math.random() * 8;
      const left  = Math.random() * 100;
      const delay = Math.random() * 600;
      const dur   = duration * (0.7 + Math.random() * 0.6);
      const color = colors[Math.floor(Math.random() * colors.length)];

      el.style.cssText = `
        width: ${size}px; height: ${size * (1 + Math.random())}px;
        left: ${left}vw; top: -20px;
        background: ${color};
        animation-duration: ${dur}ms;
        animation-delay: ${delay}ms;
        border-radius: ${Math.random() > 0.5 ? '50%' : '2px'};
      `;

      document.body.appendChild(el);
      setTimeout(() => el.remove(), dur + delay + 100);
    }
  }

  // ── Radar Chart (SVG pure) ────────────────────────────────────
  /**
   * Renders a radar/spider chart inside a container.
   * @param {string}   containerId
   * @param {string[]} labels    - axis labels
   * @param {number[]} values    - 0 to 100 per axis
   * @param {number}   size      - SVG width/height
   */
  function renderRadar(containerId, labels, values, size = 220) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const n       = labels.length;
    const cx      = size / 2;
    const cy      = size / 2;
    const maxR    = size * 0.38;
    const levels  = 4;
    const labelR  = maxR + 22;

    function angleFor(i) { return (i / n) * 2 * Math.PI - Math.PI / 2; }
    function point(r, i) {
      const a = angleFor(i);
      return { x: cx + r * Math.cos(a), y: cy + r * Math.sin(a) };
    }

    // Grid polygons
    let gridHtml = '';
    for (let l = 1; l <= levels; l++) {
      const r    = (l / levels) * maxR;
      const pts  = Array.from({ length: n }, (_, i) => point(r, i));
      const poly = pts.map(p => `${p.x},${p.y}`).join(' ');
      gridHtml += `<polygon points="${poly}" class="radar-grid"/>`;
    }

    // Axes
    let axisHtml = '';
    for (let i = 0; i < n; i++) {
      const p = point(maxR, i);
      axisHtml += `<line x1="${cx}" y1="${cy}" x2="${p.x}" y2="${p.y}" class="radar-axis"/>`;
    }

    // Data polygon
    const dataPts = values.map((v, i) => point((v / 100) * maxR, i));
    const dataPoly = dataPts.map(p => `${p.x},${p.y}`).join(' ');

    // Dots
    const dotsHtml = dataPts.map(p => `<circle cx="${p.x}" cy="${p.y}" r="3" class="radar-dot"/>`).join('');

    // Labels
    let labelsHtml = '';
    for (let i = 0; i < n; i++) {
      const p = point(labelR, i);
      labelsHtml += `<text x="${p.x}" y="${p.y}" class="radar-label">${labels[i]}</text>`;
    }

    container.innerHTML = `
      <div class="radar-wrap">
        <svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
          ${gridHtml}
          ${axisHtml}
          <polygon points="${dataPoly}" class="radar-polygon"/>
          ${dotsHtml}
          ${labelsHtml}
        </svg>
      </div>`;
  }

  // ── Button Loading State ──────────────────────────────────────
  function setButtonLoading(btnId, loading, label = '') {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    if (loading) {
      btn._originalHtml = btn.innerHTML;
      btn.disabled      = true;
      btn.innerHTML     = `
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"
             style="animation: k-spin .7s linear infinite; flex-shrink:0;">
          <circle cx="12" cy="12" r="10" stroke-opacity=".3"/>
          <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/>
        </svg>
        ${label || 'A processar...'}`;
    } else {
      btn.disabled  = false;
      btn.innerHTML = btn._originalHtml || btn.innerHTML;
    }
  }

  // ── Stepper ───────────────────────────────────────────────────
  function updateStepper(stepperId, currentStep) {
    const stepper = document.getElementById(stepperId);
    if (!stepper) return;
    const steps      = stepper.querySelectorAll('.k-stepper__step');
    const connectors = stepper.querySelectorAll('.k-stepper__connector');

    steps.forEach((step, i) => {
      step.classList.remove('active', 'done');
      if (i + 1 === currentStep) step.classList.add('active');
      else if (i + 1 < currentStep) step.classList.add('done');
    });
    connectors.forEach((conn, i) => {
      conn.classList.toggle('done', i + 1 < currentStep);
    });
  }

  // ── Auto-init on DOMContentLoaded ────────────────────────────
  document.addEventListener('DOMContentLoaded', () => {
    initReveal();
    initTickers();
    initTabs();
    initPasswordToggles();
  });

  return {
    initReveal, initTickers,
    tickNumber, setProgressRing,
    startCountdown, stopCountdown,
    initTabs, initOTP,
    initDropZone, initWordCounter,
    initPasswordToggles,
    shakeInput, fireConfetti,
    renderRadar, setButtonLoading, updateStepper,
  };

})();
