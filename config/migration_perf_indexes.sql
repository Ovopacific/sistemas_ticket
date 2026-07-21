-- ============================================================
-- MIGRACIÓN DE RENDIMIENTO — Índices faltantes (PERF/DB-01)
-- Ejecutar una sola vez en la base de datos de producción.
-- Estos cambios solo AGREGAN índices, no modifican datos.
-- ============================================================

-- Índices en tabla comments (mejorar búsquedas por ticket y por usuario)
ALTER TABLE `comments`
    ADD INDEX IF NOT EXISTS `idx_comments_ticket`  (`ticket_id`),
    ADD INDEX IF NOT EXISTS `idx_comments_user`    (`user_id`),
    ADD INDEX IF NOT EXISTS `idx_comments_created` (`created_at`);

-- Índices en tabla audit_logs (mejorar búsquedas por usuario y acción)
ALTER TABLE `audit_logs`
    ADD INDEX IF NOT EXISTS `idx_audit_user`    (`user_id`),
    ADD INDEX IF NOT EXISTS `idx_audit_action`  (`action`),
    ADD INDEX IF NOT EXISTS `idx_audit_created` (`created_at`);

-- Columna ticket_id en audit_logs para evitar búsquedas LIKE en details (PERF-06)
-- Nota: Si ya existe la columna, ignorar el error.
ALTER TABLE `audit_logs`
    ADD COLUMN IF NOT EXISTS `ticket_id` INT DEFAULT NULL AFTER `details`,
    ADD INDEX IF NOT EXISTS `idx_audit_ticket` (`ticket_id`);
