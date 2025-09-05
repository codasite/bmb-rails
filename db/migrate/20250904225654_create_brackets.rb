class CreateBrackets < ActiveRecord::Migration[8.0]
  def change
    create_table :brackets do |t|
      t.string :title, null: false
      t.string :slug, null: false
      t.string :month
      t.string :year
      t.integer :num_teams, null: false
      t.integer :wildcard_placement
      t.datetime :results_first_updated_at
      t.integer :num_plays, default: 0
      t.decimal :fee, precision: 8, scale: 2
      t.boolean :should_notify_results_updated, default: false
      t.boolean :is_voting, default: false
      t.integer :live_round_index, default: 0
      t.text :round_names

      t.timestamps
    end

    add_index :brackets, :slug, unique: true
    add_index :brackets, :year
    add_index :brackets, :month
    add_index :brackets, :is_voting
  end
end
