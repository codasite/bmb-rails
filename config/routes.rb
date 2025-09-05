Rails.application.routes.draw do
  # Define your application routes per the DSL in https://guides.rubyonrails.org/routing.html

  # Reveal health status on /up that returns 200 if the app boots with no exceptions, otherwise 500.
  # Can be used by load balancers and uptime monitors to verify that the app is live.
  get "up" => "rails/health#show", as: :rails_health_check

  # Static pages
  root 'pages#home'
  get '/about', to: 'pages#about'
  get '/privacy-policy', to: 'pages#privacy_policy'

  # API routes
  namespace :api do
    namespace :v1 do
      resources :brackets do
        resources :plays
      end
      resources :teams
      resources :users, only: [:index, :show, :create, :update]
    end
  end
end
