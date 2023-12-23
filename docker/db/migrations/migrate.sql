ALTER TABLE wp_bracket_builder_plays ADD COLUMN is_winner TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE wp_bracket_builder_plays ADD COLUMN bmb_official TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE wp_bracket_builder_plays ADD COLUMN is_entry TINYINT(1) NOT NULL DEFAULT 0;