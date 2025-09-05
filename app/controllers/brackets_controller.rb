class BracketsController < ApplicationController
  before_action :set_bracket, only: [:show, :edit, :update, :destroy]

  # GET /brackets
  def index
    @brackets = Bracket.all.order(created_at: :desc)
  end

  # GET /brackets/1
  def show
    @bracket_matches = @bracket.bracket_matches.includes(:team1, :team2, :winner).order(:round, :position)
  end

  # GET /brackets/new
  def new
    @bracket = Bracket.new
  end

  # GET /brackets/1/edit
  def edit
  end

  # POST /brackets
  def create
    @bracket = Bracket.new(bracket_params)
    
    if @bracket.save
      create_bracket_structure
      redirect_to @bracket, notice: 'Bracket was successfully created.'
    else
      render :new, status: :unprocessable_entity
    end
  end

  # PATCH/PUT /brackets/1
  def update
    if @bracket.update(bracket_params)
      redirect_to @bracket, notice: 'Bracket was successfully updated.'
    else
      render :edit, status: :unprocessable_entity
    end
  end

  # DELETE /brackets/1
  def destroy
    @bracket.destroy
    redirect_to brackets_url, notice: 'Bracket was successfully deleted.'
  end

  private

  def set_bracket
    @bracket = Bracket.find_by!(slug: params[:id])
  end

  def bracket_params
    params.require(:bracket).permit(:title, :num_teams, :month, :year, :fee, :is_voting)
  end

  def create_bracket_structure
    return unless @bracket.num_teams && @bracket.num_teams > 1

    # Create teams if team names were provided
    team_names = params[:bracket][:team_names]&.reject(&:blank?) || []
    teams = []
    
    @bracket.num_teams.times do |i|
      team_name = team_names[i].present? ? team_names[i] : "Team #{i + 1}"
      teams << Team.create!(name: team_name, seed: i + 1)
    end

    # Create bracket structure
    create_tournament_matches(teams)
  end

  def create_tournament_matches(teams)
    num_teams = teams.size
    return if num_teams < 2

    # Calculate number of rounds
    rounds = Math.log2(num_teams).ceil
    
    # Create first round matches
    round_matches = []
    (0...num_teams).step(2) do |i|
      if i + 1 < num_teams
        match = BracketMatch.create!(
          bracket: @bracket,
          round: 0,
          position: i / 2,
          team1: teams[i],
          team2: teams[i + 1]
        )
        round_matches << match
      end
    end

    # Create subsequent rounds
    (1...rounds).each do |round_num|
      next_round_matches = []
      (0...round_matches.size).step(2) do |i|
        if i + 1 < round_matches.size
          match = BracketMatch.create!(
            bracket: @bracket,
            round: round_num,
            position: i / 2
            # team1 and team2 will be determined by previous round winners
          )
          next_round_matches << match
        end
      end
      round_matches = next_round_matches
    end
  end
end