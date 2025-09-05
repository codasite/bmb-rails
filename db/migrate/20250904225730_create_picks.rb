class CreatePicks < ActiveRecord::Migration[8.0]
  def change
    create_table :picks do |t|
      t.references :bracket, null: false, foreign_key: true
      t.references :user, null: true, foreign_key: true
      t.references :team, null: false, foreign_key: true
      t.references :bracket_match, null: false, foreign_key: true
      t.integer :round, null: false
      t.integer :position, null: false
      t.boolean :is_result, default: false
      t.integer :confidence_level

      t.timestamps
    end

    add_index :picks, [:bracket_id, :user_id, :round, :position], unique: true
    add_index :picks, :is_result
    add_index :picks, [:bracket_id, :is_result]
  end
end
