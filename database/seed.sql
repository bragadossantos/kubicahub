-- ============================================================
-- KUBICA HUB — Dados de Demonstração (Seed)
-- Temporada 2026 — Luanda, Angola
-- ============================================================
-- Executar APÓS o schema.sql:
--   mysql -u root kubica_hub < seed.sql
-- ============================================================
-- NOTA: A hash abaixo foi gerada com PHP:
--   echo password_hash('Kubica@2026', PASSWORD_ARGON2ID);
-- Password de todos os utilizadores de teste: Kubica@2026
-- ============================================================

USE kubica_hub;

-- Hash Argon2ID real de 'Kubica@2026' (gerada em PHP 8.1):
SET @hash = '$argon2id$v=19$m=65536,t=4,p=1$c2FsdHNhbHRzYWx0c2FsdA$GfFXmFwfMaBCTJHn6LPkRQxrOEZyNlmjEHdUlyP9AaQ';

INSERT INTO `users`
  (role, name, email, university, faculty, password_hash, availability_hours, bio, skills, is_verified)
VALUES
-- Admin / Coordenação
('admin', 'Dr. Daniel Amaral', 'admin@kubicahub.ao', 'UAN', 'Gestão',
 @hash, NULL,
 'Coordenador do Programa FKCU — Universidade Agostinho Neto.', NULL, 1),

-- Inventors
('inventor', 'Rodrigo Augusto', 'rodrigo.augusto@eng.uan.ao', 'UAN', 'Engenharia',
 @hash, 20,
 'Estudante de Engenharia Informática, apaixonado por Agritech e IoT.',
 '["Python","JavaScript","IoT","AWS"]', 1),

('inventor', 'Kelvin Santos', 'kelvin.santos@gest.uan.ao', 'UAN', 'Gestão',
 @hash, 15,
 'Finalista de Gestão Empresarial, foco em Fintech e pagamentos digitais.',
 '["Business Development","Excel","SQL"]', 1),

('inventor', 'Ana Lima', 'ana.lima@mkt.isaf.ao', 'ISAF', 'Marketing',
 @hash, 20,
 'Estudante de Marketing com experiência em gestão de redes sociais e branding.',
 '["SEO","Social Media","Canva","Analytics"]', 1),

-- Builders
('builder', 'Maria Antónia Domingos', 'maria.domingos@dir.ucan.ao', 'UCAN', 'Direito',
 @hash, 20,
 'Estudante de Direito Societário. Especialização em IAPI e contratos startups.',
 '["Direito Societário","IAPI","Contratos","Vesting"]', 1),

('builder', 'Paulo Carvalho', 'paulo.carvalho@gest.ugs.ao', 'UGS', 'Gestão',
 @hash, 25,
 'CFO em formação. Projecções financeiras, modelos de negócio e fundraising.',
 '["Finanças","Excel","Modelagem Financeira","Pitch Deck"]', 1),

('builder', 'Luísa Santos', 'luisa.santos@mkt.isaf.ao', 'ISAF', 'Marketing',
 @hash, 15,
 'CMO em formação. Growth hacking e marketing digital para startups B2C.',
 '["Growth Hacking","Google Ads","Content Marketing","Branding"]', 1),

('builder', 'Tomás Ferreira', 'tomas.ferreira@eng.uan.ao', 'UAN', 'Engenharia',
 @hash, 30,
 'Engenheiro de Software. Full-stack Python/React. Experiência em APIs REST.',
 '["Python","React","Node.js","PostgreSQL","Docker"]', 1),

-- Mentores
('mentor', 'Prof. Pedro Lemos', 'p.lemos@eng.uan.ao', 'UAN', 'Engenharia',
 @hash, NULL,
 'Professor Associado de Sistemas de Informação. 15 anos de experiência em startups.', NULL, 1),

('mentor', 'Carlos Neves', 'c.neves@bodiva.ao', 'UCAN', 'Gestão',
 @hash, NULL,
 'Ex-Director da BODIVA. Especialista em Fintech e mercados de capitais Angola.', NULL, 1);

