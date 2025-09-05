class User < ApplicationRecord
  has_secure_password

  validates :email, presence: true, uniqueness: true, format: { with: URI::MailTo::EMAIL_REGEXP }
  validates :username, presence: true, uniqueness: true

  has_many :picks, dependent: :destroy
  has_many :plays, dependent: :destroy
  has_many :notification_subscriptions, dependent: :destroy

  scope :active, -> { where(active: true) }

  def full_name
    "#{first_name} #{last_name}".strip
  end

  def display_name
    full_name.present? ? full_name : username
  end
end
