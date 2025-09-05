# This file is auto-generated from the current state of the database. Instead
# of editing this file, please use the migrations feature of Active Record to
# incrementally modify your database, and then regenerate this schema definition.
#
# This file is the source Rails uses to define your schema when running `bin/rails
# db:schema:load`. When creating a new database, `bin/rails db:schema:load` tends to
# be faster and is potentially less error prone than running all of your
# migrations from scratch. Old migrations may fail to apply correctly if those
# migrations use external dependencies or application code.
#
# It's strongly recommended that you check this file into your version control system.

ActiveRecord::Schema[8.0].define(version: 2025_09_04_225855) do
  # These are extensions that must be enabled in order to support this database
  enable_extension "pg_catalog.plpgsql"

  create_table "bracket_matches", force: :cascade do |t|
    t.bigint "bracket_id", null: false
    t.bigint "team1_id"
    t.bigint "team2_id"
    t.bigint "winner_id"
    t.integer "round", null: false
    t.integer "position", null: false
    t.datetime "match_date"
    t.text "notes"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["bracket_id", "round", "position"], name: "index_bracket_matches_on_bracket_id_and_round_and_position", unique: true
    t.index ["bracket_id"], name: "index_bracket_matches_on_bracket_id"
    t.index ["round"], name: "index_bracket_matches_on_round"
    t.index ["team1_id"], name: "index_bracket_matches_on_team1_id"
    t.index ["team2_id"], name: "index_bracket_matches_on_team2_id"
    t.index ["winner_id"], name: "index_bracket_matches_on_winner_id"
  end

  create_table "brackets", force: :cascade do |t|
    t.string "title", null: false
    t.string "slug", null: false
    t.string "month"
    t.string "year"
    t.integer "num_teams", null: false
    t.integer "wildcard_placement"
    t.datetime "results_first_updated_at"
    t.integer "num_plays", default: 0
    t.decimal "fee", precision: 8, scale: 2
    t.boolean "should_notify_results_updated", default: false
    t.boolean "is_voting", default: false
    t.integer "live_round_index", default: 0
    t.text "round_names"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["is_voting"], name: "index_brackets_on_is_voting"
    t.index ["month"], name: "index_brackets_on_month"
    t.index ["slug"], name: "index_brackets_on_slug", unique: true
    t.index ["year"], name: "index_brackets_on_year"
  end

  create_table "picks", force: :cascade do |t|
    t.bigint "bracket_id", null: false
    t.bigint "user_id"
    t.bigint "team_id", null: false
    t.bigint "bracket_match_id", null: false
    t.integer "round", null: false
    t.integer "position", null: false
    t.boolean "is_result", default: false
    t.integer "confidence_level"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["bracket_id", "is_result"], name: "index_picks_on_bracket_id_and_is_result"
    t.index ["bracket_id", "user_id", "round", "position"], name: "index_picks_on_bracket_id_and_user_id_and_round_and_position", unique: true
    t.index ["bracket_id"], name: "index_picks_on_bracket_id"
    t.index ["bracket_match_id"], name: "index_picks_on_bracket_match_id"
    t.index ["is_result"], name: "index_picks_on_is_result"
    t.index ["team_id"], name: "index_picks_on_team_id"
    t.index ["user_id"], name: "index_picks_on_user_id"
  end

  create_table "plays", force: :cascade do |t|
    t.bigint "bracket_id", null: false
    t.bigint "user_id", null: false
    t.integer "score"
    t.datetime "completed_at"
    t.text "picks_data"
    t.boolean "is_paid", default: false
    t.string "payment_intent_id"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["bracket_id", "user_id"], name: "index_plays_on_bracket_id_and_user_id", unique: true
    t.index ["bracket_id"], name: "index_plays_on_bracket_id"
    t.index ["completed_at"], name: "index_plays_on_completed_at"
    t.index ["is_paid"], name: "index_plays_on_is_paid"
    t.index ["score"], name: "index_plays_on_score"
    t.index ["user_id"], name: "index_plays_on_user_id"
  end

  create_table "teams", force: :cascade do |t|
    t.string "name", null: false
    t.integer "seed"
    t.string "logo_url"
    t.string "color"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["name"], name: "index_teams_on_name"
    t.index ["seed"], name: "index_teams_on_seed"
  end

  create_table "users", force: :cascade do |t|
    t.string "email"
    t.string "first_name"
    t.string "last_name"
    t.string "display_name"
    t.string "password_digest"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.string "username", null: false
    t.boolean "active", default: true
    t.datetime "last_login_at"
    t.index ["active"], name: "index_users_on_active"
    t.index ["username"], name: "index_users_on_username", unique: true
  end

  add_foreign_key "bracket_matches", "brackets"
  add_foreign_key "bracket_matches", "teams", column: "team1_id"
  add_foreign_key "bracket_matches", "teams", column: "team2_id"
  add_foreign_key "bracket_matches", "teams", column: "winner_id"
  add_foreign_key "picks", "bracket_matches"
  add_foreign_key "picks", "brackets"
  add_foreign_key "picks", "teams"
  add_foreign_key "picks", "users"
  add_foreign_key "plays", "brackets"
  add_foreign_key "plays", "users"
end
