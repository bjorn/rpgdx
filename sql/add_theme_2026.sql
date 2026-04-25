-- Add the "2026" theme to the rpgdx_themes table.
--
-- Run once per database (local dev and live).
--
--   docker compose exec -T db mariadb -u root -prpgdx_root indierp_main < sql/add_theme_2026.sql
--

INSERT INTO rpgdx_themes (theme_name, theme_dir)
SELECT '2026', '2026'
WHERE NOT EXISTS (
  SELECT 1 FROM rpgdx_themes WHERE theme_dir = '2026'
);
