class CreateBracketMatches < ActiveRecord::Migration[8.0]
  def change
    create_table :bracket_matches do |t|
      t.references :bracket, null: false, foreign_key: true
      t.references :team1, null: true, foreign_key: { to_table: :teams }
      t.references :team2, null: true, foreign_key: { to_table: :teams }
      t.references :winner, null: true, foreign_key: { to_table: :teams }
      t.integer :round, null: false
      t.integer :position, null: false
      t.datetime :match_date
      t.text :notes

      t.timestamps
    end

    add_index :bracket_matches, [:bracket_id, :round, :position], unique: true
    add_index :bracket_matches, :round
  end
end
