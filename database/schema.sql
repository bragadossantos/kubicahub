-- ============================================================
-- KUBICA HUB — Schema MySQL v2.0 (Completo + Actualizado)
-- Adiciona: mensagens, rate_limit, audit_logs, ficheiros
-- Actualiza: users (login_attempts, locked_until, last_login_at)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── TABELA: users ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `role`                ENUM('inventor','builder','admin','mentor') NOT NULL,
  `name`                VARCHAR(120) NOT NULL,
  `email`               VARCHAR(255) NOT NULL UNIQUE,
  `university`          ENUM('UAN','UCAN','ISAF','UGS') NOT NULL,
  `faculty`             ENUM('Engenharia','Gestão','Direito','Marketing') NOT NULL,
  `password_hash`       VARCHAR(255) NOT NULL,
  `profile_photo`       VARCHAR(500) NULL,
  `bio`                 TEXT NULL,
  `availability_hours`  TINYINT UNSIGNED NULL COMMENT 'Horas por semana disponíveis',
  `skills`              JSON NULL,
  `profile_type`        VARCHAR(50) NULL COMMENT 'CTO, CFO, CLO, CMO, etc.',
  `is_verified`         TINYINT(1) NOT NULL DEFAULT 0,
  `is_active`           TINYINT(1) NOT NULL DEFAULT 1,
  `otp_code`            CHAR(6) NULL,
  `otp_expires_at`      DATETIME NULL,
  `login_attempts`      TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `locked_until`        DATETIME NULL,
  `last_login_at`       DATETIME NULL,
  `password_reset_token` VARCHAR(100) NULL,
  `password_reset_expires` DATETIME NULL,
  `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_role`        (`role`),
  INDEX `idx_university`  (`university`),
  INDEX `idx_faculty`     (`faculty`),
  INDEX `idx_is_active`   (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TABELA: ideas ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `ideas` (
  `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `inventor_id`         INT UNSIGNED NOT NULL,
  `title`               VARCHAR(200) NOT NULL,
  `sector`              VARCHAR(50) NOT NULL,
  `pdn_axis`            VARCHAR(100) NOT NULL,
  `problem`             TEXT NOT NULL,
  `solution`            TEXT NOT NULL,
  `target_market`       TEXT NULL,
  `roles_needed`        JSON NULL,
  `status`              ENUM('draft','pending','approved','rejected','matched','incubating','graduated') NOT NULL DEFAULT 'pending',
  `screening_feedback`  TEXT NULL,
  `views_count`         INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ideas_inventor` FOREIGN KEY (`inventor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_status`    (`status`),
  INDEX `idx_sector`    (`sector`),
  INDEX `idx_inventor`  (`inventor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TABELA: teams ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `teams` (
  `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `idea_id`             INT UNSIGNED NOT NULL,
  `name`                VARCHAR(200) NOT NULL,
  `fkcu_phase`          ENUM('captacao','matchmaking','cocriacao','graduacao') NOT NULL DEFAULT 'cocriacao',
  `mou_signed`          TINYINT(1) NOT NULL DEFAULT 0,
  `mou_signed_at`       DATETIME NULL,
  `sandbox_balance_usd` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `demo_day_registered` TINYINT(1) NOT NULL DEFAULT 0,
  `inapem_seal`         TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_teams_idea` FOREIGN KEY (`idea_id`) REFERENCES `ideas`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_idea_team` (`idea_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TABELA: team_members ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `team_members` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `team_id`         INT UNSIGNED NOT NULL,
  `user_id`         INT UNSIGNED NOT NULL,
  `role`            ENUM('CTO','CFO','CLO','CMO','CEO') NOT NULL,
  `equity_pct`      DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `vesting_months`  TINYINT UNSIGNED NOT NULL DEFAULT 36,
  `cliff_months`    TINYINT UNSIGNED NOT NULL DEFAULT 12,
  `joined_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_tm_team` FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tm_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_team_user` (`team_id`, `user_id`),
  UNIQUE KEY `uq_team_role` (`team_id`, `role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TABELA: matches ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `matches` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `idea_id`         INT UNSIGNED NOT NULL,
  `builder_id`      INT UNSIGNED NOT NULL,
  `proposed_role`   ENUM('CTO','CFO','CLO','CMO') NOT NULL,
  `equity_pct`      DECIMAL(5,2) NOT NULL,
  `hours_per_week`  TINYINT UNSIGNED NOT NULL,
  `vesting_months`  TINYINT UNSIGNED NOT NULL DEFAULT 36,
  `cliff_months`    TINYINT UNSIGNED NOT NULL DEFAULT 12,
  `message`         TEXT NULL,
  `status`          ENUM('pending','accepted','declined','expired') NOT NULL DEFAULT 'pending',
  `decline_reason`  TEXT NULL,
  `expires_at`      DATETIME NOT NULL,
  `responded_at`    DATETIME NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_m_idea`    FOREIGN KEY (`idea_id`)    REFERENCES `ideas`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_m_builder` FOREIGN KEY (`builder_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_builder` (`builder_id`),
  INDEX `idx_idea`    (`idea_id`),
  INDEX `idx_status`  (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TABELA: sandbox_transactions ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `sandbox_transactions` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `team_id`       INT UNSIGNED NOT NULL,
  `amount_usd`    DECIMAL(10,2) NOT NULL,
  `category`      VARCHAR(50) NOT NULL,
  `description`   TEXT NOT NULL,
  `tranche`       TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `status`        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `requested_by`  INT UNSIGNED NOT NULL,
  `approved_by`   INT UNSIGNED NULL,
  `rejected_by`   INT UNSIGNED NULL,
  `reject_reason` TEXT NULL,
  `requested_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_at`   DATETIME NULL,
  CONSTRAINT `fk_st_team`      FOREIGN KEY (`team_id`)      REFERENCES `teams`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_st_requester` FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`),
  CONSTRAINT `fk_st_approver`  FOREIGN KEY (`approved_by`)  REFERENCES `users`(`id`),
  INDEX `idx_team_status` (`team_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TABELA: deliverables ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `deliverables` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `team_id`       INT UNSIGNED NOT NULL,
  `role`          ENUM('CTO','CFO','CLO','CMO') NOT NULL,
  `title`         VARCHAR(250) NOT NULL,
  `description`   TEXT NULL,
  `file_url`      VARCHAR(500) NULL,
  `submitted_by`  INT UNSIGNED NOT NULL,
  `status`        ENUM('pending_review','approved','revision_required') NOT NULL DEFAULT 'pending_review',
  `reviewer_note` TEXT NULL,
  `submitted_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at`   DATETIME NULL,
  CONSTRAINT `fk_del_team` FOREIGN KEY (`team_id`)     REFERENCES `teams`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_del_user` FOREIGN KEY (`submitted_by`) REFERENCES `users`(`id`),
  INDEX `idx_team_role` (`team_id`, `role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TABELA: documents ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `documents` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `team_id`     INT UNSIGNED NOT NULL,
  `type`        ENUM('mou','cofounders_agreement','nda','vesting_schedule','iapi_request') NOT NULL,
  `title`       VARCHAR(250) NOT NULL,
  `content`     LONGTEXT NOT NULL,
  `version`     TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `status`      ENUM('draft','signed') NOT NULL DEFAULT 'draft',
  `created_by`  INT UNSIGNED NOT NULL,
  `signed_by`   INT UNSIGNED NULL,
  `signed_at`   DATETIME NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_doc_team` FOREIGN KEY (`team_id`)    REFERENCES `teams`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_doc_user` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TABELA: mediation_requests ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `mediation_requests` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `team_id`      INT UNSIGNED NOT NULL,
  `type`         ENUM('equity','deliverable','commitment','exit','other') NOT NULL,
  `description`  TEXT NOT NULL,
  `urgency`      ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `requested_by` INT UNSIGNED NOT NULL,
  `status`       ENUM('open','in_review','resolved','closed') NOT NULL DEFAULT 'open',
  `resolution`   TEXT NULL,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_at`  DATETIME NULL,
  CONSTRAINT `fk_med_team` FOREIGN KEY (`team_id`)      REFERENCES `teams`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_med_user` FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TABELA: mentoring_sessions ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `mentoring_sessions` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `team_id`      INT UNSIGNED NOT NULL,
  `mentor_id`    INT UNSIGNED NOT NULL,
  `datetime`     DATETIME NOT NULL,
  `duration_min` SMALLINT UNSIGNED NOT NULL DEFAULT 60,
  `format`       ENUM('online','presencial') NOT NULL DEFAULT 'online',
  `notes`        TEXT NULL,
  `status`       ENUM('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ms_team`   FOREIGN KEY (`team_id`)   REFERENCES `teams`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ms_mentor` FOREIGN KEY (`mentor_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TABELA: traction_metrics ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `traction_metrics` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `team_id`      INT UNSIGNED NOT NULL,
  `metric_name`  VARCHAR(100) NOT NULL,
  `value`        DECIMAL(14,2) NOT NULL,
  `unit`         VARCHAR(30) NULL,
  `recorded_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_tm2_team` FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE,
  INDEX `idx_team_metric` (`team_id`, `metric_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TABELA: notifications ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `notifications` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT UNSIGNED NOT NULL,
  `type`        ENUM('match','idea','sandbox','deadline','system','mensagem') NOT NULL,
  `title`       VARCHAR(250) NOT NULL,
  `message`     TEXT NOT NULL,
  `action_url`  VARCHAR(500) NULL,
  `is_read`     TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TABELA: badges ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `badges` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT UNSIGNED NOT NULL,
  `badge_type`  VARCHAR(50) NOT NULL,
  `awarded_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_badge_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_user_badge` (`user_id`, `badge_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TABELA: speed_dating_sessions ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `speed_dating_sessions` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `date`       DATE NOT NULL,
  `time`       TIME NOT NULL,
  `capacity`   TINYINT UNSIGNED NOT NULL DEFAULT 14,
  `status`     ENUM('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `notes`      TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ══════════════════════════════════════════════════════════════
-- NOVAS TABELAS v2.0
-- ══════════════════════════════════════════════════════════════

-- ── TABELA: mensagens (comunicação interna inventor ↔ builder) ─────────────
CREATE TABLE IF NOT EXISTS `mensagens` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `conversa_id`   VARCHAR(64) NOT NULL COMMENT 'Hash determinístico: ideaId_userId1_userId2',
  `remetente_id`  INT UNSIGNED NOT NULL,
  `destinatario_id` INT UNSIGNED NOT NULL,
  `idea_id`       INT UNSIGNED NOT NULL COMMENT 'Contexto: projecto em discussão',
  `conteudo`      TEXT NOT NULL,
  `lida`          TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_msg_remetente`     FOREIGN KEY (`remetente_id`)    REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_destinatario`  FOREIGN KEY (`destinatario_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_idea`          FOREIGN KEY (`idea_id`)         REFERENCES `ideas`(`id`) ON DELETE CASCADE,
  INDEX `idx_conversa`   (`conversa_id`),
  INDEX `idx_dest_lida`  (`destinatario_id`, `lida`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TABELA: rate_limit (protecção brute force) ─────────────────────────────
CREATE TABLE IF NOT EXISTS `rate_limit` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `chave`       VARCHAR(100) NOT NULL COMMENT 'IP ou email',
  `acao`        VARCHAR(50) NOT NULL COMMENT 'login, register, otp',
  `tentativas`  SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `bloqueado_ate` DATETIME NULL,
  `criado_em`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_chave_acao` (`chave`, `acao`),
  INDEX `idx_chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TABELA: audit_logs (rastreio de acções críticas) ──────────────────────
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT UNSIGNED NULL COMMENT 'NULL se acção de sistema',
  `acao`        VARCHAR(100) NOT NULL COMMENT 'Ex: user.login, idea.approve, sandbox.approve',
  `recurso`     VARCHAR(50) NULL COMMENT 'Ex: users, ideas, sandbox_transactions',
  `recurso_id`  INT UNSIGNED NULL,
  `dados_antes` JSON NULL COMMENT 'Estado antes da alteração',
  `dados_depois` JSON NULL COMMENT 'Estado após a alteração',
  `ip`          VARCHAR(45) NULL,
  `user_agent`  VARCHAR(500) NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_user_id`  (`user_id`),
  INDEX `idx_acao`     (`acao`),
  INDEX `idx_created`  (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TABELA: ficheiros (uploads de entregáveis e avatares) ──────────────────
CREATE TABLE IF NOT EXISTS `ficheiros` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`      INT UNSIGNED NOT NULL COMMENT 'Quem fez o upload',
  `tipo`         ENUM('avatar','entregavel','documento','outro') NOT NULL,
  `nome_original` VARCHAR(255) NOT NULL,
  `nome_ficheiro` VARCHAR(255) NOT NULL COMMENT 'Nome no servidor (UUID)',
  `caminho`      VARCHAR(500) NOT NULL,
  `mime_type`    VARCHAR(100) NOT NULL,
  `tamanho_bytes` INT UNSIGNED NOT NULL,
  `referencia_id` INT UNSIGNED NULL COMMENT 'ID do entregável/equipa associado',
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_fich_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_tipo` (`user_id`, `tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TABELA: vesting_schedules (governança 3 anos / 1 ano cliff) ───────────
CREATE TABLE IF NOT EXISTS `vesting_schedules` (
  `id`                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `team_id`            INT UNSIGNED NOT NULL,
  `user_id`            INT UNSIGNED NOT NULL,
  `role`               ENUM('CTO','CFO','CLO','CMO','CEO','CPO','COO','CAO','CDO') NOT NULL,
  `total_equity_pct`   DECIMAL(5,2) NOT NULL,
  `vested_equity_pct`  DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `vesting_start_date` DATE NOT NULL,
  `vesting_months`     TINYINT UNSIGNED NOT NULL DEFAULT 36,
  `cliff_months`       TINYINT UNSIGNED NOT NULL DEFAULT 12,
  `cliff_date`         DATE NOT NULL,
  `ip_split_pct`       DECIMAL(5,2) NOT NULL DEFAULT 100.00 COMMENT '100% estudante / 90% se lab univ.',
  `is_active`          TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_vs_team` FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vs_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_team_user_vesting` (`team_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