-- ── Ideias aprovadas ───────────────────────────────────────────────────────
INSERT INTO `ideas` (inventor_id, title, sector, pdn_axis, problem, solution, target_market, roles_needed, status)
VALUES
(2, 'AgroLink Angola',
 'Agritech',
 'Eixo 2 — Soberania Alimentar',
 'Os agricultores angolanos vendem os seus produtos a intermediários a preços 60% abaixo do mercado, enquanto os consumidores pagam preços premium. A cadeia de distribuição é ineficiente e opaca.',
 'Plataforma digital que liga directamente agricultores, cooperativas e compradores (supermercados, restaurantes, famílias) através de uma marketplace mobile-first com logística integrada.',
 'Agricultores e cooperativas das províncias de Malanje, Kwanza Norte e arredores de Luanda.',
 '["CLO","CFO","CMO"]',
 'approved'),

(3, 'PaguiFácil',
 'Fintech',
 'Eixo 3 — Transformação Digital',
 'Mais de 60% dos angolanos não têm acesso a serviços bancários formais. Os pagamentos informais são inseguros e não rastreáveis.',
 'Aplicação de pagamentos móveis que funciona com ou sem internet, integrada com Multicaixa Express e redes de agentes MPESA para atingir a população não bancarizada.',
 'População não bancarizada (18-45 anos) nas províncias de Luanda, Benguela e Huíla.',
 '["CTO","CLO","CMO"]',
 'approved'),

(4, 'TuriAngola',
 'Turismo',
 'Eixo 5 — Diversificação da Economia',
 'Angola tem um enorme potencial turístico (Tundavala, Kalandula, Namibe) mas carece de plataformas digitais que promovam experiências locais e facilitem a reserva directa.',
 'Plataforma de descoberta e reserva de experiências turísticas autênticas angolanas, com guias locais certificados e pagamento integrado.',
 'Turistas nacionais e da diáspora angolana (Portugal, África do Sul, Brasil).',
 '["CTO","CFO","CLO"]',
 'pending');

-- ── Equipa AgroLink ────────────────────────────────────────────────────────
INSERT INTO `teams` (idea_id, name, fkcu_phase, mou_signed, mou_signed_at) VALUES
(1, 'AgroLink Angola', 'cocriacao', 1, '2026-07-20 10:00:00');

-- Membros da equipa AgroLink
INSERT INTO `team_members` (team_id, user_id, role, equity_pct, vesting_months, cliff_months) VALUES
(1, 2, 'CTO', 30.00, 36, 12),  -- Rodrigo (inventor/CTO)
(1, 6, 'CFO', 25.00, 36, 12),  -- Paulo
(1, 5, 'CLO', 25.00, 36, 12),  -- Maria Antónia
(1, 7, 'CMO', 20.00, 36, 12);  -- Luísa

-- ── Match aprovado que gerou a equipa ─────────────────────────────────────
INSERT INTO `matches` (idea_id, builder_id, proposed_role, equity_pct, hours_per_week, vesting_months, cliff_months, status, responded_at, expires_at) VALUES
(1, 5, 'CLO', 25.00, 20, 36, 12, 'accepted', '2026-07-18 14:30:00', '2026-07-25 00:00:00'),
(1, 6, 'CFO', 25.00, 25, 36, 12, 'accepted', '2026-07-19 09:15:00', '2026-07-26 00:00:00'),
(1, 7, 'CMO', 20.00, 15, 36, 12, 'accepted', '2026-07-19 16:45:00', '2026-07-26 00:00:00');

