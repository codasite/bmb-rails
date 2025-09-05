class CreateTeams < ActiveRecord::Migration[8.0]
  def change
    create_table :teams do |t|
      t.string :name, null: false
      t.integer :seed
      t.string :logo_url
      t.string :color

      t.timestamps
    end

    add_index :teams, :name
    add_index :teams, :seed
  end
end
