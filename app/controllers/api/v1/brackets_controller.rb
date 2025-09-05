class Api::V1::BracketsController < Api::V1::BaseController
  before_action :set_bracket, only: [:show, :update, :destroy]

  def index
    @brackets = Bracket.all
    @brackets = @brackets.by_year(params[:year]) if params[:year].present?
    @brackets = @brackets.by_month(params[:month]) if params[:month].present?
    @brackets = @brackets.voting if params[:voting] == 'true'
    @brackets = @brackets.tournament if params[:voting] == 'false'

    render json: {
      brackets: @brackets.map { |bracket| bracket_json(bracket) },
      meta: {}
    }
  end

  def show
    render json: { bracket: detailed_bracket_json(@bracket) }
  end

  def create
    @bracket = Bracket.new(bracket_params)

    if @bracket.save
      render json: { bracket: detailed_bracket_json(@bracket) }, status: :created
    else
      render json: { error: @bracket.errors.full_messages }, status: :unprocessable_entity
    end
  end

  def update
    if @bracket.update(bracket_params)
      render json: { bracket: detailed_bracket_json(@bracket) }
    else
      render json: { error: @bracket.errors.full_messages }, status: :unprocessable_entity
    end
  end

  def destroy
    @bracket.destroy
    head :no_content
  end

  private

  def set_bracket
    @bracket = Bracket.find_by!(slug: params[:id])
  end

  def bracket_params
    params.require(:bracket).permit(
      :title, :month, :year, :num_teams, :wildcard_placement,
      :fee, :should_notify_results_updated, :is_voting,
      :live_round_index, round_names: []
    )
  end

  def bracket_json(bracket)
    {
      id: bracket.id,
      title: bracket.title,
      slug: bracket.slug,
      month: bracket.month,
      year: bracket.year,
      num_teams: bracket.num_teams,
      num_plays: bracket.num_plays,
      fee: bracket.fee,
      is_voting: bracket.is_voting,
      live_round_index: bracket.live_round_index,
      created_at: bracket.created_at,
      updated_at: bracket.updated_at
    }
  end

  def detailed_bracket_json(bracket)
    bracket_json(bracket).merge(
      round_names: bracket.round_names,
      matches: bracket.bracket_matches.includes(:team1, :team2, :winner).map { |match| match_json(match) },
      results: bracket.picks.results.includes(:team).map { |pick| pick_json(pick) }
    )
  end

  def match_json(match)
    {
      id: match.id,
      round: match.round,
      position: match.position,
      team1: match.team1 ? team_json(match.team1) : nil,
      team2: match.team2 ? team_json(match.team2) : nil,
      winner: match.winner ? team_json(match.winner) : nil,
      match_date: match.match_date,
      completed: match.completed?
    }
  end

  def pick_json(pick)
    {
      id: pick.id,
      round: pick.round,
      position: pick.position,
      team: team_json(pick.team),
      is_result: pick.is_result
    }
  end

  def team_json(team)
    {
      id: team.id,
      name: team.name,
      seed: team.seed,
      display_name: team.display_name
    }
  end
end
