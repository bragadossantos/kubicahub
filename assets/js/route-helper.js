/* ============================================================
   KUBICA HUB — Route Helper v1.0
   Navegação unificada entre as ~40 telas do sistema.
   Funciona transparentemente em 3 contextos:
     1. Ficheiro aberto diretamente (file:///)
     2. Servidor em localhost/kubica/ (raiz do repo)
     3. Servidor em localhost/kubica/public/ (DocumentRoot = public/)
   ============================================================ */

const KubicaRouter = (() => {

  // ── Deteção de Contexto ──────────────────────────────────────
  function _detectContext() {
    const proto = window.location.protocol;
    const path  = window.location.pathname;

    if (proto === 'file:') return 'file';
    if (path.includes('/kubica/public/')) return 'public-sub';   // Apache, /kubica/public/ como raiz
    if (path.includes('/public/paginas/')) return 'public-sub';
    if (path.includes('/kubica/')) return 'repo-root';            // Apache, /kubica/ como raiz
    return 'repo-root'; // fallback
  }

  // ── Resolver o prefixo de base conforme contexto ─────────────
  function _baseFor(context) {
    const path = window.location.pathname;
    if (context === 'file') {
      // Constrói o caminho absoluto do dir. raiz kubica/
      const match = path.match(/^(.+\/kubica\/)/i);
      return match ? match[1].replace(/\/$/, '') : '';
    }
    if (path.includes('/kubica/public/')) return '/kubica/public';
    if (path.includes('/kubica/'))        return '/kubica';
    if (path.includes('/public/'))        return '/public';
    return '';
  }

  // ── Mapa Mestre de Telas ─────────────────────────────────────
  // key => { root: 'caminho desde kubica/', public: 'caminho desde public/' }
  const SCREEN_MAP = {
    // Públicas / Landing
    'landing'      : { root: 'public/paginas/landing.html',       public: 'paginas/landing.html' },
    'index'        : { root: 'index.html',                        public: '../index.html' },

    // Auth
    'login'        : { root: 'public/paginas/auth/login.html',    public: 'paginas/auth/login.html' },
    'registo'      : { root: 'public/paginas/auth/registo.html',  public: 'paginas/auth/registo.html' },

    // Onboarding
    'onb-01'       : { root: 'onb-01-gateway.html',               public: '../onb-01-gateway.html' },
    'onb-02'       : { root: 'onb-02-auth.html',                  public: '../onb-02-auth.html' },
    'onb-03'       : { root: 'onb-03-inventor.html',              public: '../onb-03-inventor.html' },
    'onb-04'       : { root: 'onb-04-builder.html',               public: '../onb-04-builder.html' },
    'onb-05'       : { root: 'onb-05-welcome.html',               public: '../onb-05-welcome.html' },

    // Pipeline de Ideias
    'ide-01'       : { root: 'ide-01-submit.html',                public: '../ide-01-submit.html' },
    'ide-02'       : { root: 'ide-02-feed.html',                  public: '../ide-02-feed.html' },
    'ide-03'       : { root: 'ide-03-detail.html',                public: '../ide-03-detail.html' },
    'ide-04'       : { root: 'ide-04-feedback.html',              public: '../ide-04-feedback.html' },

    // Matchmaking
    'mat-01'       : { root: 'mat-01-builders.html',              public: '../mat-01-builders.html' },
    'mat-02'       : { root: 'mat-02-profile.html',               public: '../mat-02-profile.html' },
    'mat-03'       : { root: 'mat-03-speeddate.html',             public: '../mat-03-speeddate.html' },
    'mat-04'       : { root: 'mat-04-proposal.html',              public: '../mat-04-proposal.html' },
    'mat-05'       : { root: 'mat-05-confirm.html',               public: '../mat-05-confirm.html' },

    // Incubação
    'inc-01'       : { root: 'inc-01-dashboard.html',             public: '../inc-01-dashboard.html' },
    'inc-02'       : { root: 'inc-02-thinking.html',              public: '../inc-02-thinking.html' },
    'inc-03'       : { root: 'inc-03-deliverables.html',          public: '../inc-03-deliverables.html' },
    'inc-04'       : { root: 'inc-04-mentoring.html',             public: '../inc-04-mentoring.html' },
    'inc-05'       : { root: 'inc-05-traction.html',              public: '../inc-05-traction.html' },
    'inc-06'       : { root: 'inc-06-mediation.html',             public: '../inc-06-mediation.html' },

    // Governança
    'gov-01'       : { root: 'gov-01-vesting.html',               public: '../gov-01-vesting.html' },
    'gov-02'       : { root: 'gov-02-iapi.html',                  public: '../gov-02-iapi.html' },
    'gov-03'       : { root: 'gov-03-sandbox.html',               public: '../gov-03-sandbox.html' },
    'gov-04'       : { root: 'gov-04-documents.html',             public: '../gov-04-documents.html' },
    'gov-05'       : { root: 'gov-05-compliance.html',            public: '../gov-05-compliance.html' },

    // Graduação
    'grad-01'      : { root: 'grad-01-tccs.html',                 public: '../grad-01-tccs.html' },
    'grad-02'      : { root: 'grad-02-demoday.html',              public: '../grad-02-demoday.html' },
    'grad-03'      : { root: 'grad-03-portfolio.html',            public: '../grad-03-portfolio.html' },

    // Admin
    'adm-01'       : { root: 'adm-01-dashboard.html',             public: '../adm-01-dashboard.html' },
    'adm-02'       : { root: 'adm-02-matching.html',              public: '../adm-02-matching.html' },
    'adm-03'       : { root: 'adm-03-funding.html',               public: '../adm-03-funding.html' },
    'adm-04'       : { root: 'adm-04-mentors.html',               public: '../adm-04-mentors.html' },

    // Hub Público
    'hub-01'       : { root: 'hub-01-matrix.html',                public: '../hub-01-matrix.html' },
    'hub-02'       : { root: 'hub-02-startups.html',              public: '../hub-02-startups.html' },
    'about-fkcu'   : { root: 'about-fkcu.html',                   public: '../about-fkcu.html' },

    // Dashboards por papel
    'dash-inventor': { root: 'public/paginas/inventor/dashboard.html', public: 'paginas/inventor/dashboard.html' },
    'dash-builder' : { root: 'public/paginas/builder/dashboard.html',  public: 'paginas/builder/dashboard.html' },
    'dash-admin'   : { root: 'public/paginas/admin/dashboard.html',    public: 'paginas/admin/dashboard.html' },
  };

  // ── Resolver URL absoluta para uma chave de tela ─────────────
  /**
   * Resolve o URL completo de navegação para uma tela.
   * @param {string} screenKey - chave no SCREEN_MAP (ex: 'ide-02', 'login')
   * @param {Object} params    - query params a anexar (ex: { id: 1, role: 'CLO' })
   * @returns {string} URL pronta para uso em href ou window.location.href
   */
  function href(screenKey, params = {}) {
    const context = _detectContext();
    const base    = _baseFor(context);
    const entry   = SCREEN_MAP[screenKey];

    if (!entry) {
      console.warn(`[KubicaRouter] Tela desconhecida: "${screenKey}". Verifique o SCREEN_MAP.`);
      return '#';
    }

    // No contexto file:// usamos caminho absoluto baseado na raiz do repo
    // Nos contextos web usamos caminhos relativos ao servidor
    let path;
    if (context === 'file') {
      path = `${base}/${entry.root}`;
    } else if (context === 'public-sub') {
      // Estamos dentro de public/ ou kubica/public/
      path = `${base}/${entry.public}`;
    } else {
      // repo-root: kubica/ é a raiz
      path = `${base}/${entry.root}`;
    }

    // Normalizar duplas barras
    path = path.replace(/\/\//g, '/');
    if (context === 'file') {
      path = path.replace(/^\//, '');  // file:// não precisa de / inicial
    }

    // Anexar query params
    const qs = Object.keys(params).length
      ? '?' + new URLSearchParams(params).toString()
      : '';

    return path + qs;
  }

  /**
   * Navega imediatamente para uma tela.
   * @param {string} screenKey
   * @param {Object} params
   */
  function navigateTo(screenKey, params = {}) {
    window.location.href = href(screenKey, params);
  }

  /**
   * Retorna o URL relativo simples entre duas telas do mesmo nível.
   * Útil para âncoras dentro das telas da jornada (onb, ide, mat, etc.)
   * @param {string} screenKey
   */
  function hrefRelative(screenKey) {
    const entry = SCREEN_MAP[screenKey];
    if (!entry) return '#';
    // Extrai somente o nome do ficheiro
    const parts = entry.root.split('/');
    return parts[parts.length - 1];
  }

  /**
   * Detecta a chave ativa baseada na pathname atual.
   */
  function currentScreen() {
    const path = window.location.pathname;
    const file = path.split('/').pop();
    return Object.entries(SCREEN_MAP).find(([, v]) => v.root.endsWith(file))?.[0] || null;
  }

  return { href, navigateTo, hrefRelative, currentScreen, SCREEN_MAP };

})();
