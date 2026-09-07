# SOP: build_mvp_screen — Diretiva de Construção de Telas MVP

> **Versão:** 1.0.0  
> **Camada FKCU:** Layer 1 (Directives)  
> **Objetivo:** Estabelecer o procedimento operacional padrão para criação, estilização, validação de governança e amarração backend das telas do Kubica Hub.

---

## 1. Pré-Requisitos e Skills
Antes de iniciar qualquer tela, a orquestração deve obrigatoriamente carregar:
1. `kubica-brand-guidelines` (Cores, Tipografia PP Neue Montreal, Geometria Pill vs Card 6px).
2. `kubica-frontend-design` (Layout 1200px, Anti-default, Micro-interações, Acessibilidade).
3. `kubica-fkcu-orchestrator` (Regras da Matriz MSM, Ciclo de 4 Fases, USIT, Vesting, Sandbox).

---

## 2. Estrutura Padrão da Tela (Checklist Obrigatório)

### A. Anatomia Visual e Design System
- **Background Principal:** Espresso (`#140b00`) com sub-superfícies Midnight Cocoa (`#0b0600`).
- **Contêiner Central:** Largura máxima de 1200px centralizada (`.container`) e espaçamento vertical padronizado (52px).
- **Tipografia:** PP Neue Montreal / Inter com pesos estritos `400` (Regular) e `500` (Medium). Títulos display com tracking negativo (`-0.30px` a `-1.13px`).
- **Geometria de Controles:**
  - Botões, inputs e selects em **Full Pill** (`border-radius: 9999px`).
  - Cards, contêineres e caixas de texto estruturais em **Soft Corner** (`border-radius: 6px`).
- **Bordas e Linhas:** Hairline borders de 1px Walnut (`#43392d`) ou Cedar (`#4f4538`). Proibido o uso de sombras difusas (blur shadows).
- **Acento Cromático:** Amber Forge (`#ffb442`) exclusivo para CTAs primários, estados ativos e anéis de foco.

### B. Psicologia e UX Cognitiva
- **Hick's Law:** Reduzir atrito decisório nas telas de entrada (máx. 2 escolhas principais).
- **Endowed Progress Effect:** Em formulários multi-passo (wizards), inicializar com progresso artificial (ex.: 20%) para elevar a taxa de conclusão.
- **Micro-Copywriting Ativo:** Verbos de ação no infinitivo e perspectiva do estudante angolano ("Submeter Ideia", "Verificar OTP", "Recrutar Co-Founder").

### C. Integração Backend e Governança
- **Rotas e Controladores:** Mapeamento explícito para endpoints RESTful PHP (`/api/v1/auth/*`, `/api/v1/ideas/*`, etc.).
- **Validação de Entrada:** Proteção contra CSRF, XSS (sanitização de inputs) e rate limiting por IP.
- **Conformidade FKCU:** Validação de vínculo com universidades parceiras (UAN, UCAN, ISAF, UGS) e alocação na Matriz de 4 Faculdades (Engenharia, Gestão, Direito, Marketing).

---

## 3. Fluxo de Execução por Tela
1. **Definição da Diretiva da Tela:** Especificar ID (ex.: ONB-01), título, objetivo e mental model do utilizador.
2. **Scaffolding HTML/CSS:** Estruturar marcação semântica com classes do `kubica.css`.
3. **Comportamento Interativo JS:** Implementar transições, estados e toasts reativos via `kubica.js`.
4. **Vinculação de Dados:** Integrar com o backend (PHP MVC / endpoints JSON).
5. **Validação e Testes:** Testar responsividade móvel, estados de erro e conformidade com as regras FKCU.
