# WP Bracket Builder

A WordPress plugin for creating and managing tournament brackets.

## Table of Contents
- [Prerequisites](#prerequisites)
- [Development Setup](#development-setup)
- [Development Tools](#development-tools)
  - [Testing](#testing)
  - [Code Coverage](#code-coverage)
  - [Image Generation](#image-generation)
- [Deployment](#deployment)
- [Reference](#reference)

## Prerequisites

1. Install required tools:
   - [Docker](https://docs.docker.com/get-docker/)
   - [Docker Compose](https://docs.docker.com/compose/install/)
   - [Task](https://taskfile.dev/installation/)
   - [Git](https://git-scm.com/downloads)

2. Set up your editor:
   - Install [Prettier](https://prettier.io/docs/en/editors) for code formatting
   - Configure [Coverage Gutters](#code-coverage) for test coverage visualization

## Development Setup

1. Clone the repository:
```bash
git clone <repository-url>
cd wp-bracket-builder
```

2. Set up environment:
```bash
# Copy and configure environment variables
cp .env.example .env
```

3. Download required plugins to `docker/wordpress/plugins`:
   - [Sentry](https://wordpress.org/plugins/wp-sentry-integration/)
   - [WooCommerce](https://wordpress.org/plugins/woocommerce/)
   - [Oxygen](https://drive.google.com/file/d/19UxR1oMcq7yU1EkXxhuC2FMrXPVx8hI2/view?usp=sharing)
   - [Oxygen-WooCommerce](https://drive.google.com/file/d/19Ux5P87RLMcGkyF3n9zbqYU8qCMOyNPb/view?usp=sharing)

4. Install dependencies and start services:
```bash
# Install dependencies
task composer:install  # PHP dependencies
task react:install    # React dependencies

# Start WordPress
task wp:up           # Start WordPress containers
task wp:install      # Initialize WordPress with sample data

# Start React development
task react:start     # Start React development server
```

5. Access the site:
   - Frontend: `localhost:8008`
   - Admin: `localhost:8008/wp-admin` (username: `admin`, password: `admin`)

> **Note**: The WordPress installation comes pre-configured with all necessary pages, templates, and sample data. You don't need to create any pages or Oxygen templates manually.

## Development Tools

### Testing
```bash
# Run all tests
task wp:test

# Run with coverage report
task wp:test:coverage     # Report available at localhost:8080/coverage
```

### Code Coverage

1. Install [Coverage Gutters](https://marketplace.visualstudio.com/items?itemName=ryanluker.vscode-coverage-gutters) VS Code extension
2. Add to VS Code settings.json:
```json
{
  "coverage-gutters.coverageFileNames": ["coverage.xml"],
  "coverage-gutters.remotePathResolve": [
    [
      "/var/www/html/wp-content/plugins/wp-bracket-builder",
      "${workspaceFolder}/plugin/"
    ]
  ]
}
```

### Image Generation

The application uses three Docker containers for image generation:

1. **image-generator** (`:3000`)
   - Handles WordPress requests for bracket images/PDFs
   - Uses Puppeteer for screenshots

2. **react-server** (`:3001`)
   - Serves API for screenshot generation
   - Manages production bundled code

3. **react-client** (`:8080`)
   - Development server for React components

## Deployment

### Staging
```bash
task prod:build              # Build plugin
task prod:push              # Push to registry
trellis deploy staging      # Deploy
```

### Production
```bash
ssh wpbb@147.182.190.133
cd wp-bracket-builder
git pull
task composer:install
task react:install
task react:build
```

## Reference

### Bracket Tags
- `bmb_official` - Display on official brackets page
- `bmb_vip_featured` - Display on celebrity picks page
- `bmb_vip_profile` - Display on author's profile page

### Useful Commands
```bash
task wp:log         # View WordPress debug log
task wp:log:watch   # Watch debug log in real-time
task wp:db:query    # Run SQL queries
task wp:bash        # Open shell in WordPress container
```

### Pre-configured Pages
The following pages are automatically created during installation:
- Dashboard (`/dashboard`)
- Official Brackets (`/official-brackets`)
- Celebrity Picks (`/celebrity-picks`)
- Bracket Builder (`/bracket-builder`)
- Stripe Onboarding Redirect (`/stripe-onboarding-redirect`)

### Resources
- [Plugin Unit Tests](https://make.wordpress.org/cli/handbook/misc/plugin-unit-tests/)
- [Writing PHPUnit Tests](https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/)
