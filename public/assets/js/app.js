/* ============================================================
   KUBICA HUB — App Core v2.2
   Módulo de Autenticação, Route Guards e API Fetch Inteligente
   Compatível com qualquer servidor (PHP -S, Apache, Nginx, Subdiretórios)
   ============================================================ */

const KubicaApp = (() => {
  // ── Resolução Dinâmica de Base URL ─────────────────────────
  function getBasePath() {
    const path = window.location.pathname;
    if (path.includes('/kubica/public/')) return '/kubica/public';
    if (path.includes('/kubica/')) return '/kubica';
    if (path.includes('/public/')) return '/public';
    return '';
  }

  function getApiBase() {
    const base = getBasePath();
    return `${base}/api/v1`;
  }
  
  // Estado Local
  let currentUser = null;

  // ── API Fetch Wrapper ───────────────────────────────────────
  async function api(endpoint, method = 'GET', body = null) {
    const cleanEndpoint = endpoint.startsWith('/') ? endpoint : `/${endpoint}`;
    const url = `${getApiBase()}${cleanEndpoint}`;

    const headers = {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    };

    let fetchBody = null;
    if (body instanceof FormData) {
      fetchBody = body; // Deixa o navegador definir o boundary
    } else if (body !== null && typeof body === 'object') {
      headers['Content-Type'] = 'application/json';
      fetchBody = JSON.stringify(body);
    } else if (body) {
      fetchBody = body;
    }

    try {
      const response = await fetch(url, {
        method,
        headers,
        body: fetchBody,
        credentials: 'same-origin'
      });

      const data = await response.json().catch(() => null);

      if (!response.ok) {
        if (response.status === 401) {
          // Sessão expirada ou não autenticado
          localStorage.removeItem('kubica_user');
          currentUser = null;
          // Redireciona apenas se não estiver já na página de login ou registo
          if (!window.location.pathname.includes('/auth/')) {
            window.location.href = `${getBasePath()}/paginas/auth/login.html?session_expired=1`;
          }
        }
        throw { status: response.status, data: data || { message: `Erro ${response.status}` } };
      }

      return data;
    } catch (error) {
      console.error(`[Kubica API] Error on ${method} ${url}:`, error);
      throw error;
    }
  }

  // ── Gestão de Autenticação ──────────────────────────────────
  function carregarUser() {
    if (currentUser) return currentUser;
    const saved = localStorage.getItem('kubica_user');
    if (saved) {
      try {
        currentUser = JSON.parse(saved);
      } catch(e) {
        currentUser = null;
      }
    }
    return currentUser;
  }

  function guardarUser(user) {
    currentUser = user;
    localStorage.setItem('kubica_user', JSON.stringify(user));
  }

  async function login(email, password) {
    const res = await api('/auth/login', 'POST', { email, password });
    if (res && res.success && res.data && res.data.user) {
      guardarUser(res.data.user);
      return res.data.user;
    }
    return false;
  }

  async function logout() {
    try {
      await api('/auth/logout', 'GET');
    } catch (_) {}
    localStorage.removeItem('kubica_user');
    currentUser = null;
    window.location.href = `${getBasePath()}/paginas/landing.html`;
  }

  // ── Route Guards ────────────────────────────────────────────
  function verificarAcesso(roleRequerido = null) {
    const user = carregarUser();
    const base = getBasePath();
    
    // Se não estiver logado
    if (!user) {
      window.location.href = `${base}/paginas/auth/login.html`;
      return false;
    }

    // Se exigir um role específico
    if (roleRequerido && user.role !== roleRequerido && user.role !== 'admin') {
      if (user.role === 'admin') window.location.href = `${base}/paginas/admin/dashboard.html`;
      else if (user.role === 'inventor') window.location.href = `${base}/paginas/inventor/dashboard.html`;
      else if (user.role === 'builder') window.location.href = `${base}/paginas/builder/dashboard.html`;
      else window.location.href = `${base}/paginas/landing.html`;
      return false;
    }

    // Se estiver na tela de login mas já estiver autenticado
    if (!roleRequerido && window.location.pathname.includes('/auth/login.html')) {
      if (user.role === 'admin') window.location.href = `${base}/paginas/admin/dashboard.html`;
      else if (user.role === 'inventor') window.location.href = `${base}/paginas/inventor/dashboard.html`;
      else if (user.role === 'builder') window.location.href = `${base}/paginas/builder/dashboard.html`;
      return false;
    }

    return user;
  }

  // ── Helpers UI ──────────────────────────────────────────────
  function mostrarErro(mensagem, elementoId = 'form-errors') {
    const el = document.getElementById(elementoId);
    if (el) {
      el.innerHTML = `<div class="k-alert k-alert--error">${mensagem}</div>`;
      el.style.display = 'block';
    } else {
      alert(mensagem);
    }
  }

  function mostrarSucesso(mensagem, elementoId = 'form-errors') {
    const el = document.getElementById(elementoId);
    if (el) {
      el.innerHTML = `<div class="k-alert k-alert--success">${mensagem}</div>`;
      el.style.display = 'block';
    }
  }

  return {
    api,
    login,
    logout,
    verificarAcesso,
    getUser: carregarUser,
    setUser: guardarUser,
    getBasePath,
    getApiBase,
    mostrarErro,
    mostrarSucesso
  };
})();
