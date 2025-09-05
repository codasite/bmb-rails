# Bracket Maker Builder - Rails Application

**Complete WordPress to Ruby on Rails Migration** 🚀

A modern, scalable tournament bracket management system built with Ruby on Rails 8.0, migrated from WordPress/Oxygen templates. Create, manage, and share professional tournament brackets with a powerful REST API and responsive web interface.

## 🌟 Features

- **Tournament Brackets:** Create single/double elimination and round-robin tournaments
- **Voting Brackets:** Interactive voting for entertainment brackets
- **User Management:** Secure authentication and user profiles
- **Mobile Ready:** Responsive design + mobile app API support
- **Payment Integration:** Stripe integration for paid tournaments (planned)
- **Real-time Updates:** Live bracket updates and notifications (planned)
- **Professional Design:** Modern, mobile-first user interface

---

## 🚀 Quick Start (Local Testing)

### Prerequisites

- **Ruby 3.3.0** (use rbenv for version management)
- **PostgreSQL 12+**
- **Node.js 16+** and npm (for React integration)
- **Git**

### Option 1: Automated Setup (Recommended)

```bash
# Clone the repository
git clone <repository-url>
cd bmb-rails

# Run the automated setup script
./setup_local.sh
```

🎉 **That's it!** The script handles everything automatically.

### Option 2: Manual Setup

```bash
# Set up Ruby version
rbenv install 3.3.0
rbenv local 3.3.0
eval "$(rbenv init -)"

# Install dependencies
bundle install

# Setup database
bin/rails db:create db:migrate db:seed

# Install React dependencies (optional)
cd react-bracket-builder && npm install && cd ..
```

### 3. Start the Application

```bash
# Start Rails server
bin/rails server

# Application runs at: http://localhost:3000
```

---

## 🧪 Local Testing Guide

### Web Interface Testing

#### **Home Page**
```bash
# Visit the landing page
open http://localhost:3000

# Features to test:
# ✅ Responsive design
# ✅ Navigation menu
# ✅ Hero section with call-to-action
# ✅ Feature cards
# ✅ React bracket builder mounting point
```

#### **Static Pages**
```bash
# About page
open http://localhost:3000/about

# Privacy policy
open http://localhost:3000/privacy-policy

# Test responsive design on mobile viewport
```

### API Testing

#### **Quick API Health Check**
```bash
# Test API is working
curl http://localhost:3000/api/v1/brackets.json

# Expected: JSON response with 3 sample brackets
```

#### **Brackets API**
```bash
# List all brackets
curl -H "Accept: application/json" http://localhost:3000/api/v1/brackets.json

# Get specific bracket by slug
curl http://localhost:3000/api/v1/brackets/march-madness-2024.json

# Filter brackets by year
curl "http://localhost:3000/api/v1/brackets.json?year=2024"

# Filter by voting type
curl "http://localhost:3000/api/v1/brackets.json?voting=true"

# Create new bracket (POST)
curl -X POST http://localhost:3000/api/v1/brackets.json \
  -H "Content-Type: application/json" \
  -d '{
    "bracket": {
      "title": "Test Tournament",
      "num_teams": 8,
      "year": "2024",
      "month": "December"
    }
  }'
```

#### **Teams API**
```bash
# List all teams
curl http://localhost:3000/api/v1/teams.json

# Get seeded teams only
curl "http://localhost:3000/api/v1/teams.json?seeded=true"

# Get specific team
curl http://localhost:3000/api/v1/teams/1.json

# Create new team
curl -X POST http://localhost:3000/api/v1/teams.json \
  -H "Content-Type: application/json" \
  -d '{
    "team": {
      "name": "Test Team",
      "seed": 1
    }
  }'
```

