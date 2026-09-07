/* ============================================================
   KUBICA HUB — API Client Resiliente v1.0
   Estratégia: Backend PHP primeiro, LocalMockEngine como fallback.
   Se o servidor PHP estiver inacessível, todos os dados são
   lidos/escritos no localStorage com estrutura idêntica à API real.
   ============================================================ */

const KubicaAPI = (() => {

  // ── Estado do modo offline ────────────────────────────────────
  let _isOffline = false;
  let _bannerShown = false;

  // ── Seeds de dados mock ───────────────────────────────────────
  const MOCK_SEEDS = {
    'kubica_ideas': [
      {
        id: 'mock-001', title: 'AgriSat Angola',
        description: 'Plataforma de monitorização de colheitas por satélite para pequenos agricultores de Malange.',
        category: 'Agro-Tecnologia', status: 'pending_review',
        team_roles_needed: ['CTO', 'CMO'], created_at: new Date(Date.now() - 86400000 * 5).toISOString(),
        university: 'Universidade Agostinho Neto', innovation_score: 87,
      },
      {
        id: 'mock-002', title: 'LegalDok',
        description: 'Automação de contratos legais para PMEs angolanas via IA generativa.',
        category: 'LegalTech', status: 'approved',
        team_roles_needed: ['CLO', 'CTO'], created_at: new Date(Date.now() - 86400000 * 12).toISOString(),
        university: 'Universidade Católica de Angola', innovation_score: 92,
      },
      {
        id: 'mock-003', title: 'FinKwanza',
        description: 'Carteira digital multi-banco e sistema de micropagamentos para o mercado informal.',
        category: 'FinTech', status: 'matched',
        team_roles_needed: ['CFO'], created_at: new Date(Date.now() - 86400000 * 20).toISOString(),
        university: 'Universidade Metodista de Angola', innovation_score: 95,
      },
      {
        id: 'mock-004', title: 'MedicaNet',
        description: 'Sistema de telemedicina B2B para clínicas municipais com prontuário eletrônico offline-first.',
        category: 'HealthTech', status: 'incubating',
        team_roles_needed: [], created_at: new Date(Date.now() - 86400000 * 35).toISOString(),
        university: 'Universidade Jean Piaget de Angola', innovation_score: 88,
      },
      {
        id: 'mock-005', title: 'EduLocal',
        description: 'Plataforma de aprendizagem adaptativa para o ensino primário em zonas rurais, offline.',
        category: 'EdTech', status: 'draft',
        team_roles_needed: ['CEO', 'CMO', 'CTO'], created_at: new Date(Date.now() - 86400000 * 2).toISOString(),
        university: 'Universidade Lusíada de Angola', innovation_score: 78,
      },
      {
        id: 'mock-006', title: 'SolarKit',
        description: 'Kits solares modulares pay-as-you-go para habitações sem acesso à rede elétrica nacional.',
        category: 'CleanTech', status: 'pending',
        team_roles_needed: ['CTO', 'CFO'], created_at: new Date(Date.now() - 86400000 * 8).toISOString(),
        university: 'Instituto Politécnico de Luanda', innovation_score: 82,
      },
    ],
    'kubica_matches': [],
    'kubica_teams': [],
    'kubica_notifications': [],
    'kubica_vesting': [],
  };

  // ── Inicializar seeds se as chaves não existirem ──────────────
  function _initSeeds() {
    Object.entries(MOCK_SEEDS).forEach(([key, value]) => {
      if (!localStorage.getItem(key)) {
        localStorage.setItem(key, JSON.stringify(value));
      }
    });
  }

  // ── Banner "Modo Local Ativo" ─────────────────────────────────
  function _showOfflineBanner() {
    if (_bannerShown) return;
    _bannerShown = true;

    const banner = document.createElement('div');
    banner.id = 'k-offline-banner';
    banner.setAttribute('role', 'status');
    banner.setAttribute('aria-live', 'polite');
    banner.style.cssText = `
      position: fixed;
      bottom: 0; left: 0; right: 0;
      z-index: 9998;
      background: rgba(11, 6, 0, 0.95);
      border-top: 1px solid var(--color-walnut, #43392d);
      color: var(--color-driftwood, #85796c);
      font-size: 12px;
      font-family: var(--font-base, 'Inter', sans-serif);
      padding: 8px 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      backdrop-filter: blur(8px);
    `;

    const dot = document.createElement('span');
    dot.style.cssText = `
      display: inline-block;
      width: 7px; height: 7px;
      border-radius: 9999px;
      background: var(--color-amber-forge, #ffb442);
      animation: k-pulse-dot 2s ease infinite;
      flex-shrink: 0;
    `;

    // Injeta keyframes se ainda não existirem
    if (!document.getElementById('k-pulse-dot-style')) {
      const style = document.createElement('style');
      style.id = 'k-pulse-dot-style';
      style.textContent = `
        @keyframes k-pulse-dot {
          0%, 100% { opacity: 1; transform: scale(1); }
          50%       { opacity: 0.4; transform: scale(0.75); }
        }
      `;
      document.head.appendChild(style);
    }

    const msg = document.createElement('span');
    msg.textContent = 'Modo Local Ativo — Servidor PHP não detectado. Os dados estão a ser persistidos no navegador.';

    const dismiss = document.createElement('button');
    dismiss.textContent = 'Dispensar';
    dismiss.style.cssText = `
      margin-left: auto;
      background: none;
      border: 1px solid var(--color-walnut, #43392d);
      color: var(--color-driftwood, #85796c);
      padding: 3px 10px;
      border-radius: 9999px;
      cursor: pointer;
      font-size: 11px;
    `;
    dismiss.onclick = () => banner.remove();

    banner.appendChild(dot);
    banner.appendChild(msg);
    banner.appendChild(dismiss);
    document.body.appendChild(banner);
  }

  // ── LocalMockEngine — Roteador de endpoints ───────────────────
  const LocalMock = {

    /** GET /api/v1/ideas */
    'GET /ideas': () => {
      const ideas = JSON.parse(localStorage.getItem('kubica_ideas') || '[]');
      return { success: true, data: { items: ideas, total: ideas.length } };
    },

    /** GET /api/v1/ideas/:id */
    'GET /ideas/:id': (params) => {
      const ideas = JSON.parse(localStorage.getItem('kubica_ideas') || '[]');
      const idea = ideas.find(i => i.id === params.id);
      return idea
        ? { success: true, data: idea }
        : { success: false, message: 'Ideia não encontrada', status: 404 };
    },

    /** POST /api/v1/ideas */
    'POST /ideas': (_, body) => {
      const ideas = JSON.parse(localStorage.getItem('kubica_ideas') || '[]');
      const newIdea = {
        id: 'mock-' + Date.now(),
        ...body,
        status: 'draft',
        created_at: new Date().toISOString(),
        innovation_score: Math.floor(60 + Math.random() * 35),
      };
      ideas.unshift(newIdea);
      localStorage.setItem('kubica_ideas', JSON.stringify(ideas));
      return { success: true, data: newIdea, message: 'Ideia criada (modo local).' };
    },

    /** GET /api/v1/dashboard/stats */
    'GET /dashboard/stats': () => {
      const ideas  = JSON.parse(localStorage.getItem('kubica_ideas') || '[]');
      const active = ideas.filter(i => ['matched', 'incubating'].includes(i.status)).length;
      return {
        success: true,
        data: {
          ideas_count:   ideas.length,
          active_count:  active,
          match_score:   Math.round(67 + Math.random() * 20),
          funding_pool:  '50,000 USD',
          phase:         ideas.some(i => i.status === 'incubating') ? 'Incubação' : 'Matchmaking',
        }
      };
    },

    /** GET /api/v1/notifications */
    'GET /notifications': () => {
      const notifs = JSON.parse(localStorage.getItem('kubica_notifications') || '[]');
      return { success: true, data: { items: notifs, unread_count: notifs.filter(n => !n.read).length } };
    },

    /** GET /api/v1/teams */
    'GET /teams': () => {
      const teams = JSON.parse(localStorage.getItem('kubica_teams') || '[]');
      return { success: true, data: { items: teams } };
    },

    /** POST /api/v1/matches/propose */
    'POST /matches/propose': (_, body) => {
      const matches = JSON.parse(localStorage.getItem('kubica_matches') || '[]');
      const m = { id: 'match-' + Date.now(), ...body, status: 'pending', proposed_at: new Date().toISOString() };
      matches.push(m);
      localStorage.setItem('kubica_matches', JSON.stringify(matches));
      return { success: true, data: m, message: 'Proposta enviada (modo local).' };
    },

    /** Fallback genérico */
    '_default': (method, endpoint) => {
      console.info(`[KubicaAPI LocalMock] Endpoint sem handler: ${method} ${endpoint}. Retornando mock vazio.`);
      return { success: true, data: {}, _mock: true };
    },
  };

  /**
   * Dispatch do LocalMockEngine — extrai parâmetros de rota dinâmica.
   */
  function _mockDispatch(method, endpoint, body) {
    // Normalizar endpoint (remover query string)
    const cleanPath = endpoint.split('?')[0].replace(/^\/api\/v1/, '');

    // Tentar match exato primeiro
    const exactKey = `${method} ${cleanPath}`;
    if (LocalMock[exactKey]) return LocalMock[exactKey]({}, body);

    // Match com parâmetro de rota (:id, :slug, etc.)
    for (const [key, handler] of Object.entries(LocalMock)) {
      if (key.startsWith('_')) continue;
      const [kMethod, kPath] = key.split(' ');
      if (kMethod !== method) continue;

      const kParts = kPath.split('/');
      const eParts = cleanPath.split('/');
      if (kParts.length !== eParts.length) continue;

      const params = {};
      let matched = true;
      for (let i = 0; i < kParts.length; i++) {
        if (kParts[i].startsWith(':')) {
          params[kParts[i].slice(1)] = eParts[i];
        } else if (kParts[i] !== eParts[i]) {
          matched = false; break;
        }
      }
      if (matched) return handler(params, body);
    }

    return LocalMock._default(method, cleanPath);
  }

  // ── Wrapper Principal ─────────────────────────────────────────
  /**
   * Executa um request à API com fallback automático para LocalMockEngine.
   * Interface 100% compatível com KubicaApp.api().
   *
   * @param {string} endpoint   - ex: '/ideas', '/dashboard/stats'
   * @param {string} method     - 'GET' | 'POST' | 'PUT' | 'DELETE'
   * @param {Object|FormData|null} body
   * @returns {Promise<Object>}  - { success, data, message? }
   */
  async function request(endpoint, method = 'GET', body = null) {

    // Tentar backend PHP
    if (!_isOffline) {
      try {
        const result = await KubicaApp.api(endpoint, method, body);
        return result;
      } catch (err) {
        // TypeError = rede/servidor inacessível
        const isNetworkError = err instanceof TypeError ||
          (err.name && err.name === 'TypeError') ||
          (typeof err === 'object' && err.status === 0);

        if (isNetworkError) {
          _isOffline = true;
          _initSeeds();
          _showOfflineBanner();
          console.warn('[KubicaAPI] Backend PHP inacessível. Ativando modo Local.');
        } else {
          // Erro de aplicação (4xx, 5xx) — relançar sem fallback
          throw err;
        }
      }
    }

    // LocalMockEngine
    return new Promise(resolve => {
      setTimeout(() => {
        resolve(_mockDispatch(method, endpoint, body));
      }, 40 + Math.random() * 80); // Latência simulada (40-120ms)
    });
  }

  /**
   * Shorthand para GET.
   */
  async function get(endpoint) {
    return request(endpoint, 'GET');
  }

  /**
   * Shorthand para POST.
   */
  async function post(endpoint, body) {
    return request(endpoint, 'POST', body);
  }

  /**
   * Verifica se está em modo offline.
   */
  function isOffline() {
    return _isOffline;
  }

  /**
   * Força modo online (tenta reconectar).
   */
  function reconnect() {
    _isOffline = false;
    _bannerShown = false;
    const banner = document.getElementById('k-offline-banner');
    if (banner) banner.remove();
  }

  return { request, get, post, isOffline, reconnect };

})();
