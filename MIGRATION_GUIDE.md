# WordPress to Rails Migration Guide

This document outlines the migration from WordPress/Oxygen templates to Ruby on Rails for the Bracket Maker Builder platform.

## Overview

The migration transforms the WordPress-based frontend while maintaining the existing React bracket builder functionality. The new Rails application provides:

- Modern, responsive web interface
- RESTful API endpoints
- Better performance and scalability
- Improved maintainability

## Architecture Changes

### Before (WordPress)
- WordPress with Oxygen page builder templates
- PHP backend with custom post types
- WordPress REST API
- React app embedded in WordPress pages

### After (Rails)
- Ruby on Rails 8.0 application
- Modern MVC architecture
- RESTful JSON API
- React app integrated with Rails asset pipeline
- PostgreSQL database

## New Rails Structure

### Models
- `Bracket` - Tournament brackets with metadata
- `User` - User accounts and authentication  
- `Team` - Teams participating in brackets
- `BracketMatch` - Individual matches within brackets
- `Pick` - User predictions and actual results
- `Play` - User's complete bracket submissions

### Controllers

#### Pages Controller
Handles static pages:
- `GET /` - Home page with React bracket builder
- `GET /about` - About page
- `GET /privacy-policy` - Privacy policy page

#### API Controllers
RESTful API endpoints under `/api/v1/`:

- `BracketsController` - CRUD operations for brackets
  - `GET /api/v1/brackets` - List brackets with filtering
  - `POST /api/v1/brackets` - Create new bracket
  - `GET /api/v1/brackets/:slug` - Get bracket details
  - `PUT /api/v1/brackets/:slug` - Update bracket
  - `DELETE /api/v1/brackets/:slug` - Delete bracket

- `PlaysController` - User bracket submissions
  - `GET /api/v1/brackets/:bracket_id/plays` - List plays for bracket
  - `POST /api/v1/brackets/:bracket_id/plays` - Submit new play
  - `GET /api/v1/brackets/:bracket_id/plays/:id` - Get play details
  - `PUT /api/v1/brackets/:bracket_id/plays/:id` - Update play

- `TeamsController` - Team management
  - `GET /api/v1/teams` - List teams
  - `POST /api/v1/teams` - Create team
  - `PUT /api/v1/teams/:id` - Update team

### Views
- ERB templates with responsive CSS
- Bootstrap-inspired grid system
- Mobile-first design approach
- Semantic HTML structure

## Database Schema

### Key Tables

#### brackets
```sql
- id (primary key)
- title (string, required)
- slug (string, unique, required)
- month (string)
- year (string) 
- num_teams (integer, required)
- wildcard_placement (integer)
- results_first_updated_at (datetime)
- num_plays (integer, default: 0)
- fee (decimal)
- should_notify_results_updated (boolean, default: false)
- is_voting (boolean, default: false)
- live_round_index (integer, default: 0)
- round_names (text, serialized array)
- created_at, updated_at
```

#### users
```sql
- id (primary key)
- username (string, unique, required)
- email (string, unique, required)
- password_digest (string, required)
- first_name, last_name (string)
- active (boolean, default: true)
- last_login_at (datetime)
- created_at, updated_at
```

## React Integration

The existing React bracket builder is integrated with Rails through:

1. **Asset Pipeline**: React bundle served via Rails assets
2. **API Communication**: React app calls Rails API endpoints
3. **DOM Mounting**: React mounts to `#react-bracket-builder` div
4. **CDN Dependencies**: React/ReactDOM loaded from CDN

## Migration Steps

### 1. Environment Setup
```bash
# Install Ruby dependencies
bundle install

# Setup database
bin/rails db:create
bin/rails db:migrate

# Build React app for Rails
cd react-bracket-builder
npm run build:rails
```

### 2. Data Migration
Create migration scripts to transfer data from WordPress to Rails:
- Extract bracket data from WordPress custom posts
- Migrate user accounts and relationships
- Convert team and match data
- Preserve pick/play history

### 3. React App Updates
Update the React app to use Rails API endpoints:
- Replace WordPress REST API calls
- Update authentication flow
- Modify data structures to match Rails JSON format

### 4. Styling Migration
Convert Oxygen templates to Rails views:
- Extract CSS from Oxygen templates
- Create responsive ERB templates
- Implement mobile-first design
- Maintain visual consistency

## API Documentation

### Bracket API

#### List Brackets
```
GET /api/v1/brackets
Query Parameters:
- year: Filter by year
- month: Filter by month  
- voting: Filter by voting type (true/false)

Response:
{
  "brackets": [
    {
      "id": 1,
      "title": "March Madness 2024",
      "slug": "march-madness-2024",
      "num_teams": 64,
      "is_voting": false,
      "created_at": "2024-01-01T00:00:00Z"
    }
  ]
}
```

#### Get Bracket Details
```
GET /api/v1/brackets/:slug

Response:
{
  "bracket": {
    "id": 1,
    "title": "March Madness 2024",
    "slug": "march-madness-2024",
    "round_names": ["Round 1", "Round 2", "Final"],
    "matches": [...],
    "results": [...]
  }
}
```

## Deployment Considerations

### Production Environment
- Use PostgreSQL for database
- Configure Redis for caching
- Set up CDN for static assets
- Enable HTTPS with SSL certificates
- Configure environment variables for secrets

### Performance Optimizations
- Database indexing on frequently queried columns
- API response caching
- Gzip compression for assets
- Image optimization for team logos
- Lazy loading for large bracket data

## Security Improvements

### Authentication
- Secure password hashing with bcrypt
- Session-based authentication
- CSRF protection for forms
- Rate limiting for API endpoints

### Data Protection
- Input validation and sanitization
- SQL injection prevention via ActiveRecord
- XSS protection in views
- Secure headers configuration

## Testing Strategy

### Unit Tests
- Model validations and associations
- Controller actions and responses
- Helper methods and utilities

### Integration Tests
- API endpoint functionality
- User authentication flows
- Bracket creation and management

### System Tests
- End-to-end bracket workflows
- React app integration
- Mobile responsiveness

## Monitoring and Maintenance

### Logging
- Structured logging with Rails logger
- API request/response logging
- Error tracking and alerting
- Performance monitoring

### Backup Strategy
- Regular database backups
- Asset backup and versioning
- Configuration backup
- Disaster recovery procedures

## Future Enhancements

### Planned Features
- Real-time bracket updates with ActionCable
- Advanced analytics and reporting
- Social sharing improvements
- Enhanced mobile app integration
- Performance dashboard for administrators

### Technical Debt
- Implement comprehensive test suite
- Add API versioning strategy
- Optimize database queries
- Improve error handling and user feedback
- Add comprehensive documentation