#### **Plays API (User Submissions)**
```bash
# List plays for March Madness bracket
curl http://localhost:3000/api/v1/brackets/march-madness-2024/plays.json

# Get specific play details
curl http://localhost:3000/api/v1/brackets/march-madness-2024/plays/1.json

# Submit new bracket play
curl -X POST http://localhost:3000/api/v1/brackets/march-madness-2024/plays.json \
  -H "Content-Type: application/json" \
  -d '{
    "play": {
      "user_id": 1,
      "picks_data": {
        "round_1": [1, 2, 1, 2],
        "round_2": [1, 2],
        "finals": [1]
      }
    }
  }'
```

#### **Users API**
```bash
# List users
curl http://localhost:3000/api/v1/users.json

# Get specific user
curl http://localhost:3000/api/v1/users/1.json

# Create new user
curl -X POST http://localhost:3000/api/v1/users.json \
  -H "Content-Type: application/json" \
  -d '{
    "user": {
      "username": "newuser",
      "email": "newuser@example.com",
      "password": "password",
      "first_name": "New",
      "last_name": "User"
    }
  }'
```

### Sample Data Testing

The application comes with comprehensive sample data:

- **5 Users:** Including admin and test accounts
- **32 Teams:** March Madness style with seeds
- **3 Brackets:** Sports tournament, voting, and paid brackets
- **31 Matches:** Complete tournament structure
- **5 Plays:** Sample user submissions
- **8 Picks:** Mix of predictions and results

#### **Test Accounts**
```
Admin Account:
- Email: admin@bracketmakerbuilder.com
- Password: password

User Account:
- Email: john@example.com  
- Password: password
```

### Advanced Testing

#### **Database Console**
```bash
# Open Rails console
bin/rails console

# Test data integrity
> User.count
# => 5

> Bracket.count
# => 3

> BracketMatch.includes(:team1, :team2, :winner).first
# => #<BracketMatch...>

# Test relationships
> bracket = Bracket.first
> bracket.bracket_matches.count
# => 31

> user = User.first
> user.plays.count
# => 1
```

#### **Testing Model Validations**
```ruby
# In Rails console
bracket = Bracket.new
bracket.valid?
# => false

bracket.errors.full_messages
# => ["Title can't be blank", "Slug can't be blank", "Num teams can't be blank"]

bracket.update(title: "Test", num_teams: 8)
# => true (creates slug automatically)
```

#### **Testing Serialization**
```ruby
# Test JSON serialization
bracket = Bracket.first
bracket.round_names = ["Round 1", "Semifinals", "Finals"]
bracket.save!

play = Play.first
play.picks_data = { "round_1" => [1, 2], "finals" => [1] }
play.save!
```

---

## 🔧 Development Workflow

### Running Tests
```bash
# Run all tests (when implemented)
bin/rails test

# Run specific test file
bin/rails test test/models/bracket_test.rb

# Test with coverage
COVERAGE=true bin/rails test
```

### Code Quality
```bash
# Check code formatting
npm run pretty:check

# Format code
npm run pretty

# Ruby linting (if configured)
bundle exec rubocop
```

### React Development
```bash
# Build React app for Rails integration
cd react-bracket-builder
npm run build:rails

# Development mode with hot reload
npm run dev:rails
```

### Database Management
```bash
# Create new migration
bin/rails generate migration AddColumnToModel column:type

# Run pending migrations
bin/rails db:migrate

# Rollback last migration
bin/rails db:rollback

# Reset database with fresh data
bin/rails db:reset
```

---

## 📊 Performance Testing

### Load Testing API Endpoints
```bash
# Install Apache Bench
brew install apache-bench

# Test brackets endpoint
ab -n 100 -c 10 http://localhost:3000/api/v1/brackets.json

# Test specific bracket
ab -n 100 -c 10 http://localhost:3000/api/v1/brackets/march-madness-2024.json
```

### Memory & Performance
```bash
# Monitor Rails process
top -pid $(pgrep -f rails)

# Check database performance
bin/rails runner "
  require 'benchmark'
  puts Benchmark.measure { 
    Bracket.includes(:bracket_matches, :picks).first(10) 
  }
"
```

