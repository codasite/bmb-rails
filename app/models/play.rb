class Play < ApplicationRecord
  belongs_to :bracket
  belongs_to :user

  validates :score, numericality: { greater_than_or_equal_to: 0 }, allow_nil: true

  serialize :picks_data, coder: JSON

  scope :by_user, ->(user) { where(user: user) }
  scope :by_bracket, ->(bracket) { where(bracket: bracket) }
  scope :completed, -> { where.not(completed_at: nil) }
  scope :active, -> { where(completed_at: nil) }

  def completed?
    completed_at.present?
  end

  def active?
    !completed?
  end

  def calculate_score
    # Logic to calculate score based on picks vs results
    # This would be implemented based on your scoring rules
    0
  end
end
