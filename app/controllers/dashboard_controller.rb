class DashboardController < ApplicationController
  before_action :require_authentication

  def index
    @brackets = current_user ? Bracket.all : []
    @user_brackets = current_user ? current_user.plays.includes(:bracket) : []
  end

  def brackets
    @brackets = Bracket.all
  end

  def new_bracket
    # React app will handle the bracket creation
  end

  private

  def require_authentication
    unless current_user
      redirect_to login_path, alert: 'Please log in to access the dashboard'
    end
  end
end
