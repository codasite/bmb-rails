class Api::V1::PlaysController < Api::V1::BaseController
  before_action :set_bracket
  before_action :set_play, only: [:show, :update, :destroy]

  def index
    @plays = @bracket.plays.includes(:user)
    render json: {
      plays: @plays.map { |play| play_json(play) }
    }
  end

  def show
    render json: { play: detailed_play_json(@play) }
  end

  def create
    @play = @bracket.plays.build(play_params)

    if @play.save
      render json: { play: detailed_play_json(@play) }, status: :created
    else
      render json: { error: @play.errors.full_messages }, status: :unprocessable_entity
    end
  end

  def update
    if @play.update(play_params)
      # Recalculate score if picks were updated
      @play.update(score: @play.calculate_score) if play_params[:picks_data].present?
      render json: { play: detailed_play_json(@play) }
    else
      render json: { error: @play.errors.full_messages }, status: :unprocessable_entity
    end
  end

  def destroy
    @play.destroy
    head :no_content
  end

  private

  def set_bracket
    @bracket = Bracket.find_by!(slug: params[:bracket_id])
  end

  def set_play
    @play = @bracket.plays.find(params[:id])
  end

  def play_params
    params.require(:play).permit(:user_id, :score, :completed_at, :is_paid, :picks_data)
  end

  def play_json(play)
    {
      id: play.id,
      user: play.user ? user_json(play.user) : nil,
      score: play.score,
      completed_at: play.completed_at,
      is_paid: play.is_paid,
      created_at: play.created_at,
      updated_at: play.updated_at
    }
  end

  def detailed_play_json(play)
    play_json(play).merge(
      picks_data: play.picks_data,
      picks: play.bracket.picks.where(user: play.user).includes(:team).map { |pick| pick_json(pick) }
    )
  end

  def user_json(user)
    {
      id: user.id,
      username: user.username,
      display_name: user.display_name
    }
  end

  def pick_json(pick)
    {
      id: pick.id,
      round: pick.round,
      position: pick.position,
      team: {
        id: pick.team.id,
        name: pick.team.name,
        seed: pick.team.seed
      }
    }
  end
end
