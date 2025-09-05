class CreatePlays < ActiveRecord::Migration[8.0]
  def change
    create_table :plays do |t|
      t.references :bracket, null: false, foreign_key: true
      t.references :user, null: false, foreign_key: true
      t.integer :score
      t.datetime :completed_at
      t.text :picks_data
      t.boolean :is_paid, default: false
      t.string :payment_intent_id

      t.timestamps
    end

    add_index :plays, [:bracket_id, :user_id], unique: true
    add_index :plays, :score
    add_index :plays, :completed_at
    add_index :plays, :is_paid
  end
end
