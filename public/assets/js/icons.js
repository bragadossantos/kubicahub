/* ============================================================
   KUBICA HUB — SVG Icon Library v1.0
   11 ícones vetoriais inline, stroke-width: 1.5 / 2.0
   Baseados em currentColor — zero emojis unicode
   ============================================================ */

const KubicaIcons = (() => {

  const _defaults = {
    size: 20,
    strokeWidth: 1.5,
    color: 'currentColor',
  };

  // ── Primitivo SVG ────────────────────────────────────────────
  function _svg(path, opts = {}) {
    const { size, strokeWidth, color } = { ..._defaults, ...opts };
    return `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 24 24"
      fill="none" stroke="${color}" stroke-width="${strokeWidth}"
      stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${path}</svg>`;
  }

  // ── Ícones ───────────────────────────────────────────────────

  /** Documento / Proposta */
  function fileText(opts) {
    return _svg(`
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
      <polyline points="14 2 14 8 20 8"/>
      <line x1="16" y1="13" x2="8" y2="13"/>
      <line x1="16" y1="17" x2="8" y2="17"/>
      <polyline points="10 9 9 9 8 9"/>
    `, opts);
  }

  /** Confirmação / Aceite */
  function checkCircle(opts) {
    return _svg(`
      <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
      <polyline points="22 4 12 14.01 9 11.01"/>
    `, opts);
  }

  /** Birrete Académico */
  function academicCap(opts) {
    return _svg(`
      <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
      <path d="M6 12v5c0 2.21 3.58 4 8 4s8-1.79 8-4v-5"/>
    `, opts);
  }

  /** Instituição / Universidade */
  function institution(opts) {
    return _svg(`
      <rect x="2" y="20" width="20" height="2"/>
      <rect x="4" y="11" width="2" height="9"/>
      <rect x="9" y="11" width="2" height="9"/>
      <rect x="14" y="11" width="2" height="9"/>
      <rect x="19" y="11" width="2" height="9"/>
      <polygon points="12 2 2 9 22 9"/>
    `, opts);
  }

  /** Sino / Notificação */
  function bell(opts) {
    return _svg(`
      <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
      <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
    `, opts);
  }

  /** Seta Diagonal / Link externo */
  function arrowUpRight(opts) {
    return _svg(`
      <line x1="7" y1="17" x2="17" y2="7"/>
      <polyline points="7 7 17 7 17 17"/>
    `, opts);
  }

  /** Escudo com Marca / Conformidade */
  function shieldCheck(opts) {
    return _svg(`
      <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
      <polyline points="9 12 11 14 15 10"/>
    `, opts);
  }

  /** Relógio / Tempo */
  function clock(opts) {
    return _svg(`
      <circle cx="12" cy="12" r="10"/>
      <polyline points="12 6 12 12 16 14"/>
    `, opts);
  }

  /** Raio / Agilidade / Startup */
  function lightning(opts) {
    return _svg(`
      <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
    `, opts);
  }

  /** Pessoas / Equipa */
  function users(opts) {
    return _svg(`
      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
      <circle cx="9" cy="7" r="4"/>
      <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
      <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
    `, opts);
  }

  /** Cadeado / Segurança */
  function lock(opts) {
    return _svg(`
      <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
      <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
    `, opts);
  }

  /** Pasta / Projectos */
  function folder(opts) {
    return _svg(`
      <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
    `, opts);
  }

  /** Gráfico de Barras / Relatórios */
  function barChart(opts) {
    return _svg(`
      <line x1="18" y1="20" x2="18" y2="10"/>
      <line x1="12" y1="20" x2="12" y2="4"/>
      <line x1="6"  y1="20" x2="6"  y2="14"/>
    `, opts);
  }

  /** Engrenagem / Configurações */
  function settings(opts) {
    return _svg(`
      <circle cx="12" cy="12" r="3"/>
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33
        1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06
        a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09
        A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06
        A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51
        a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9
        a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
    `, opts);
  }

  /** Painel / Dashboard (hexágono) */
  function grid(opts) {
    return _svg(`
      <rect x="3"  y="3"  width="7" height="7"/>
      <rect x="14" y="3"  width="7" height="7"/>
      <rect x="14" y="14" width="7" height="7"/>
      <rect x="3"  y="14" width="7" height="7"/>
    `, opts);
  }

  /** Lâmpada / Ideia */
  function lightbulb(opts) {
    return _svg(`
      <line x1="9"  y1="18" x2="15" y2="18"/>
      <line x1="10" y1="22" x2="14" y2="22"/>
      <path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5
               A4.61 4.61 0 0 1 8.91 14"/>
    `, opts);
  }

  /** Aperto de mão / Colaboração */
  function handshake(opts) {
    return _svg(`
      <path d="M20.42 4.58a5.4 5.4 0 0 0-7.65 0l-.77.78-.77-.78a5.4 5.4 0 0 0-7.65 7.65l.77.79L12 21l7.65-8.63.77-.79
               a5.4 5.4 0 0 0 0-7.4z"/>
    `, opts);
  }

  // ── Contêiner Circular para KPI ─────────────────────────────
  /**
   * Embrulha um ícone SVG num contêiner circular estilo Design System Kubica.
   * @param {string} iconHtml  - resultado de qualquer função de ícone acima
   * @param {string} size      - '36px' (padrão sidebar), '44px' (KPI)
   */
  function kpiWrapper(iconHtml, size = '44px') {
    return `
      <div style="
        width: ${size}; height: ${size};
        border-radius: 9999px;
        background: var(--color-amber-dim, rgba(255,180,66,0.08));
        border: 1px solid var(--color-walnut, #43392d);
        display: inline-flex; align-items: center; justify-content: center;
        flex-shrink: 0; color: var(--color-amber-forge, #ffb442);">
        ${iconHtml}
      </div>`;
  }

  /**
   * Lookup por nome de string (útil para data-driven rendering).
   * @param {string} name  - ex: 'fileText', 'bell', 'users'
   * @param {Object} opts  - opções de tamanho/cor
   */
  function get(name, opts = {}) {
    const map = {
      fileText, checkCircle, academicCap, institution, bell,
      arrowUpRight, shieldCheck, clock, lightning, users, lock,
      folder, barChart, settings, grid, lightbulb, handshake,
    };
    const fn = map[name];
    if (!fn) {
      console.warn(`[KubicaIcons] Ícone "${name}" não encontrado.`);
      return '';
    }
    return fn(opts);
  }

  return {
    fileText, checkCircle, academicCap, institution, bell,
    arrowUpRight, shieldCheck, clock, lightning, users, lock,
    folder, barChart, settings, grid, lightbulb, handshake,
    kpiWrapper, get,
  };

})();
