# Bracket Maker Builder Rails Setup Guide

## Prerequisites

- Ruby 3.1+ (check `.ruby-version` file)
- Node.js 16+ and npm
- PostgreSQL 12+
- Git

## Quick Start

### 1. Clone and Setup
```bash
git clone <repository-url>
cd bmb-rails

# Install Ruby dependencies
bundle install

# Install Node.js dependencies for React app
cd react-bracket-builder
npm install
cd ..
```

### 2. Database Setup
```bash
# Create database
bin/rails db:create

# Run migrations
bin/rails db:migrate

# Optional: Seed with sample data
bin/rails db:seed
```

### 3. Build React App
```bash
cd react-bracket-builder
npm run build:rails
cd ..
```

### 4. Start the Server
```bash
bin/rails server
```

Visit `http://localhost:3000` to see the application.

## Environment Variables

Create a `.env` file in the project root:

```bash
# Database
DATABASE_URL=postgresql://username:password@localhost:5432/bmb_rails_development

# Rails
RAILS_ENV=development
SECRET_KEY_BASE=your-secret-key-base-here

# Optional: External services
STRIPE_PUBLISHABLE_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...
MAILCHIMP_API_KEY=your-mailchimp-key
SENTRY_DSN=your-sentry-dsn
```

## Development Workflow

### Running the Application
```bash
# Start Rails server
bin/rails server

# In another terminal, watch for React changes (development mode)
cd react-bracket-builder
npm run dev:rails
```

### Database Operations
```bash
# Create new migration
bin/rails generate migration AddColumnToTable column:type

# Run pending migrations
bin/rails db:migrate

# Rollback last migration
bin/rails db:rollback

# Reset database (WARNING: Destroys all data)
bin/rails db:reset
```

### Running Tests
```bash
# Run all tests
bin/rails test

# Run specific test file
bin/rails test test/models/bracket_test.rb

# Run with coverage
COVERAGE=true bin/rails test
```

## Production Deployment

### Using Docker

1. **Build the image:**
```bash
docker build -t bmb-rails .
```

2. **Run with docker-compose:**
```bash
docker-compose up -d
```

### Manual Deployment

1. **Server Setup:**
```bash
# Install dependencies
sudo apt-get update
sudo apt-get install -y ruby nodejs npm postgresql postgresql-contrib

# Install rbenv for Ruby version management
curl -fsSL https://github.com/rbenv/rbenv-installer/raw/HEAD/bin/rbenv-installer | bash
rbenv install $(cat .ruby-version)
```

2. **Application Deployment:**
```bash
# Clone repository
git clone <repository-url> /var/www/bmb-rails
cd /var/www/bmb-rails

# Install dependencies
bundle install --deployment --without development test
cd react-bracket-builder && npm ci && npm run build:rails && cd ..

# Setup database
RAILS_ENV=production bin/rails db:create db:migrate

# Precompile assets
RAILS_ENV=production bin/rails assets:precompile

# Start application (use process manager like systemd in production)
RAILS_ENV=production bin/rails server -p 3000
```

### Environment Configuration

#### Production Environment Variables
```bash
# Required
RAILS_ENV=production
DATABASE_URL=postgresql://user:pass@localhost:5432/bmb_rails_production
SECRET_KEY_BASE=<generate-with-rails-secret>
RAILS_MASTER_KEY=<from-config/master.key>

# Optional but recommended
RAILS_SERVE_STATIC_FILES=true
RAILS_LOG_TO_STDOUT=true
WEB_CONCURRENCY=2
```

#### Generate Secret Key
```bash
bin/rails secret
```

### Web Server Configuration (Nginx)

Create `/etc/nginx/sites-available/bmb-rails`:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    
    root /var/www/bmb-rails/public;
    
    location / {
        proxy_pass http://localhost:3000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
    
    location /assets/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/bmb-rails /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### SSL Configuration (Let's Encrypt)

```bash
sudo apt-get install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

## Database Management

### Backup
```bash
# Create backup
pg_dump bmb_rails_production > backup_$(date +%Y%m%d_%H%M%S).sql

# Automated backup script
#!/bin/bash
BACKUP_DIR="/var/backups/bmb-rails"
DATE=$(date +%Y%m%d_%H%M%S)
pg_dump bmb_rails_production | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete
```

### Restore
```bash
# Restore from backup
psql bmb_rails_production < backup_file.sql
```

## Monitoring

### Log Management
```bash
# View Rails logs
tail -f log/production.log

# Rotate logs (add to crontab)
0 0 * * * cd /var/www/bmb-rails && bin/rails log:clear
```

### Health Checks
The application includes a health check endpoint:
```
GET /up
```

### Performance Monitoring

Add to `Gemfile`:
```ruby
gem 'newrelic_rpm'
gem 'rack-mini-profiler'
```

## Troubleshooting

### Common Issues

#### Asset Loading Problems
```bash
# Precompile assets
RAILS_ENV=production bin/rails assets:precompile

# Clear asset cache
bin/rails tmp:clear
```

#### Database Connection Issues
```bash
# Check PostgreSQL service
sudo systemctl status postgresql

# Test database connection
bin/rails dbconsole
```

#### React App Not Loading
```bash
# Rebuild React bundle
cd react-bracket-builder
npm run build:rails
cd ..

# Check if bundle exists
ls -la app/assets/javascripts/react-bracket-builder/
```

#### Permission Issues
```bash
# Fix file permissions
sudo chown -R www-data:www-data /var/www/bmb-rails
sudo chmod -R 755 /var/www/bmb-rails
```

### Debugging

#### Enable Debug Mode
```bash
# In development
RAILS_ENV=development bin/rails console

# Check logs
tail -f log/development.log
```

#### Database Issues
```bash
# Check migration status
bin/rails db:migrate:status

# Reset database (development only)
bin/rails db:drop db:create db:migrate db:seed
```

## Maintenance

### Regular Updates
```bash
# Update gems
bundle update

# Update npm packages
cd react-bracket-builder
npm update
cd ..

# Update Rails
bundle update rails
```

### Security Updates
```bash
# Check for vulnerabilities
bundle audit

# Update specific gems
bundle update gem_name
```

## Development Tools

### Recommended VS Code Extensions
- Ruby
- Rails
- ERB Language Server
- PostgreSQL Explorer
- GitLens

### Useful Commands
```bash
# Generate new controller
bin/rails generate controller ControllerName

# Generate new model
bin/rails generate model ModelName

# Rails console
bin/rails console

# Rails routes
bin/rails routes

# Database console
bin/rails dbconsole
```