class BracketMatch < ApplicationRecord
  belongs_to :bracket
  belongs_to :team1, class_name: 'Team', optional: true
  belongs_to :team2, class_name: 'Team', optional: true
  belongs_to :winner, class_name: 'Team', optional: true

  has_many :picks, dependent: :destroy

  validates :round, presence: true
  validates :position, presence: true

  scope :by_round, ->(round) { where(round: round) }
  scope :completed, -> { where.not(winner: nil) }
  scope :pending, -> { where(winner: nil) }

  def completed?
    winner.present?
  end

  def pending?
    !completed?
  end

  def teams
    [team1, team2].compact
  end

  def has_both_teams?
    team1.present? && team2.present?
  end
end
