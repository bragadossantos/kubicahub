/* ============================================================
   KUBICA HUB — MoU Generator v1.0
   Geração e impressão real do Memorando de Entendimento FKCU.

   Conformidade:
   - Estrutura 4 Co-Founders com papéis C-Level FKCU
   - Vesting: 36 meses / 12 meses cliff (USIT default)
   - IAPI: Referência à Lei n.º 3/92 (Propriedade Industrial)
   - INAPEM: Conformidade com registo de startups
   - Sandbox Funding: Cláusula de Seed Capital
   ============================================================ */

const KubicaMoU = (() => {

  // ── Papéis FKCU completos ─────────────────────────────────────
  const FKCU_ROLES = {
    CTO:  { pillar: 'Engenharia & Tech',      resp: 'Arquitetura de software, MVP técnico, documentação técnica e infraestrutura.' },
    CEO:  { pillar: 'Gestão & Negócios',      resp: 'Visão estratégica, Business Canvas, operações e modelo de negócio.' },
    CFO:  { pillar: 'Finanças & Economia',    resp: 'Modelagem financeira, unit economics, captação de investimento.' },
    CLO:  { pillar: 'Direito & Conformidade', resp: 'MoU, co-founders agreement, vesting, registo IAPI/INAPEM (Lei n.º 3/92).' },
    CMO:  { pillar: 'Marketing & Línguas',    resp: 'Identidade visual, pitch deck, storytelling, gestão de redes sociais.' },
    COO:  { pillar: 'Operações',              resp: 'Processos internos, OKRs, gestão de projecto e logística.' },
    CDO:  { pillar: 'Dados & Analytics',      resp: 'Data strategy, KPIs, dashboards e inteligência artificial.' },
    CSRO: { pillar: 'Responsabilidade Social', resp: 'Impacto social, ESG, relações comunitárias e parcerias ONGs.' },
    CHRO: { pillar: 'Capital Humano',         resp: 'Recrutamento, cultura organizacional, formação e retenção de talento.' },
    CXO:  { pillar: 'Experiência do Utilizador', resp: 'Design UX/UI, pesquisa de utilizadores, testes de usabilidade.' },
    CPO:  { pillar: 'Produto',                resp: 'Product roadmap, backlog, user stories e métricas de produto.' },
    CSO:  { pillar: 'Vendas & Parcerias',     resp: 'Pipeline comercial, parcerias estratégicas e crescimento de receita.' },
  };

  // ── Valores padrão FKCU ───────────────────────────────────────
  const DEFAULTS = {
    vestingMonths: 36,
    cliffMonths: 12,
    sandboxSeed: 'USD 25,000',
    ipOwnership: '100% Estudantes (USIT — Universidade sem IP)',
    ipOwnershipLab: '90% Estudantes / 10% Laboratório (USIT — Universidade com IP)',
    governingLaw: 'Lei Angolana — Tribunal Provincial de Luanda',
    iapiRef: 'Lei n.º 3/92 de 28 de Fevereiro (Propriedade Industrial de Angola)',
    inapemRef: 'Decreto Executivo n.º 48/11 de 23 de Março (Registo INAPEM)',
  };

  // ── Formatar data ─────────────────────────────────────────────
  function _formatDate(date = new Date()) {
    return date.toLocaleDateString('pt-AO', { day: '2-digit', month: 'long', year: 'numeric' });
  }

  // ── Gerar documento HTML do MoU ───────────────────────────────
  /**
   * Gera o HTML completo do MoU para impressão.
   * @param {Object} data - dados do projecto e equipa
   */
  function _buildMoUHtml(data) {
    const {
      projectTitle   = 'Projecto sem título',
      ideaDescription = 'Descrição não fornecida.',
      founders       = [],
      usitType       = 'sem-ip', // 'sem-ip' | 'com-ip'
      vestingMonths  = DEFAULTS.vestingMonths,
      cliffMonths    = DEFAULTS.cliffMonths,
      sandboxSeed    = DEFAULTS.sandboxSeed,
      university     = 'Universidade a definir',
      createdAt      = new Date(),
    } = data;

    const ipOwnership = usitType === 'com-ip'
      ? DEFAULTS.ipOwnershipLab
      : DEFAULTS.ipOwnership;

    const founderRows = founders.map((f, i) => `
      <tr>
        <td>${i + 1}.</td>
        <td>${f.name || '________________________________'}</td>
        <td>${f.role || '—'}</td>
        <td>${f.email || '—'}</td>
        <td>${f.equity != null ? f.equity + '%' : '—'}</td>
      </tr>
    `).join('');

    const signatureRows = founders.map(f => `
      <div class="sig-block">
        <div class="sig-line"></div>
        <p class="sig-name">${f.name || '________________________________'}</p>
        <p class="sig-role">${f.role ? FKCU_ROLES[f.role]?.pillar || f.role : '—'}</p>
        <p class="sig-date">Data: ___/___/______</p>
      </div>
    `).join('');

    return `
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>MoU — ${projectTitle}</title>
  <link rel="stylesheet" href="../../assets/css/mou-print.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
  </style>
</head>
<body>
  <div class="mou-page">

    <!-- Cabeçalho Institucional -->
    <header class="mou-header">
      <div class="mou-logo-mark">K</div>
      <div class="mou-header-text">
        <h1 class="mou-title">Memorando de Entendimento</h1>
        <p class="mou-subtitle">Framework KUBICA de Co-Criação Universitária (FKCU)</p>
        <p class="mou-meta">Emitido por: <strong>Kubica Hub</strong> &nbsp;|&nbsp;
          Universidade: <strong>${university}</strong> &nbsp;|&nbsp;
          Data: <strong>${_formatDate(new Date(createdAt))}</strong>
        </p>
      </div>
    </header>

    <hr class="mou-divider">

    <!-- Secção 1: Identificação do Projecto -->
    <section class="mou-section">
      <h2 class="mou-section-title">1. Identificação do Projecto</h2>
      <table class="mou-table mou-table--definition">
        <tr><th>Nome do Projecto</th><td>${projectTitle}</td></tr>
        <tr><th>Descrição</th><td>${ideaDescription}</td></tr>
        <tr><th>Fase FKCU</th><td>Matchmaking → Incubação</td></tr>
        <tr><th>Data de Constituição da Equipa</th><td>${_formatDate(new Date(createdAt))}</td></tr>
      </table>
    </section>

    <!-- Secção 2: Matriz Multidisciplinar de Co-Fundadores -->
    <section class="mou-section">
      <h2 class="mou-section-title">2. Matriz Multidisciplinar de Co-Fundadores (MSM)</h2>
      <p class="mou-text">A equipa foi constituída conforme as diretrizes da <strong>Matriz de Sinergia Multidisciplinar</strong> do FKCU, garantindo cobertura de pelo menos 4 pilares fundamentais.</p>
      <table class="mou-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Nome Completo</th>
            <th>Papel (C-Level)</th>
            <th>Contacto Institucional</th>
            <th>Participação</th>
          </tr>
        </thead>
        <tbody>
          ${founderRows || '<tr><td colspan="5">— Sem co-fundadores registados —</td></tr>'}
        </tbody>
      </table>
    </section>

    <!-- Secção 3: Propriedade Intelectual e IAPI -->
    <section class="mou-section">
      <h2 class="mou-section-title">3. Propriedade Intelectual (IAPI)</h2>
      <p class="mou-text">
        Toda a propriedade intelectual gerada no âmbito deste projecto é regida pela
        <strong>${DEFAULTS.iapiRef}</strong>.
      </p>
      <table class="mou-table mou-table--definition">
        <tr><th>Titularidade do IP</th><td>${ipOwnership}</td></tr>
        <tr><th>Registo de Marca / Patente</th><td>A submeter ao IAPI no prazo de 90 dias após formalização.</td></tr>
        <tr><th>Código-Fonte</th><td>Licença proprietária da startup. Não pode ser reutilizado individualmente sem consentimento da equipa.</td></tr>
      </table>
    </section>

    <!-- Secção 4: Vesting e Participação Acionista -->
    <section class="mou-section">
      <h2 class="mou-section-title">4. Plano de Vesting e Estrutura Acionista</h2>
      <p class="mou-text">
        Os co-fundadores acordam o seguinte plano de vesting para as suas participações na startup:
      </p>
      <table class="mou-table mou-table--definition">
        <tr><th>Período Total de Vesting</th><td>${vestingMonths} meses</td></tr>
        <tr><th>Período de Cliff</th><td>${cliffMonths} meses (nenhuma ação é adquirida antes deste período)</td></tr>
        <tr><th>Vesting Pós-Cliff</th><td>Mensal proporcional até ao fim dos ${vestingMonths} meses</td></tr>
        <tr><th>Saída Prematura</th><td>Co-fundador perde ações não adquiridas. A equipa tem direito de recompra ao valor nominal.</td></tr>
        <tr><th>Saída por Justa Causa</th><td>100% das ações não adquiridas revertem para o pool de fundadores remanescentes.</td></tr>
      </table>
    </section>

    <!-- Secção 5: Sandbox de Financiamento -->
    <section class="mou-section">
      <h2 class="mou-section-title">5. Sandbox de Financiamento</h2>
      <p class="mou-text">
        A startup é elegível ao programa <strong>Sandbox Kubica</strong> de seed funding no valor de
        <strong>${sandboxSeed}</strong>, sujeito a avaliação de conformidade FKCU nas Fases 1 e 2.
      </p>
      <table class="mou-table mou-table--definition">
        <tr><th>Registo Obrigatório</th><td>INAPEM — ${DEFAULTS.inapemRef}</td></tr>
        <tr><th>Condição de Desembolso</th><td>Aprovação do Comité de Governança Kubica + entrega do Business Plan validado</td></tr>
        <tr><th>Utilização Restrita</th><td>Desenvolvimento de MVP, infraestrutura técnica, e registo de IP</td></tr>
      </table>
    </section>

    <!-- Secção 6: Cláusulas Gerais -->
    <section class="mou-section">
      <h2 class="mou-section-title">6. Cláusulas Gerais</h2>
      <ol class="mou-list">
        <li>O presente MoU é um documento de intenções e não constitui um contrato vinculativo definitivo. O Contrato de Co-Fundadores será assinado na Fase de Incubação.</li>
        <li>Qualquer co-fundador pode solicitar rescisão com aviso prévio de 30 dias, sujeito às regras de vesting desta cláusula 4.</li>
        <li>Conflitos entre co-fundadores serão mediados pelo Comité FKCU antes de recurso judicial.</li>
        <li>Este MoU é regido pela <strong>${DEFAULTS.governingLaw}</strong>.</li>
        <li>Alterações a este documento requerem unanimidade de todos os co-fundadores signatários.</li>
      </ol>
    </section>

    <!-- Secção 7: Assinaturas -->
    <section class="mou-section mou-section--signatures">
      <h2 class="mou-section-title">7. Assinaturas</h2>
      <p class="mou-text">Ao assinar, todos os co-fundadores declaram ter lido, compreendido e aceite os termos do presente Memorando.</p>
      <div class="mou-signatures">
        ${signatureRows || '<p class="mou-text">— Sem co-fundadores registados para assinar —</p>'}
      </div>
    </section>

    <!-- Rodapé -->
    <footer class="mou-footer">
      <p>Documento gerado automaticamente pelo <strong>Kubica Hub OS</strong> em ${_formatDate()}.</p>
      <p>Framework KUBICA de Co-Criação Universitária (FKCU) — Versão 2.0</p>
    </footer>

  </div>
</body>
</html>
    `.trim();
  }

  // ── API Pública ───────────────────────────────────────────────

  /**
   * Gera e imprime o MoU diretamente via janela de impressão do browser.
   * @param {Object} data - dados do projecto/equipa
   */
  function print(data) {
    const html = _buildMoUHtml(data);

    const iframe = document.createElement('iframe');
    iframe.id = 'k-mou-iframe';
    iframe.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:0;height:0;border:none;';
    document.body.appendChild(iframe);

    const doc = iframe.contentDocument || iframe.contentWindow.document;
    doc.open();
    doc.write(html);
    doc.close();

    // Aguardar assets carregarem antes de imprimir
    iframe.contentWindow.onload = () => {
      setTimeout(() => {
        iframe.contentWindow.print();
        // Remover iframe após impressão/cancelamento
        setTimeout(() => iframe.remove(), 2000);
      }, 300);
    };

    // Fallback se onload não disparar
    setTimeout(() => {
      if (document.getElementById('k-mou-iframe')) {
        iframe.contentWindow.print();
        setTimeout(() => iframe.remove(), 2000);
      }
    }, 1500);
  }

  /**
   * Gera o HTML do MoU e abre numa nova janela (alternativa ao iframe).
   * @param {Object} data
   */
  function openInWindow(data) {
    const html = _buildMoUHtml(data);
    const win = window.open('', '_blank', 'width=900,height=700,scrollbars=yes');
    if (!win) {
      console.error('[KubicaMoU] Pop-up bloqueado. Use print() em vez de openInWindow().');
      print(data);
      return;
    }
    win.document.open();
    win.document.write(html);
    win.document.close();
    win.focus();
    setTimeout(() => win.print(), 800);
  }

  /**
   * Extrai dados do projecto da página atual ou de uma store.
   * Usa KubicaAPI.get() para buscar dados reais (ou mock).
   * @param {string} ideaId
   * @returns {Promise<Object>}
   */
  async function fetchProjectData(ideaId) {
    try {
      const res = await KubicaAPI.get(`/ideas/${ideaId}`);
      if (res && res.success) {
        const idea = res.data;
        // Tentar buscar membros da equipa
        let founders = [];
        try {
          const teamRes = await KubicaAPI.get(`/teams?idea_id=${ideaId}`);
          if (teamRes && teamRes.success) {
            founders = (teamRes.data.items || []).map(m => ({
              name: m.name, role: m.role, email: m.email, equity: m.equity,
            }));
          }
        } catch (_) {}

        return {
          projectTitle: idea.title,
          ideaDescription: idea.description,
          university: idea.university,
          createdAt: idea.created_at,
          founders,
        };
      }
    } catch (err) {
      console.warn('[KubicaMoU] Não foi possível buscar dados do projecto:', err);
    }

    // Fallback: tentar ler do localStorage
    const user = KubicaApp.getUser();
    return {
      projectTitle: 'Projecto FKCU — ' + (user?.name || 'Utilizador'),
      ideaDescription: 'Descrição do projecto a ser preenchida.',
      university: user?.university || 'Universidade a definir',
      founders: user ? [{ name: user.name, role: user.c_level || 'CEO', email: user.email, equity: 25 }] : [],
    };
  }

  return {
    print,
    openInWindow,
    fetchProjectData,
    buildHtml: _buildMoUHtml,  // Expor para testes
    FKCU_ROLES,
    DEFAULTS,
  };

})();
