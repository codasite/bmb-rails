class ApplicationController < ActionController::Base
  # Only add CSRF protection to HTML requests, skip for API requests
  protect_from_forgery with: :exception, unless: :json_request?

  helper_method :current_user, :logged_in?

  private

  def json_request?
    request.format.json?
  end

  def current_user
    @current_user ||= User.find_by(id: session[:user_id]) if session[:user_id]
  end

  def logged_in?
    !!current_user
  end

  def require_authentication
    unless current_user
      redirect_to login_path, alert: 'Please log in to access this page'
    end
  end
end
