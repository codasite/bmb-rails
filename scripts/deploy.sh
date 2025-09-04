#!/bin/bash

# Deployment script for BMB Rails API
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
ENVIRONMENT=${1:-staging}
APP_NAME="bmb-rails"
DOCKER_COMPOSE_FILE="docker-compose.${ENVIRONMENT}.yml"

# Functions
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

check_requirements() {
    log_info "Checking requirements..."
    
    if ! command -v docker &> /dev/null; then
        log_error "Docker is not installed"
        exit 1
    fi
    
    if ! command -v docker-compose &> /dev/null; then
        log_error "Docker Compose is not installed"
        exit 1
    fi
    
    if [ ! -f "$DOCKER_COMPOSE_FILE" ]; then
        log_error "Docker Compose file $DOCKER_COMPOSE_FILE not found"
        exit 1
    fi
    
    if [ ! -f ".env.${ENVIRONMENT}" ]; then
        log_error "Environment file .env.${ENVIRONMENT} not found"
        exit 1
    fi
    
    log_info "Requirements check passed"
}

load_environment() {
    log_info "Loading environment variables..."
    export $(cat .env.${ENVIRONMENT} | grep -v '^#' | xargs)
    log_info "Environment variables loaded"
}

build_images() {
    log_info "Building Docker images..."
    docker-compose -f $DOCKER_COMPOSE_FILE build --no-cache
    log_info "Docker images built successfully"
}

run_migrations() {
    log_info "Running database migrations..."
    docker-compose -f $DOCKER_COMPOSE_FILE run --rm app bundle exec rails db:migrate
    log_info "Database migrations completed"
}

precompile_assets() {
    log_info "Precompiling assets..."
    docker-compose -f $DOCKER_COMPOSE_FILE run --rm app bundle exec rails assets:precompile
    log_info "Assets precompiled successfully"
}

start_services() {
    log_info "Starting services..."
    docker-compose -f $DOCKER_COMPOSE_FILE up -d
    log_info "Services started successfully"
}

wait_for_health() {
    log_info "Waiting for services to be healthy..."
    
    # Wait for database
    log_info "Waiting for database..."
    timeout 60 bash -c 'until docker-compose -f '$DOCKER_COMPOSE_FILE' exec postgres pg_isready -U $POSTGRES_USER -d $POSTGRES_DB; do sleep 2; done'
    
    # Wait for Redis
    log_info "Waiting for Redis..."
    timeout 60 bash -c 'until docker-compose -f '$DOCKER_COMPOSE_FILE' exec redis redis-cli ping; do sleep 2; done'
    
    # Wait for Rails app
    log_info "Waiting for Rails app..."
    timeout 120 bash -c 'until curl -f http://localhost:3000/health; do sleep 5; done'
    
    log_info "All services are healthy"
}

run_tests() {
    log_info "Running tests..."
    docker-compose -f $DOCKER_COMPOSE_FILE run --rm app bundle exec rspec
    log_info "Tests completed successfully"
}

cleanup() {
    log_info "Cleaning up..."
    docker-compose -f $DOCKER_COMPOSE_FILE down
    docker system prune -f
    log_info "Cleanup completed"
}

rollback() {
    log_warn "Rolling back deployment..."
    docker-compose -f $DOCKER_COMPOSE_FILE down
    log_info "Rollback completed"
}

# Main deployment function
deploy() {
    log_info "Starting deployment to $ENVIRONMENT environment..."
    
    check_requirements
    load_environment
    build_images
    run_migrations
    precompile_assets
    start_services
    wait_for_health
    
    log_info "Deployment completed successfully!"
    log_info "API is available at: https://api.backmybracket.com"
    log_info "Health check: https://api.backmybracket.com/health"
}

# Handle script arguments
case "${1:-deploy}" in
    deploy)
        deploy
        ;;
    test)
        check_requirements
        load_environment
        build_images
        run_tests
        cleanup
        ;;
    rollback)
        rollback
        ;;
    cleanup)
        cleanup
        ;;
    *)
        echo "Usage: $0 {deploy|test|rollback|cleanup} [environment]"
        echo "  deploy    - Deploy the application (default)"
        echo "  test      - Run tests"
        echo "  rollback  - Rollback the deployment"
        echo "  cleanup   - Clean up Docker resources"
        echo "  environment - staging|production (default: staging)"
        exit 1
        ;;
esac
