ALTER TABLE wp_bracket_builder_plays ADD COLUMN is_winner TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE wp_bracket_builder_plays ADD COLUMN bmb_official TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE wp_bracket_builder_plays ADD COLUMN is_tournament_entry TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE wp_bracket_builder_plays ADD COLUMN is_paid TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABlE wp_bracket_builder_bracket_results ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE wp_bracket_builder_brackets
        ADD COLUMN is_voting TINYINT(1) DEFAULT 0,
        ADD COLUMN live_round_index TINYINT(2) DEFAULT 0;

ALTER TABlE wp_bracket_builder_bracket_results ADD COLUMN popularity DECIMAL(6, 5) DEFAULT NULL;

ALTER TABLE wp_bracket_builder_notifications RENAME TO wp_bracket_builder_notification_subscriptions;

ALTER TABLE wp_bracket_builder_brackets ADD COLUMN is_template TINYINT(1) DEFAULT 0;
