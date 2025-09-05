#!/bin/bash

# Bracket Maker Builder - Local Development Setup Script
# This script sets up the Rails application for local development and testing

set -e  # Exit on any error

echo "🚀 Setting up Bracket Maker Builder Rails Application"
echo "=================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Check if we're in the right directory
if [ ! -f "Gemfile" ] || [ ! -f "config/application.rb" ]; then
    print_error "This doesn't appear to be the Rails application directory."
    print_error "Please run this script from the bmb-rails root directory."
    exit 1
fi

print_info "Checking prerequisites..."

# Check for rbenv
if ! command -v rbenv &> /dev/null; then
    print_error "rbenv is not installed. Please install rbenv first:"
    echo "  brew install rbenv"
    exit 1
fi

# Check Ruby version
print_info "Setting up Ruby environment..."
eval "$(rbenv init -)"

REQUIRED_RUBY="3.3.0"
CURRENT_RUBY=$(ruby --version | cut -d' ' -f2)

if [[ "$CURRENT_RUBY" != "$REQUIRED_RUBY"* ]]; then
    print_warning "Current Ruby version: $CURRENT_RUBY"
    print_info "Installing Ruby $REQUIRED_RUBY..."
    rbenv install $REQUIRED_RUBY
    rbenv local $REQUIRED_RUBY
    eval "$(rbenv init -)"
fi

print_status "Ruby $REQUIRED_RUBY is active"

# Check for PostgreSQL
print_info "Checking PostgreSQL..."
if ! command -v psql &> /dev/null; then
    print_error "PostgreSQL is not installed. Please install it:"
    echo "  brew install postgresql"
    echo "  brew services start postgresql"
    exit 1
fi

# Start PostgreSQL if not running
if ! pgrep -x "postgres" > /dev/null; then
    print_info "Starting PostgreSQL..."
    brew services start postgresql || {
        print_error "Failed to start PostgreSQL"
        exit 1
    }
fi

print_status "PostgreSQL is running"

# Install/update bundler
print_info "Installing bundler..."
gem install bundler

# Clean and install Ruby dependencies
print_info "Installing Ruby dependencies..."
bundle clean --force
bundle install

print_status "Ruby dependencies installed"

# Database setup
print_info "Setting up database..."

# Create databases
if bin/rails db:create; then
    print_status "Databases created"
else
    print_info "Databases already exist"
fi

# Run migrations
print_info "Running database migrations..."
bin/rails db:migrate

# Seed with sample data
print_info "Seeding database with sample data..."
bin/rails db:seed

print_status "Database setup complete"

# Check for Node.js (for React integration)
print_info "Checking Node.js for React integration..."
if command -v npm &> /dev/null; then
    print_status "Node.js and npm are available"

    if [ -d "react-bracket-builder" ]; then
        print_info "Installing React dependencies..."
        cd react-bracket-builder
        npm install
        print_status "React dependencies installed"
        cd ..
    fi
else
    print_warning "Node.js not found. React integration will be limited."
    print_info "To install Node.js: brew install node"
fi

# Test the Rails server
print_info "Testing Rails server startup..."
if timeout 10s bin/rails server --help > /dev/null 2>&1; then
    print_status "Rails server is ready"
else
    print_error "Rails server test failed"
    exit 1
fi

echo ""
echo "🎉 Setup Complete!"
echo "==================="
print_status "Rails application is ready for local development"
echo ""
echo "📋 Quick Start Commands:"
echo ""
echo "  Start the server:"
echo "    bin/rails server"
echo ""
echo "  Visit the application:"
echo "    http://localhost:3000"
echo ""
echo "  Test the API:"
echo "    curl http://localhost:3000/api/v1/brackets.json"
echo ""
echo "📊 Sample Data Available:"
echo "  • 5 Users (admin@bracketmakerbuilder.com / password)"
echo "  • 32 Teams (March Madness style)"
echo "  • 3 Brackets (sports, voting, paid)"
echo "  • 31 Matches with relationships"
echo "  • 5 Plays with sample picks"
echo ""
echo "📚 Documentation:"
echo "  • API_DOCUMENTATION.md - Complete API reference"
echo "  • MIGRATION_GUIDE.md - WordPress migration details"
echo "  • SETUP.md - Production deployment guide"
echo "  • README.md - Local development guide"
echo ""
print_status "Happy coding! 🚀"