---

## 🐛 Troubleshooting

### Common Issues

#### Ruby Version Issues
```bash
# Check Ruby version
ruby --version
# Should be: ruby 3.3.0

# If wrong version:
rbenv local 3.3.0
eval "$(rbenv init -)"
```

#### Bundle Issues
```bash
# Update bundler
gem install bundler

# Clean install
bundle clean --force
bundle install
```

#### Database Issues
```bash
# Check PostgreSQL is running
brew services list | grep postgresql

# Start PostgreSQL
brew services start postgresql

# Reset database if corrupted
bin/rails db:drop db:create db:migrate db:seed
```

#### Asset Issues
```bash
# Clear asset cache
bin/rails tmp:clear

# Precompile assets
bin/rails assets:precompile

# Check manifest
ls -la app/assets/config/
```

#### Port Already in Use
```bash
# Kill process on port 3000
lsof -ti:3000 | xargs kill -9

# Or run on different port
bin/rails server -p 3001
```

### Debug Mode
```bash
# Run server with debugging
bin/rails server --debugger

# View detailed logs
tail -f log/development.log

# Interactive debugging in code
# Add: binding.irb
```

---

## 📁 Project Structure

```
bmb-rails/
├── app/
│   ├── controllers/
│   │   ├── api/v1/          # REST API controllers
│   │   ├── pages_controller.rb
│   │   └── application_controller.rb
│   ├── models/              # ActiveRecord models
│   │   ├── bracket.rb
│   │   ├── user.rb
│   │   ├── team.rb
│   │   └── ...
│   ├── views/
│   │   ├── layouts/         # Application layout
│   │   └── pages/           # Static pages
│   └── assets/
│       ├── stylesheets/     # CSS files
│       └── javascripts/     # JS files
├── config/
│   ├── routes.rb            # URL routing
│   ├── database.yml         # Database config
│   └── application.rb       # Rails config
├── db/
│   ├── migrate/             # Database migrations
│   ├── seeds.rb             # Sample data
│   └── schema.rb            # Database schema
├── plugin/                  # Legacy WordPress code (reference)
├── react-bracket-builder/   # React application
├── API_DOCUMENTATION.md     # Complete API reference
├── MIGRATION_GUIDE.md       # WordPress→Rails migration guide
├── SETUP.md                # Deployment instructions
└── README.md               # This file
```

---

## 🚀 Production Deployment

See [SETUP.md](SETUP.md) for detailed production deployment instructions including:
- Docker deployment
- Environment configuration
- SSL setup
- Performance optimization
- Monitoring setup

---

## 📖 Documentation

- **[API Documentation](API_DOCUMENTATION.md)** - Complete API reference comparing WordPress vs Rails
- **[Migration Guide](MIGRATION_GUIDE.md)** - WordPress to Rails migration details
- **[Setup Guide](SETUP.md)** - Production deployment instructions
- **[Migration Success](MIGRATION_SUCCESS.md)** - Migration completion status

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

---

## 🎯 Migration Status

### ✅ Completed
- [x] Ruby on Rails 8.0 application setup
- [x] PostgreSQL database with comprehensive schema
- [x] RESTful API with 4 core controllers
- [x] Responsive web interface (Home, About, Privacy)
- [x] User authentication system
- [x] Sample data and testing framework
- [x] Asset pipeline integration
- [x] Complete API documentation

### 🔄 In Progress
- [ ] React app integration with Rails API
- [ ] User authentication UI
- [ ] Advanced bracket features

### ⏳ Planned
- [ ] Push notifications
- [ ] Stripe payment integration
- [ ] Email notifications
- [ ] Mobile app API enhancements
- [ ] Real-time updates with ActionCable

---

**🏆 Ready to build amazing tournament brackets with modern Rails technology!**