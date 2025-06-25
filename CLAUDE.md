# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Common Development Commands

This project uses [Task](https://taskfile.dev/) as the task runner. Key commands:

### WordPress Development
- `task wp:up` - Start WordPress containers
- `task wp:down` - Stop and remove WordPress containers
- `task wp:bash` - Open shell in WordPress container
- `task wp:log` - View WordPress debug log
- `task wp:log:watch` - Watch debug log in real-time

### Dependencies
- `task composer:install` - Install PHP dependencies
- `task react:install` - Install React dependencies

### Development Workflow
- `task react:start` - Start React development server
- `task react:build` - Build React for production

### Testing
- `task wp:test:install` - Install test dependencies
- `task wp:test` - Run all tests
- `task wp:test:coverage` - Run tests with coverage (available at localhost:8080/coverage)

### Code Quality
- `npm run pretty:check` - Check code formatting
- `npm run pretty` - Format code with Prettier

### Mobile Development
- `task flutter:get` - Install Flutter dependencies
- `task flutter:run` - Run Flutter app
- `task app:version:bump-patch` - Increment patch version
- `task ios:build` - Build iOS app
- `task ios:push` - Upload to App Store Connect

### Database Operations
- `task wp:import:prod` - Import production database
- `task wp:db:query` - Run SQL queries
- `task wp:sync:plugins` - Sync plugins from production

## Architecture Overview

### WordPress Plugin Structure
- **Plugin Root**: `/plugin/` - Main WordPress plugin directory
- **Features**: Domain-driven design with feature modules in `/plugin/Features/`
  - `Bracket/` - Core bracket functionality and queries
  - `Notifications/` - Push notifications and email notifications
  - `VotingBracket/` - Voting system for brackets
  - `MobileApp/` - Mobile app integration endpoints

### Domain Layer
- **Domain Models**: `/plugin/Includes/Domain/` - Core business entities
  - `Bracket.php`, `Pick.php`, `Play.php`, `Team.php`, `User.php`
- **Repository Pattern**: `/plugin/Includes/Repository/` - Data access layer
- **Services**: `/plugin/Includes/Service/` - Business logic services

### Frontend Architecture
- **React Components**: `/react-bracket-builder/src/` - Main React application
- **Mobile App**: `/bmb_mobile/` - Flutter mobile application
- **Image Generation**: `/image-generator/` - Node.js service for PDF/image generation

### Key Services
- **Notifications**: Email (Mailchimp) and push notifications (FCM)
- **Payments**: Stripe integration for paid tournaments
- **Image Processing**: Puppeteer-based screenshot generation
- **Object Storage**: S3 integration for media files

### API Structure
- REST API endpoints in `/plugin/Includes/Controllers/`
- Mobile app specific endpoints in `/plugin/Features/MobileApp/`
- Notification APIs in `/plugin/Features/Notifications/Presentation/`

### Database
- Custom tables for bracket data alongside WordPress post types
- Repository classes handle database operations
- Uses WordPress custom post types for brackets and related data

### Docker Services
- **WordPress**: Main application container
- **Database**: MySQL/MariaDB
- **React Development**: Hot-reload development server
- **Image Generator**: Puppeteer service for screenshots
- **React Server**: Production React bundle server

### Testing
- PHPUnit for PHP unit and integration tests
- Jest for React component tests
- Coverage reporting with XML output for VS Code integration

### Environment Setup
- Docker Compose for local development
- Trellis for production deployment
- Environment variables in `.env` file
- Plugin syncing from production server

### Key Integrations
- **Stripe**: Payment processing and Connect accounts
- **Mailchimp**: Email marketing and notifications
- **Firebase**: Push notifications for mobile
- **S3**: Object storage for images and files
- **Sentry**: Error tracking and monitoring