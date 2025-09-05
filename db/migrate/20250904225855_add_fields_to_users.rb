class AddFieldsToUsers < ActiveRecord::Migration[8.0]
  def change
    add_column :users, :username, :string, null: false
    add_column :users, :active, :boolean, default: true
    add_column :users, :last_login_at, :datetime
    add_index :users, :username, unique: true
    add_index :users, :active
  end
end