-- ── Sandbox transactions (AgroLink) ───────────────────────────────────────
INSERT INTO `sandbox_transactions` (team_id, amount_usd, category, description, tranche, status, requested_by, approved_by, requested_at, approved_at) VALUES
(1, 1200.00, 'cloud', 'AWS EC2 t3.medium + RDS MySQL para MVP de produção — 6 meses', 1, 'pending', 2, NULL, '2026-08-27 10:00:00', NULL),
(1, 800.00, 'research', 'Entrevistas com 50 agricultores nas províncias de Malanje e Kwanza Norte', 1, 'approved', 2, 1, '2026-07-15 09:00:00', '2026-07-17 14:30:00'),
(1, 200.00, 'iapi', 'Taxa de registo de marca "AgroLink" no IAPI — Luanda', 1, 'approved', 5, 1, '2026-07-20 11:00:00', '2026-07-22 10:00:00');

-- ── Entregáveis (AgroLink) ────────────────────────────────────────────────
INSERT INTO `deliverables` (team_id, role, title, description, status, submitted_by, submitted_at) VALUES
(1, 'CTO', 'Arquitectura de Sistema AgroLink', 'Diagrama de arquitectura com AWS, API REST e aplicação móvel React Native.', 'approved', 2, '2026-07-10 15:00:00'),
(1, 'CTO', 'MVP Funcional — agrolink.vercel.app', 'Versão 0.1 funcional com marketplace básico e sistema de pagamento.', 'approved', 2, '2026-07-22 18:00:00'),
(1, 'CLO', 'Acordo de Co-Founders Assinado', 'Acordo assinado por todos os 4 membros da equipa — autenticado em cartório.', 'approved', 5, '2026-07-18 12:00:00'),
(1, 'CFO', 'Modelo Financeiro 3 Anos', 'Projecções de receitas, custos e break-even para o mercado angolano.', 'pending_review', 6, '2026-08-01 09:00:00');

-- ── Métricas de tração (AgroLink) ─────────────────────────────────────────
INSERT INTO `traction_metrics` (team_id, metric_name, value, unit, recorded_at) VALUES
(1, 'active_farmers',    150, 'agricultores', '2026-07-01 00:00:00'),
(1, 'active_farmers',    320, 'agricultores', '2026-08-01 00:00:00'),
(1, 'active_farmers',    500, 'agricultores', '2026-08-27 00:00:00'),
(1, 'monthly_orders',    45,  'encomendas',   '2026-07-01 00:00:00'),
(1, 'monthly_orders',    120, 'encomendas',   '2026-08-01 00:00:00'),
(1, 'gmv_usd',           3200,'USD',          '2026-07-01 00:00:00'),
(1, 'gmv_usd',           8900,'USD',          '2026-08-01 00:00:00'),
(1, 'nps_score',         72,  'pontos',       '2026-08-15 00:00:00');

-- ── Notificações seed ──────────────────────────────────────────────────────
INSERT INTO `notifications` (user_id, type, title, message, action_url, is_read) VALUES
(2, 'sandbox', 'Disbursement aprovado — USD 800', 'O teu pedido de disbursement para Pesquisa de Mercado foi aprovado.', '/api/sandbox/1', 1),
(2, 'idea',    'Ideia aprovada pela coordenação', 'A tua ideia AgroLink Angola foi aprovada e está no Banco de Ideias.', '/api/ideas/1', 1),
(5, 'match',   'Proposta societária aceite', 'Juntaste-te à equipa AgroLink Angola como CLO com 25% de equity.', '/api/proposals/1', 1);

-- ── Badges ────────────────────────────────────────────────────────────────
INSERT INTO `badges` (user_id, badge_type) VALUES
(2, 'inventor'),
(2, 'cto_fkcu'),
(2, 'mvp_entregue'),
(2, 'fundador'),
(5, 'builder'),
(5, 'clo_fkcu'),
(5, 'fundador'),
(5, 'verificado'),
(6, 'builder'),
(6, 'cfo_fkcu'),
(7, 'builder'),
(7, 'cmo_fkcu');

-- ── Speed Dating sessions ──────────────────────────────────────────────────
INSERT INTO `speed_dating_sessions` (date, time, capacity, status) VALUES
('2026-09-15', '18:00:00', 14, 'scheduled'),
('2026-10-10', '18:00:00', 14, 'scheduled');
