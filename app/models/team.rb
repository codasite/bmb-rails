class Team < ApplicationRecord
  validates :name, presence: true
  validates :seed, numericality: { greater_than: 0 }, allow_nil: true

  has_many :picks, dependent: :destroy

  scope :seeded, -> { where.not(seed: nil).order(:seed) }
  scope :unseeded, -> { where(seed: nil) }

  def seeded?
    seed.present?
  end

  def display_name
    seeded? ? "#{seed}. #{name}" : name
  end
end
