class Pick < ApplicationRecord
  belongs_to :bracket
  belongs_to :user, optional: true
  belongs_to :team
  belongs_to :bracket_match

  validates :round, presence: true
  validates :position, presence: true

  scope :by_round, ->(round) { where(round: round) }
  scope :by_user, ->(user) { where(user: user) }
  scope :results, -> { where(is_result: true) }
  scope :predictions, -> { where(is_result: false) }

  def result?
    is_result
  end

  def prediction?
    !is_result
  end
end
