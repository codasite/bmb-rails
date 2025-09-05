class Api::V1::TeamsController < Api::V1::BaseController
  before_action :set_team, only: [:show, :update, :destroy]

  def index
    @teams = Team.all
    @teams = @teams.seeded if params[:seeded] == 'true'
    @teams = @teams.unseeded if params[:seeded] == 'false'

    render json: {
      teams: @teams.map { |team| team_json(team) }
    }
  end

  def show
    render json: { team: team_json(@team) }
  end

  def create
    @team = Team.new(team_params)

    if @team.save
      render json: { team: team_json(@team) }, status: :created
    else
      render json: { error: @team.errors.full_messages }, status: :unprocessable_entity
    end
  end

  def update
    if @team.update(team_params)
      render json: { team: team_json(@team) }
    else
      render json: { error: @team.errors.full_messages }, status: :unprocessable_entity
    end
  end

  def destroy
    @team.destroy
    head :no_content
  end

  private

  def set_team
    @team = Team.find(params[:id])
  end

  def team_params
    params.require(:team).permit(:name, :seed, :logo_url, :color)
  end

  def team_json(team)
    {
      id: team.id,
      name: team.name,
      seed: team.seed,
      display_name: team.display_name,
      logo_url: team.logo_url,
      color: team.color,
      seeded: team.seeded?
    }
  end
end
