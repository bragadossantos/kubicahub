/* ============================================================
   KUBICA HUB — Dashboard Module v2.0
   Sidebar, nav user info, notificações, route guards e helpers de UI
   ============================================================ */

const KubicaDash = (() => {

  // ── Estado ──────────────────────────────────────────────────
  let _user = null;
  let _unread = 0;

  // ── Inicializar Dashboard ────────────────────────────────────
  /**
   * @param {string|null} role  - 'inventor' | 'builder' | 'admin' | null (qualquer autenticado)
   * @param {string} activeNav - id do link activo na sidebar
   */
  async function init(role = null, activeNav = '') {
    // 1. Verificar autenticação via KubicaApp
    _user = KubicaApp.verificarAcesso(role);
    if (!_user) return;

    // 2. Renderizar sidebar + header
    _renderSidebar(activeNav);
    _renderHeader();

    // 3. Carregar notificações assincronamente
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
          <a href="${landingUrl}" class="k-sidebar__brand">
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
        
        <nav class="k-sidebar__nav">
          <ul class="k-sidebar__nav-list">
            ${links.map(l => `
              <li class="k-sidebar__nav-item">
                <a href="${l.href}" class="k-sidebar__nav-link ${activeNav === l.id ? 'k-sidebar__nav-link--active' : ''}" data-nav="${l.id}">
                  <span class="k-sidebar__nav-icon">${l.icon}</span>
                  <span class="k-sidebar__nav-label">${l.label}</span>
                  ${l.id === 'notificacoes' ? `<span class="k-badge k-badge--danger k-sidebar__nav-badge" id="sidebar-notif-count" style="display:${_unread > 0 ? 'inline-block' : 'none'};">${_unread}</span>` : ''}
                </a>
              </li>
            `).join('')}
          </ul>
        </nav>
        
        <div class="k-sidebar__footer">
          <button class="k-sidebar__logout" onclick="KubicaDash.logout()">
            <span>⇠</span> Terminar Sessão
          </button>
        </div>
      </aside>
      <div class="k-sidebar__overlay" id="k-sidebar-overlay" onclick="KubicaDash.closeSidebar()"></div>
    `;
    placeholder.innerHTML = html;
  }

  function _navLinks(role) {
    const common = [
      { id: 'notificacoes', href: 'notificacoes.html', icon: '🔔', label: 'Notificações' },
      { id: 'perfil',       href: 'perfil.html',       icon: '👤', label: 'O meu Perfil' },
    ];

    if (role === 'inventor') {
      return [
        { id: 'dashboard',    href: 'dashboard.html',      icon: '⬡', label: 'Painel' },
        { id: 'projetos',     href: 'projetos.html',       icon: '💡', label: 'Os meus Projectos' },
        { id: 'projeto-criar',href: 'projeto-criar.html',  icon: '＋', label: 'Novo Projecto' },
        { id: 'colaboracoes', href: 'colaboracoes.html',   icon: '🤝', label: 'Encontrar Builders' },
        ...common
      ];
    }
    if (role === 'builder') {
      return [
        { id: 'dashboard',        href: 'dashboard.html',        icon: '⬡', label: 'Painel' },
        { id: 'explorar',         href: 'explorar.html',         icon: '🔭', label: 'Explorar Ideias' },
        { id: 'propostas',        href: 'propostas.html',        icon: '📋', label: 'Propostas' },
        { id: 'projetos-activos', href: 'projetos-activos.html', icon: '🚀', label: 'Projectos Activos' },
        ...common
      ];
    }
    if (role === 'admin') {
      return [
        { id: 'dashboard',     href: 'dashboard.html',     icon: '⬡', label: 'Painel Admin' },
        { id: 'projetos',      href: 'projetos.html',      icon: '💡', label: 'Gerir Projectos' },
        { id: 'utilizadores',  href: 'utilizadores.html',  icon: '👥', label: 'Utilizadores' },
        { id: 'relatorios',    href: 'relatorios.html',    icon: '📊', label: 'Sandbox / Funding' },
        { id: 'configuracoes', href: 'configuracoes.html', icon: '⚙', label: 'Configurações' },
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
        <button class="k-dash-header__burger" onclick="KubicaDash.openSidebar()" aria-label="Abrir menu">☰</button>
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
        _unread = res.data.unread_count || 0;
        const badge = document.getElementById('sidebar-notif-count');
        if (badge) {
          badge.textContent = _unread;
          badge.style.display = _unread > 0 ? 'inline-block' : 'none';
        }
      }
    } catch (_) { /* silencioso */ }
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

  /** Criar card HTML de KPI */
  function kpiCard(label, value, icon, change = null) {
    const changeHtml = change !== null
      ? `<span class="k-kpi__change ${change >= 0 ? 'k-kpi__change--up' : 'k-kpi__change--down'}">${change >= 0 ? '▲' : '▼'} ${Math.abs(change)}%</span>`
      : '';
    return `
      <div class="k-kpi">
        <div class="k-kpi__icon">${icon}</div>
        <div class="k-kpi__body">
          <div class="k-kpi__value">${value}</div>
          <div class="k-kpi__label">${label}</div>
          ${changeHtml}
        </div>
      </div>
    `;
  }

  /** Status badge */
  function statusBadge(status) {
    const map = {
      approved:       ['✓ Aprovado',         'success'],
      pending:        ['⏳ Pendente',         'warning'],
      pending_review: ['🔍 Em Revisão',       'warning'],
      accepted:       ['✓ Aceite',           'success'],
      declined:       ['✗ Recusado',         'danger'],
      matched:        ['🤝 Em Equipa',        'info'],
      incubating:     ['🚀 Em Incubação',     'info'],
      rejected:       ['✗ Rejeitado',         'danger'],
      draft:          ['✏ Rascunho',         'muted'],
      signed:         ['✎ Assinado',          'success'],
      open:           ['🔓 Aberto',           'warning'],
      resolved:       ['✓ Resolvido',         'success'],
    };
    const [label, variant] = map[status] || [status, 'muted'];
    return `<span class="k-badge k-badge--${variant}">${label}</span>`;
  }

  /** Mostrar spinner de carregamento num container */
  function showLoading(containerId) {
    const el = document.getElementById(containerId);
    if (el) el.innerHTML = '<div class="k-loading"><div class="k-spinner"></div><p>A carregar...</p></div>';
  }

  /** Mostrar estado vazio */
  function showEmpty(containerId, msg = 'Sem dados disponíveis.', icon = '📭') {
    const el = document.getElementById(containerId);
    if (el) el.innerHTML = `<div class="k-empty"><span class="k-empty__icon">${icon}</span><p>${msg}</p></div>`;
  }

  /** Mostrar erro num container */
  function showError(containerId, msg = 'Erro ao carregar dados.') {
    const el = document.getElementById(containerId);
    if (el) el.innerHTML = `<div class="k-error-block"><p>⚠ ${msg}</p></div>`;
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

  /** Mostrar toast */
  function toast(msg, type = 'success') {
    let container = document.getElementById('k-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'k-toast-container';
      container.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;';
      document.body.appendChild(container);
    }
    const t = document.createElement('div');
    const bg = type === 'success' ? '#14532d' : type === 'error' ? '#7f1d1d' : '#1e293b';
    const border = type === 'success' ? '#22c55e' : type === 'error' ? '#ef4444' : '#64748b';
    t.style.cssText = `background:${bg};border:1px solid ${border};color:#fff;padding:12px 20px;border-radius:8px;font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,0.5);transition:all 0.3s;opacity:0;transform:translateY(10px);`;
    t.textContent = msg;
    container.appendChild(t);
    setTimeout(() => { t.style.opacity = '1'; t.style.transform = 'translateY(0)'; }, 10);
    setTimeout(() => {
      t.style.opacity = '0';
      t.style.transform = 'translateY(10px)';
      setTimeout(() => t.remove(), 300);
    }, 4000);
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
