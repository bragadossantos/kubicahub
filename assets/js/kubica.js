/* ============================================================
   KUBICA HUB — Shared Navigation & UI Utilities v2.0
   ============================================================ */

// ── NAV MODULE ────────────────────────────────────────────────
const KubicaNav = (() => {
  function getNavHTML(activePage = '') {
    const isAuthPage = activePage === 'auth';
    const user = (typeof KubicaApp !== 'undefined') ? KubicaApp.getUser() : null;
    const basePath = (typeof KubicaApp !== 'undefined') ? KubicaApp.getBasePath() : '';

    let actionButtons = '';

    if (user) {
      const dashUrl = `${basePath}/paginas/${user.role}/dashboard.html`;
      const arrowSvg = typeof KubicaIcons !== 'undefined' ? KubicaIcons.get('arrowRight', { size: 14 }) : '';
      actionButtons = `
        <a href="${dashUrl}" class="btn-primary btn-sm">Meu Painel (${user.role}) <span class="k-icon">${arrowSvg}</span></a>
        <button onclick="KubicaApp.logout()" class="btn-outline btn-sm" style="font-size:12px;padding:5px 10px;">Sair</button>
      `;
    } else if (!isAuthPage) {
      const loginUrl = `${basePath}/paginas/auth/login.html`;
      const registerUrl = `${basePath}/paginas/auth/registo.html`;
      const arrowSvg = typeof KubicaIcons !== 'undefined' ? KubicaIcons.get('arrowRight', { size: 14 }) : '';
      actionButtons = `
        <a href="${loginUrl}" class="btn-secondary btn-sm">Entrar</a>
        <a href="${registerUrl}" class="btn-primary btn-sm">Participar <span class="k-icon">${arrowSvg}</span></a>
      `;
    } else {
      const landingUrl = `${basePath}/paginas/landing.html`;
      const arrowLeftSvg = typeof KubicaIcons !== 'undefined' ? KubicaIcons.get('arrowLeft', { size: 14 }) : '';
      actionButtons = `
        <a href="${landingUrl}" class="btn-outline btn-sm"><span class="k-icon">${arrowLeftSvg}</span> Início</a>
      `;
    }

    return `
    <nav class="k-nav" role="navigation" aria-label="Kubica Hub navigation">
      <div class="k-nav__inner">
        <a href="${basePath}/paginas/landing.html" class="k-nav__logo" aria-label="Kubica Hub início">
          <div class="k-nav__logo-mark" aria-hidden="true">K</div>
          <span class="k-nav__wordmark">Kubica Hub</span>
        </a>

        <div class="k-nav__actions" style="margin-left:auto;display:flex;align-items:center;gap:12px;">
          ${actionButtons}
        </div>
      </div>
    </nav>`;
  }

  function init(activePage = '') {
    const placeholder = document.getElementById('k-nav-placeholder');
    if (placeholder) {
      placeholder.innerHTML = getNavHTML(activePage);
    }
  }

  return { init };
})();

// ── FOOTER MODULE ─────────────────────────────────────────────
function getFooterHTML() {
  const basePath = (typeof KubicaApp !== 'undefined') ? KubicaApp.getBasePath() : '';
  return `
  <footer class="k-footer" role="contentinfo">
    <div class="container">
      <div class="k-footer__grid">
        <div>
          <div class="k-nav__logo" style="margin-bottom:0">
            <div class="k-nav__logo-mark">K</div>
            <span class="k-nav__wordmark">Kubica Hub</span>
          </div>
          <p class="k-footer__brand-desc">
            Forjando as novas indústrias de Angola no coração das universidades. 
            Framework KUBICA de Co-Criação Universitária (FKCU).
          </p>
        </div>
        <div>
          <p class="k-footer__col-title">Acesso</p>
          <div class="k-footer__links">
            <a href="${basePath}/paginas/auth/login.html" class="k-footer__link">Entrar no Hub</a>
            <a href="${basePath}/paginas/auth/registo.html" class="k-footer__link">Criar Conta FKCU</a>
          </div>
        </div>
        <div>
          <p class="k-footer__col-title">Universidades Parceiras</p>
          <div class="k-footer__links">
            <span class="k-footer__link">Universidade Agostinho Neto (UAN)</span>
            <span class="k-footer__link">Universidade Católica de Angola (UCAN)</span>
            <span class="k-footer__link">ISAF & UGS</span>
          </div>
        </div>
      </div>
      <div class="k-footer__bottom">
        <span>© 2026 Kubica Hub · FKCU · Luanda, Angola</span>
        <div class="flex flex-gap-24">
          <span>Lei n.º 3/92 — IAPI</span>
          <span>Proposta Lei Startups 2026</span>
          <span>PDN 2023-2027</span>
        </div>
      </div>
    </div>
  </footer>`;
}

// ── DIAL WIDGET ───────────────────────────────────────────────
function buildDial(containerId, value, max, label, unit = '') {
  const el = document.getElementById(containerId);
  if (!el) return;

  const pct    = Math.min(value / max, 1);
  const radius = 70;
  const dots   = 60;
  const dotAngles = Array.from({ length: dots }, (_, i) => (i / dots) * 360);

  const dotsSVG = dotAngles.map((angle, i) => {
    const rad  = (angle - 90) * (Math.PI / 180);
    const cx   = 90 + radius * Math.cos(rad);
    const cy   = 90 + radius * Math.sin(rad);
    const active = i / dots < pct;
    return `<circle cx="${cx.toFixed(2)}" cy="${cy.toFixed(2)}" r="2"
      fill="${active ? '#ffb442' : '#43392d'}" opacity="${active ? 1 : 0.6}"/>`;
  }).join('');

  el.innerHTML = `
    <div class="dial-widget">
      <div class="dial-svg-wrap">
        <svg width="180" height="180" viewBox="0 0 180 180" aria-label="${label}: ${value}${unit}">
          ${dotsSVG}
        </svg>
        <div class="dial-center-text">
          <div class="dial-value">${unit}${value.toLocaleString('pt-AO')}</div>
          <div class="dial-unit">${label}</div>
        </div>
      </div>
    </div>`;
}

// ── TOAST NOTIFICATION ─────────────────────────────────────────
function showToast(message, type = 'info', duration = 3500) {
  let container = document.getElementById('k-toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'k-toast-container';
    container.setAttribute('aria-live', 'polite');
    container.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;pointer-events:none;';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `k-toast k-toast--${type}`;
  toast.setAttribute('role', 'alert');
  toast.style.pointerEvents = 'auto';

  const iconNameMap = { success: 'checkCircle', error: 'xCircle', warning: 'alertTriangle', info: 'info' };
  const iconSvg = typeof KubicaIcons !== 'undefined'
    ? KubicaIcons.get(iconNameMap[type] || 'info', { size: 16 })
    : '';

  toast.innerHTML = `
    <span class="k-toast__icon" aria-hidden="true">${iconSvg}</span>
    <span class="k-toast__msg">${message}</span>
  `;

  container.appendChild(toast);
  requestAnimationFrame(() => toast.classList.add('k-toast--visible'));

  setTimeout(() => {
    toast.classList.remove('k-toast--visible');
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

// Inicializar Rodapé automaticamente se o elemento existir
document.addEventListener('DOMContentLoaded', () => {
  const footerPlaceholder = document.getElementById('k-footer-placeholder');
  if (footerPlaceholder) {
    footerPlaceholder.innerHTML = getFooterHTML();
  }
});
