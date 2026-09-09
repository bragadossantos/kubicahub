/* ============================================================
   KUBICA HUB — SVG Icon Library v2.0
   Biblioteca de ícones vetoriais em traço linear (stroke-width: 1.5)
   Baseados em currentColor — ZERO emojis unicode.
   Design System Kubica Hub (PP Neue Montreal / Espresso / Amber Forge)
   ============================================================ */

const KubicaIcons = (() => {

  const _defaults = {
    size: 20,
    strokeWidth: 1.5,
    color: 'currentColor',
    className: 'k-icon-svg',
  };

  function _svg(path, opts = {}) {
    const { size, strokeWidth, color, className } = { ..._defaults, ...opts };
    return `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="${color}" stroke-width="${strokeWidth}" stroke-linecap="round" stroke-linejoin="round" class="${className}" aria-hidden="true">${path}</svg>`;
  }

  // ── 1. Documentos & Propostas ──────────────────────────────────
  function fileText(o) {
    return _svg('<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>', o);
  }
  function scrollText(o) {
    return _svg('<path d="M8 21h12a2 2 0 0 0 2-2v-2H10v2a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v3h4"/><path d="M19 17V5a2 2 0 0 0-2-2H4"/><path d="M15 8h-5"/><path d="M15 12h-5"/>', o);
  }
  function clipboardCheck(o) {
    return _svg('<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/>', o);
  }

  // ── 2. Académico & Institucional ──────────────────────────────
  function academicCap(o) {
    return _svg('<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 2.21 3.58 4 8 4s8-1.79 8-4v-5"/>', o);
  }
  function institution(o) {
    return _svg('<rect x="2" y="20" width="20" height="2"/><rect x="4" y="11" width="2" height="9"/><rect x="9" y="11" width="2" height="9"/><rect x="14" y="11" width="2" height="9"/><rect x="19" y="11" width="2" height="9"/><polygon points="12 2 2 9 22 9"/>', o);
  }
  function landmark(o) {
    return _svg('<line x1="2" x2="22" y1="22" y2="22"/><line x1="6" x2="6" y1="18"/><line x1="10" x2="10" y1="18"/><line x1="14" x2="14" y1="18"/><line x1="18" x2="18" y1="18"/><polygon points="12 2 20 7 4 7"/>', o);
  }
  function bookOpen(o) {
    return _svg('<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>', o);
  }

  // ── 3. Direito, Governação & Regulação ─────────────────────────
  function scale(o) {
    return _svg('<path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="M7 21h10"/><path d="M12 3v18"/><path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"/>', o);
  }
  function shieldCheck(o) {
    return _svg('<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>', o);
  }
  function lock(o) {
    return _svg('<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>', o);
  }
  function keyRound(o) {
    return _svg('<path d="M2 18v3c0 .6.4 1 1 1h4v-3h3v-3h2l1.4-1.4a6.5 6.5 0 1 0-4-4Z"/><circle cx="16.5" cy="7.5" r=".5"/>', o);
  }
  function tag(o) {
    return _svg('<path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><path d="M7 7h.01"/>', o);
  }

  // ── 4. Finanças & Sandbox ─────────────────────────────────────
  function coins(o) {
    return _svg('<circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/>', o);
  }
  function banknote(o) {
    return _svg('<rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/>', o);
  }
  function creditCard(o) {
    return _svg('<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>', o);
  }
  function trendingUp(o) {
    return _svg('<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>', o);
  }
  function barChart(o) {
    return _svg('<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>', o);
  }

  // ── 5. Matchmaking, Equipa & Co-Founders ────────────────────────
  function handshake(o) {
    return _svg('<path d="M20.42 4.58a5.4 5.4 0 0 0-7.65 0l-.77.78-.77-.78a5.4 5.4 0 0 0-7.65 7.65l.77.79L12 21l7.65-8.63.77-.79a5.4 5.4 0 0 0 0-7.4z"/>', o);
  }
  function users(o) {
    return _svg('<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>', o);
  }
  function user(o) {
    return _svg('<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>', o);
  }
  function lightning(o) {
    return _svg('<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>', o);
  }
  function target(o) {
    return _svg('<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>', o);
  }

  // ── 6. Engenharia, TI & Inovação ──────────────────────────────
  function laptop(o) {
    return _svg('<path d="M20 16V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v9m16 0H4m16 0 1.28 2.55a1 1 0 0 1-.9 1.45H3.62a1 1 0 0 1-.9-1.45L4 16"/>', o);
  }
  function cpu(o) {
    return _svg('<rect width="16" height="16" x="4" y="4" rx="2"/><rect width="6" height="6" x="9" y="9" rx="1"/><path d="M15 2v2M9 2v2M20 15h2M20 9h2M9 20v2M15 20v2M2 9h2M2 15h2"/>', o);
  }
  function layers(o) {
    return _svg('<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>', o);
  }
  function folder(o) {
    return _svg('<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>', o);
  }
  function folderGit(o) {
    return _svg('<path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/><circle cx="12" cy="13" r="2"/><path d="M12 15v3"/>', o);
  }
  function lightbulb(o) {
    return _svg('<line x1="9" y1="18" x2="15" y2="18"/><line x1="10" y1="22" x2="14" y2="22"/><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 0 1 8.91 14"/>', o);
  }
  function smartphone(o) {
    return _svg('<rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/>', o);
  }
  function globe(o) {
    return _svg('<circle cx="12" cy="12" r="10"/><line x1="2" x2="22" y1="12" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>', o);
  }
  function externalLink(o) {
    return _svg('<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/>', o);
  }

  // ── 7. Marketing & Comunicação ────────────────────────────────
  function megaphone(o) {
    return _svg('<path d="m3 11 18-5v12L3 13v-2z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>', o);
  }
  function palette(o) {
    return _svg('<circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/>', o);
  }
  function mic(o) {
    return _svg('<path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/>', o);
  }
  function messageSquare(o) {
    return _svg('<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>', o);
  }

  // ── 8. Sectores de Negócio (Startups) ──────────────────────────
  function sprout(o) {
    return _svg('<path d="M7 20h10"/><path d="M10 20c5.5-2.5.8-6.4 3-13"/><path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4.1 5.5.8z"/><path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4.2.9-4.9 2z"/>', o);
  }
  function batteryCharging(o) {
    return _svg('<path d="M15 7h1a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-2"/><path d="M6 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h1"/><line x1="22" x2="22" y1="11" y2="13"/><polyline points="11 6 7 12 13 12 9 18"/>', o);
  }
  function truck(o) {
    return _svg('<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-5l-3-4h-5v10Z"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>', o);
  }
  function heartPulse(o) {
    return _svg('<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="M3.22 12H9.5l1.5-3 2 6.5 1.5-3.5h6.28"/>', o);
  }

  // ── 9. Design Thinking & Activadores ──────────────────────────
  function map(o) {
    return _svg('<polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" x2="9" y1="3" y2="18"/><line x1="15" x2="15" y1="6" y2="21"/>', o);
  }
  function refreshCw(o) {
    return _svg('<path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/>', o);
  }
  function repeat(o) {
    return _svg('<polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>', o);
  }
  function flaskConical(o) {
    return _svg('<path d="M10 2v7.31L4.16 19.46A2 2 0 0 0 5.86 22h12.28a2 2 0 0 0 1.7-2.54L14 9.31V2"/><path d="M8.5 2h7"/><path d="M7 16h10"/>', o);
  }
  function paperclip(o) {
    return _svg('<path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/>', o);
  }
  function rocket(o) {
    return _svg('<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>', o);
  }
  function briefcase(o) {
    return _svg('<rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>', o);
  }
  function helpCircle(o) {
    return _svg('<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" x2="12.01" y1="17" y2="17"/>', o);
  }

  // ── 10. Estados, Badges & Notificações ────────────────────────
  function checkCircle(o) {
    return _svg('<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>', o);
  }
  function check(o) {
    return _svg('<polyline points="20 6 9 17 4 12"/>', o);
  }
  function xCircle(o) {
    return _svg('<circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/>', o);
  }
  function x(o) {
    return _svg('<line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/>', o);
  }
  function alertTriangle(o) {
    return _svg('<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/>', o);
  }
  function bell(o) {
    return _svg('<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>', o);
  }
  function clock(o) {
    return _svg('<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>', o);
  }
  function calendar(o) {
    return _svg('<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>', o);
  }
  function award(o) {
    return _svg('<circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>', o);
  }
  function sparkles(o) {
    return _svg('<path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/>', o);
  }
  function flame(o) {
    return _svg('<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>', o);
  }
  function inbox(o) {
    return _svg('<polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>', o);
  }
  function bot(o) {
    return _svg('<rect width="18" height="12" x="3" y="6" rx="2"/><path d="M12 2v4M8 12h.01M16 12h.01"/>', o);
  }
  function mapPin(o) {
    return _svg('<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>', o);
  }
  function uploadCloud(o) {
    return _svg('<polyline points="16 16 12 12 8 16"/><line x1="12" x2="12" y1="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>', o);
  }
  function playSquare(o) {
    return _svg('<rect width="18" height="18" x="3" y="3" rx="2"/><polygon points="10 8 16 12 10 16 10 8"/>', o);
  }

  // ── 11. UI & Navegação ────────────────────────────────────────
  function arrowUpRight(o) {
    return _svg('<line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/>', o);
  }
  function arrowRight(o) {
    return _svg('<line x1="5" x2="19" y1="12" y2="12"/><polyline points="12 5 19 12 12 19"/>', o);
  }
  function arrowLeft(o) {
    return _svg('<line x1="19" x2="5" y1="12" y2="12"/><polyline points="12 19 5 12 12 5"/>', o);
  }
  function settings(o) {
    return _svg('<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>', o);
  }
  function grid(o) {
    return _svg('<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>', o);
  }
  function info(o) {
    return _svg('<circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="16" y2="12"/><line x1="12" x2="12.01" y1="8" y2="8"/>', o);
  }
  function flag(o) {
    return _svg('<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" x2="4" y1="22" y2="15"/>', o);
  }

  // ── Mapa de Ícones & Aliases ──────────────────────────────────
  const iconMap = {
    fileText, scrollText, clipboardCheck,
    academicCap, institution, landmark, bookOpen,
    scale, shieldCheck, lock, keyRound, tag,
    coins, banknote, creditCard, trendingUp, barChart,
    handshake, users, user, lightning, target,
    laptop, cpu, layers, folder, folderGit, lightbulb, smartphone, globe, externalLink,
    megaphone, palette, mic, messageSquare,
    sprout, batteryCharging, truck, heartPulse,
    map, refreshCw, repeat, flaskConical, paperclip, rocket, briefcase, helpCircle,
    checkCircle, check, xCircle, x, alertTriangle, bell, clock, calendar, award, sparkles, flame, inbox, bot, mapPin, uploadCloud, playSquare,
    arrowUpRight, arrowRight, arrowLeft, settings, grid, info, flag,
  };

  const aliases = {
    zap: 'lightning',
    balance: 'scale',
    money: 'coins',
    doc: 'fileText',
    search: 'externalLink',
    warning: 'alertTriangle',
    close: 'x',
    danger: 'alertTriangle',
    success: 'checkCircle',
    team: 'users',
    idea: 'lightbulb',
    university: 'institution',
    law: 'scale',
    flagAo: 'flag',
    // Emoji fallbacks
    '💡': 'lightbulb',
    '🎓': 'academicCap',
    '⚖️': 'scale',
    '⚖': 'scale',
    '🤝': 'handshake',
    '💰': 'coins',
    '💵': 'banknote',
    '💳': 'creditCard',
    '💻': 'laptop',
    '⚙️': 'cpu',
    '⚙': 'cpu',
    '📊': 'barChart',
    '📈': 'trendingUp',
    '📢': 'megaphone',
    '🎨': 'palette',
    '🎯': 'target',
    '🌾': 'sprout',
    '🔋': 'batteryCharging',
    '🏗': 'layers',
    '📁': 'folderGit',
    '🔗': 'externalLink',
    '🌐': 'globe',
    '🌍': 'globe',
    '📱': 'smartphone',
    '🏛': 'landmark',
    '🏫': 'institution',
    '📜': 'scrollText',
    '🔒': 'lock',
    '🔐': 'keyRound',
    '🏷': 'tag',
    '🗣': 'mic',
    '🎤': 'mic',
    '👤': 'user',
    '👥': 'users',
    '💬': 'messageSquare',
    '📍': 'mapPin',
    '📣': 'megaphone',
    '📭': 'inbox',
    '⏰': 'clock',
    '⏳': 'clock',
    '📅': 'calendar',
    '⚡': 'lightning',
    '✅': 'checkCircle',
    '✓': 'check',
    '✗': 'x',
    '❌': 'xCircle',
    '⚠️': 'alertTriangle',
    '⚠': 'alertTriangle',
    '🔔': 'bell',
    '🏅': 'award',
    '💫': 'sparkles',
    '🔥': 'flame',
    '🤖': 'bot',
    '📄': 'fileText',
    '📋': 'clipboardCheck',
    '📤': 'uploadCloud',
    '📺': 'playSquare',
    '👋': 'handshake',
    '📚': 'bookOpen',
    '🚚': 'truck',
    '🏥': 'heartPulse',
    '🗺': 'map',
    '🌀': 'refreshCw',
    '🔄': 'repeat',
    '🔬': 'flaskConical',
    '📎': 'paperclip',
    '🚀': 'rocket',
    '🇦🇴': 'flag',
  };

  // ── Helper de Wrappers ───────────────────────────────────────
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

  function get(name, opts = {}) {
    const resolvedName = aliases[name] || name;
    const fn = iconMap[resolvedName];
    if (!fn) {
      console.warn(`[KubicaIcons] Ícone "${name}" não encontrado.`);
      return '';
    }
    return fn(opts);
  }

  // ── Substituição Automática de Elementos no DOM ───────────────
  function replaceElements(root = document) {
    const elements = root.querySelectorAll('[data-kubica-icon], [data-icon]');
    elements.forEach(el => {
      const name = el.getAttribute('data-kubica-icon') || el.getAttribute('data-icon');
      const size = parseInt(el.getAttribute('data-size') || '18', 10);
      const color = el.getAttribute('data-color') || 'currentColor';
      const strokeWidth = parseFloat(el.getAttribute('data-stroke') || '1.5');
      const svg = get(name, { size, color, strokeWidth });
      if (svg) {
        el.innerHTML = svg;
      }
    });
  }

  return {
    ...iconMap,
    kpiWrapper,
    get,
    replaceElements,
  };

})();

if (typeof document !== 'undefined') {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => KubicaIcons.replaceElements());
  } else {
    KubicaIcons.replaceElements();
  }
}
