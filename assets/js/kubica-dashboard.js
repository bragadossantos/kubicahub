/* ============================================================
   KUBICA HUB — Dashboard Module v3.0
   Sidebar, header, notificações, route guards e helpers de UI.
   ZERO emojis — todos os ícones via KubicaIcons SVG.
   ============================================================ */

const KubicaDash = (() => {

  // ── Estado ──────────────────────────────────────────────────
  let _user = null;
  let _unread = 0;

  // ── Inicializar Dashboard ────────────────────────────────────
  /**
   * @param {string|null} role  - 'inventor' | 'builder' | 'admin' | null
   * @param {string} activeNav  - id do link activo na sidebar
   */
  async function init(role = null, activeNav = '') {
    _user = KubicaApp.verificarAcesso(role);
    if (!_user) return;

    _renderSidebar(activeNav);
    _renderHeader();
    _loadNotifications();
  }

  // ── Sidebar ─────────────────────────────────────────────────
  function _renderSidebar(activeNav) {
    const placeholder = document.getElementById('k-sidebar-placeholder');
    if (!placeholder) return;

    const role = _user.role;
    const links = _navLinks(role);
    const landingUrl = `${KubicaApp.getBasePath()}/paginas/landing.html`;

    const html = `
      <aside class="k-sidebar" id="k-sidebar">
        <div class="k-sidebar__logo">
          <a href="${landingUrl}" class="k-sidebar__brand" aria-label="Kubica Hub — início">
            <span class="k-sidebar__brand-mark">K</span>
            <span class="k-sidebar__brand-name">Kubica Hub</span>
          </a>
        </div>

        <div class="k-sidebar__user">
          <div class="k-sidebar__avatar">${(_user.name || 'U').charAt(0).toUpperCase()}</div>
          <div class="k-sidebar__user-info">
            <span class="k-sidebar__user-name">${(_user.name || 'Utilizador').split(' ')[0]}</span>
            <span class="k-sidebar__user-role k-badge k-badge--${role}">${_roleLabel(role)}</span>
          </div>
        </div>

        <nav class="k-sidebar__nav" aria-label="Navegação do painel">
          <ul class="k-sidebar__nav-list">
            ${links.map(l => `
              <li class="k-sidebar__nav-item">
                <a href="${l.href}"
                   class="k-sidebar__nav-link ${activeNav === l.id ? 'k-sidebar__nav-link--active' : ''}"
                   data-nav="${l.id}"
                   aria-current="${activeNav === l.id ? 'page' : 'false'}">
                  <span class="k-sidebar__nav-icon" aria-hidden="true">${l.icon}</span>
                  <span class="k-sidebar__nav-label">${l.label}</span>
                  ${l.id === 'notificacoes'
                    ? `<span class="k-badge k-badge--danger k-sidebar__nav-badge"
                              id="sidebar-notif-count"
                              style="display:${_unread > 0 ? 'inline-block' : 'none'};">${_unread}</span>`
                    : ''}
                </a>
              </li>
            `).join('')}
          </ul>
        </nav>

        <div class="k-sidebar__footer">
          <button class="k-sidebar__logout" onclick="KubicaDash.logout()" type="button">
            ${KubicaIcons.get('arrowUpRight', { size: 16 })} Terminar Sessão
          </button>
        </div>
      </aside>
      <div class="k-sidebar__overlay" id="k-sidebar-overlay" onclick="KubicaDash.closeSidebar()"></div>
    `;
    placeholder.innerHTML = html;
  }

  function _navLinks(role) {
    const common = [
      { id: 'notificacoes', href: 'notificacoes.html', icon: KubicaIcons.bell({ size: 16 }),          label: 'Notificações' },
      { id: 'perfil',       href: 'perfil.html',       icon: KubicaIcons.users({ size: 16 }),          label: 'O meu Perfil' },
    ];

    if (role === 'inventor') {
      return [
        { id: 'dashboard',     href: 'dashboard.html',     icon: KubicaIcons.grid({ size: 16 }),       label: 'Painel' },
        { id: 'projetos',      href: 'projetos.html',      icon: KubicaIcons.lightbulb({ size: 16 }),  label: 'Os meus Projectos' },
        { id: 'projeto-criar', href: 'projeto-criar.html', icon: KubicaIcons.lightning({ size: 16 }),  label: 'Novo Projecto' },
        { id: 'colaboracoes',  href: 'colaboracoes.html',  icon: KubicaIcons.handshake({ size: 16 }),  label: 'Encontrar Builders' },
        ...common
      ];
    }

    if (role === 'builder') {
      return [
        { id: 'dashboard',        href: 'dashboard.html',        icon: KubicaIcons.grid({ size: 16 }),      label: 'Painel' },
        { id: 'explorar',         href: 'explorar.html',         icon: KubicaIcons.arrowUpRight({ size: 16}),label: 'Explorar Ideias' },
        { id: 'propostas',        href: 'propostas.html',        icon: KubicaIcons.fileText({ size: 16 }),   label: 'Propostas' },
        { id: 'projetos-activos', href: 'projetos-activos.html', icon: KubicaIcons.lightning({ size: 16 }),  label: 'Projectos Activos' },
        ...common
      ];
    }

    if (role === 'admin') {
      return [
        { id: 'dashboard',     href: 'dashboard.html',     icon: KubicaIcons.grid({ size: 16 }),      label: 'Painel Admin' },
        { id: 'projetos',      href: 'projetos.html',      icon: KubicaIcons.folder({ size: 16 }),    label: 'Gerir Projectos' },
        { id: 'utilizadores',  href: 'utilizadores.html',  icon: KubicaIcons.users({ size: 16 }),     label: 'Utilizadores' },
        { id: 'relatorios',    href: 'relatorios.html',    icon: KubicaIcons.barChart({ size: 16 }),  label: 'Sandbox / Funding' },
        { id: 'configuracoes', href: 'configuracoes.html', icon: KubicaIcons.settings({ size: 16 }), label: 'Configurações' },
      ];
    }

    return common;
  }

  function _roleLabel(role) {
    return { inventor: 'Inventor', builder: 'Builder', admin: 'Admin', mentor: 'Mentor' }[role] || role;
  }

  // ── Header ───────────────────────────────────────────────────
  function _renderHeader() {
    const placeholder = document.getElementById('k-header-placeholder');
    if (!placeholder) return;
    placeholder.innerHTML = `
      <header class="k-dash-header">
        <button class="k-dash-header__burger"
                onclick="KubicaDash.openSidebar()"
                aria-label="Abrir menu lateral"
                type="button">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true">
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
        </button>
        <div class="k-dash-header__right">
          <span class="k-dash-header__user">Olá, ${(_user.name || 'Utilizador').split(' ')[0]}</span>
        </div>
      </header>
    `;
  }

  // ── Notificações ─────────────────────────────────────────────
  async function _loadNotifications() {
    try {
      const res = await KubicaApp.api('/notifications?limite=5');
      if (res && res.success) {
        _unread = res.data?.unread_count || 0;
        const badge = document.getElementById('sidebar-notif-count');
        if (badge) {
          badge.textContent = _unread;
          badge.style.display = _unread > 0 ? 'inline-block' : 'none';
        }
      }
    } catch (_) { /* silencioso — api offline gerida pelo api-client */ }
  }

  // ── Sidebar Mobile ───────────────────────────────────────────
  function openSidebar() {
    document.getElementById('k-sidebar')?.classList.add('k-sidebar--open');
    document.getElementById('k-sidebar-overlay')?.classList.add('k-sidebar__overlay--visible');
  }

  function closeSidebar() {
    document.getElementById('k-sidebar')?.classList.remove('k-sidebar--open');
    document.getElementById('k-sidebar-overlay')?.classList.remove('k-sidebar__overlay--visible');
  }

  // ── Logout ───────────────────────────────────────────────────
  async function logout() {
    await KubicaApp.logout();
  }

  // ── Utilitários de UI ────────────────────────────────────────

  /** Card HTML de KPI — com ícone SVG, sem emojis */
  function kpiCard(label, value, iconName, change = null) {
    const iconHtml = typeof iconName === 'string'
      ? KubicaIcons.kpiWrapper(KubicaIcons.get(iconName, { size: 20 }))
      : KubicaIcons.kpiWrapper(iconName); // aceita SVG raw também

    const changeHtml = change !== null
      ? `<span class="k-kpi__change ${change >= 0 ? 'k-kpi__change--up' : 'k-kpi__change--down'}">
           ${change >= 0
             ? '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>'
             : '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>'}
           ${Math.abs(change)}%
         </span>`
      : '';

    return `
      <div class="k-kpi">
        ${iconHtml}
        <div class="k-kpi__body">
          <div class="k-kpi__value">${value}</div>
          <div class="k-kpi__label">${label}</div>
          ${changeHtml}
        </div>
      </div>
    `;
  }

  /** Status badge sem emojis */
  function statusBadge(status) {
    const map = {
      approved:       ['Aprovado',       'success', 'checkCircle'],
      pending:        ['Pendente',        'warning', 'clock'],
      pending_review: ['Em Revisão',      'warning', 'clock'],
      accepted:       ['Aceite',          'success', 'checkCircle'],
      declined:       ['Recusado',        'danger',  'lock'],
      matched:        ['Em Equipa',       'info',    'users'],
      incubating:     ['Em Incubação',    'info',    'lightning'],
      rejected:       ['Rejeitado',       'danger',  'lock'],
      draft:          ['Rascunho',        'muted',   'fileText'],
      signed:         ['Assinado',        'success', 'shieldCheck'],
      open:           ['Aberto',          'warning', 'arrowUpRight'],
      resolved:       ['Resolvido',       'success', 'checkCircle'],
    };
    const [label, variant, icon] = map[status] || [status, 'muted', 'fileText'];
    const svg = KubicaIcons.get(icon, { size: 11 });
    return `<span class="k-badge k-badge--${variant}" style="display:inline-flex;align-items:center;gap:4px;">${svg}${label}</span>`;
  }

  /** Spinner de carregamento */
  function showLoading(containerId) {
    const el = document.getElementById(containerId);
    if (el) el.innerHTML = '<div class="k-loading"><div class="k-spinner"></div><p>A carregar...</p></div>';
  }

  /** Estado vazio — sem emoji, usa SVG */
  function showEmpty(containerId, msg = 'Sem dados disponíveis.', iconName = 'fileText') {
    const el = document.getElementById(containerId);
    if (!el) return;
    const iconSvg = KubicaIcons.get(iconName, { size: 32, color: 'var(--color-driftwood)' });
    el.innerHTML = `
      <div class="k-empty">
        <div class="k-empty__icon" aria-hidden="true">${iconSvg}</div>
        <p>${msg}</p>
      </div>`;
  }

  /** Bloco de erro */
  function showError(containerId, msg = 'Erro ao carregar dados.') {
    const el = document.getElementById(containerId);
    if (!el) return;
    const icon = KubicaIcons.shieldCheck({ size: 20, color: 'var(--color-danger, #f87171)' });
    el.innerHTML = `<div class="k-error-block">${icon}<p>${msg}</p></div>`;
  }

  /** Formatar data legível */
  function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('pt-AO', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  /** Formatar moeda */
  function formatCurrency(val) {
    return 'USD ' + Number(val || 0).toLocaleString('pt-AO', { minimumFractionDigits: 2 });
  }

  /** Toast sem emoji */
  function toast(msg, type = 'success') {
    let container = document.getElementById('k-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'k-toast-container';
      container.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;max-width:380px;';
      document.body.appendChild(container);
    }
    const colorMap = {
      success: { bg: 'rgba(20,83,45,0.95)',  border: 'rgba(74,222,128,0.4)',  icon: 'checkCircle' },
      error:   { bg: 'rgba(127,29,29,0.95)', border: 'rgba(248,113,113,0.4)', icon: 'lock' },
      warning: { bg: 'rgba(78,65,0,0.95)',   border: 'rgba(255,180,66,0.4)',  icon: 'clock' },
      info:    { bg: 'rgba(10,20,40,0.95)',  border: 'rgba(96,165,250,0.4)', icon: 'arrowUpRight' },
    };
    const c = colorMap[type] || colorMap.info;
    const iconHtml = KubicaIcons.get(c.icon, { size: 16 });
    const t = document.createElement('div');
    t.style.cssText = `
      background:${c.bg}; border:1px solid ${c.border};
      color:var(--color-warm-cream,#fff1e0); padding:12px 16px;
      border-radius:6px; font-size:13px; line-height:1.4;
      display:flex; align-items:center; gap:10px;
      transition:all 0.25s ease; opacity:0; transform:translateY(8px);
      backdrop-filter:blur(8px);`;
    t.innerHTML = `<span style="flex-shrink:0;color:${c.border};">${iconHtml}</span><span>${msg}</span>`;
    container.appendChild(t);
    requestAnimationFrame(() => {
      t.style.opacity = '1';
      t.style.transform = 'translateY(0)';
    });
    setTimeout(() => {
      t.style.opacity = '0';
      t.style.transform = 'translateY(8px)';
      setTimeout(() => t.remove(), 280);
    }, 4200);
  }

  return {
    init, logout,
    openSidebar, closeSidebar,
    kpiCard, statusBadge,
    showLoading, showEmpty, showError,
    formatDate, formatCurrency, toast,
    getUser: () => _user || KubicaApp.getUser(),
  };

})();
