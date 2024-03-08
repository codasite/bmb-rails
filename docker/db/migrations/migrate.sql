ALTER TABLE wp_bracket_builder_plays ADD COLUMN is_winner TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE wp_bracket_builder_plays ADD COLUMN bmb_official TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE wp_bracket_builder_plays ADD COLUMN is_tournament_entry TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE wp_bracket_builder_plays ADD COLUMN is_paid TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABle wp_bracket_builder_bracket_results ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;