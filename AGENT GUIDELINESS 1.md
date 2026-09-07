# Agent Instructions — Kubica Hub OS

> This file is mirrored across AGENTS.md, GEMINI.md, and CLAUDE.md to standardize execution across any AI engine inside Google Antigravity.

You operate within a 3-layer architecture specifically tuned for developing the **Kubica Hub** web platform and enforcing the **Framework KUBICA de Co-Criação Universitária (FKCU)** across Angolan universities (UAN, UCAN, ISAF, UGS).

## The 3-Layer Architecture for Kubica Hub

**Layer 1: Directive (What to do)**
- Located in `directives/` as structured Markdown SOPs.
- Defines UI screen requirements, FKCU matchmaking rules, IAPI regulatory steps, and Sandbox funding milestones.
- Human intent written in clear, unambiguous terms.

**Layer 2: Orchestration (Decision Making — That is YOU)**
- Your role is intelligent routing, architecture enforcement, and state coordination.
- When tasked with building a screen or feature, locate the directive in `directives/`, invoke the appropriate skill from `skills/`, call deterministic scripts in `execution/`, and handle edge cases.
- NEVER write unstructured code or invent random styles. Always adhere to the Kubica Design System (Espresso `#140b00`, Amber Forge `#ffb442`, PP Neue Montreal).

**Layer 3: Execution (Doing the work)**
- Deterministic Python/Node scripts in `execution/`.
- Handle code scaffolding, schema generation, KPI mathematical validation ($T_{MD}$, $V_{MM}$), file conversions, and database seeding.
- Keep complexity in deterministic code, leaving orchestration to the model.

---

## Operating Principles & Self-Annealing

1. **Check for Skills & Directives First:**
   Before generating code or UI, check `skills/` and `directives/`. Consult `kubica-brand-guidelines` and `kubica-frontend-design` for all interface work.
2. **Self-Annealing Loop (Self-Healing):**
   - When a TypeScript/React/Python build or test breaks, read the stack trace.
   - Fix the script in `execution/` or component in `src/`.
   - Re-test until green.
   - Update the respective directive in `directives/` with the edge case discovered.
3. **FKCU Governance Adherence:**
   Every project in the platform MUST map to the 4 faculties (Engineering, Business, Law, Marketing)[cite: 4]. Any matchmaking logic must enforce the 3-year vesting / 1-year cliff standard and non-dilutive Sandbox funding gates[cite: 4].

---

## Directory Conventions

- `.tmp/`: All temporary build artifacts, scraped test data, and intermediate exports. (Never commit, always regenerated).
- `execution/`: Deterministic tools and automation scripts.
- `directives/`: Living SOPs defining all platform flows.
- `skills/`: Local specialized knowledge bases (Design, Governance, Screen Specs).
- `src/`: Platform web application source code.

## Command Trigger
When the user types `@agents instantiate`, verify the directory tree, create missing folders (`.tmp/`, `execution/`, `directives/`, `skills/`), and report ready status.