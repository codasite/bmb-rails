class Bracket < ApplicationRecord
  validates :title, presence: true
  validates :slug, presence: true, uniqueness: true
  validates :num_teams, presence: true, numericality: { greater_than: 0 }

  before_validation :generate_slug, if: :title_changed?

  serialize :round_names, coder: JSON

  scope :by_year, ->(year) { where(year: year) if year.present? }
  scope :by_month, ->(month) { where(month: month) if month.present? }
  scope :voting, -> { where(is_voting: true) }
  scope :tournament, -> { where(is_voting: false) }

  has_many :bracket_matches, dependent: :destroy
  has_many :picks, dependent: :destroy
  has_many :plays, dependent: :destroy

  def to_param
    slug
  end

  def voting?
    is_voting
  end

  def tournament?
    !is_voting
  end

  def has_fee?
    fee.present? && fee > 0
  end

  def current_round
    live_round_index
  end

  def completed?
    # Logic to determine if bracket is completed
    # This would depend on your specific business rules
    false
  end

  private

  def generate_slug
    self.slug = title.parameterize if title.present?
  end
end
