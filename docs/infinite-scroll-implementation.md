# Infinite Scroll Implementation

## Current State
- `BracketBoardPage.php` loads all brackets at once
- Uses `posts_per_page => -1`
- Bracket list items are rendered server-side using PHP templates
- React components handle bracket interactions (play, score, etc.)
- Current data flow:
  - Server-side PHP templates fetch data using `BracketRepo`
  - React components fetch their own data through WordPress REST API
  - Filtering uses URL parameters and server-side rendering

## Architecture Overview

### Code Duplication Refactoring
To address the duplication between `BracketHtmlApi` and `BracketsCommon`, we'll introduce:

1. **Query Builder Service**
   - New class to encapsulate all bracket query building logic
   - Handles status filtering
   - Manages pagination parameters
   - Handles mobile app meta queries
   - Used by both HTML API and template rendering
   - Benefits:
     - Single source of truth for query logic
     - Consistent filtering across endpoints
     - Easier maintenance and updates
     - Type-safe query building

2. **Bracket List Renderer Service**
   - Extracts shared rendering logic
   - Handles bracket list item rendering
   - Manages pagination rendering
   - Used by both API and template code
   - Benefits:
     - Consistent HTML output
     - Shared template handling
     - Reduced template duplication
     - Better separation of concerns

3. **Shared Constants and Types**
   - Move shared constants to a dedicated file
   - Define shared types for query parameters
   - Create enums for status values
   - Benefits:
     - Single source for constants
     - Type safety across codebase
     - Better IDE support
     - Easier refactoring

### React Application Structure
The React application is built and bundled into:
- `plugin/Includes/react-bracket-builder/build/wordpress/index.js`
- `plugin/Includes/react-bracket-builder/build/wordpress/index.css`

The app is initialized with configuration through WordPress:
- REST API base URL: `wp-bracket-builder/v1/`
- WordPress nonce for authentication
- Other configuration passed via `wpbb_app_obj`

### API Integration Pattern
The React application uses a consistent pattern for API integration:
1. `WpHttpClient` class handles:
   - Base URL configuration
   - WordPress nonce authentication
   - Request/response handling

2. API classes organize endpoints by feature:
   - `BracketApi` for bracket operations
   - `NotificationApi` for notifications
   - Each with specific methods for different operations

3. Type definitions for request/response data:
   - `BracketReq`, `BracketRes` for bracket data
   - `PlayReq`, `PlayRes` for play data
   - Ensures type safety and consistent data structure

### HTML Fragment Endpoint
We'll create a dedicated endpoint architecture for serving HTML fragments, separate from our JSON API endpoints. This separation provides several benefits:
- Clear distinction between JSON API and HTML fragment endpoints
- Specialized handling for HTML responses
- Simplified pagination and fragment management
- Better integration with PHP templates

### Component Structure
1. **HTML Fragment Base Controller**
   - Handles route registration
   - Manages permission checks
   - Provides pagination support
   - Handles HTML response formatting
   - Integrates with WordPress REST API

2. **Bracket List Endpoint**
   - Extends HTML Fragment Base Controller
   - Manages bracket list specific logic
   - Handles filter state
   - Returns rendered HTML fragments
   - Includes pagination metadata

3. **React Integration**
   - Creates a new React component for infinite scroll
   - Mounts in the existing bracket list container
   - Preserves existing filter buttons and UI
   - Handles scroll position and loading states
   - Fetches HTML fragments from the new endpoint
   - Appends new content to the list
   - Maintains compatibility with existing React components
   - Integration points:
     - Uses existing `WpHttpClient` for API calls
     - Maintains WordPress nonce authentication
     - Follows existing API class pattern
     - Preserves existing React component mounting

## Files to Modify

### Backend Files
1. `plugin/Features/Bracket/Infrastructure/BracketQueryBuilder.php` (New)
   - Encapsulates all bracket query building logic
   - Handles status filtering
   - Manages pagination parameters
   - Handles mobile app meta queries
   - Used by both API and template code

2. `plugin/Features/Bracket/Presentation/BracketListRenderer.php` (New)
   - Extracts shared rendering logic
   - Handles bracket list item rendering
   - Manages pagination rendering
   - Used by both API and template code

3. `plugin/Features/Bracket/Domain/BracketQueryTypes.php` (New)
   - Defines shared types and constants
   - Contains status enums
   - Defines query parameter types
   - Used across the codebase

4. `plugin/Features/Bracket/Presentation/Html/BracketHtmlApi.php`
   - Update to use BracketQueryBuilder
   - Update to use BracketListRenderer
   - Focus on API-specific logic
   - Remove duplicated code

5. `plugin/Public/Partials/shared/BracketsCommon.php`
   - Update to use BracketQueryBuilder
   - Update to use BracketListRenderer
   - Focus on template-specific logic
   - Remove duplicated code

6. `plugin/Includes/Repository/BracketRepo.php`
   - Add pagination support to `get_all()`
   - Update to work with BracketQueryBuilder
   - Add total count calculation
   - Update query parameters

### Frontend Files
1. `plugin/Includes/react-bracket-builder/src/brackets/shared/api/BracketListApi.ts` (New)
   - Create new API class for bracket list operations
   - Extend existing API pattern
   - Handle HTML fragment requests
   - Manage pagination parameters
   - Type definitions for request/response

2. `plugin/Includes/react-bracket-builder/src/components/BracketList/` (New)
   - Create React component to manage infinite scroll
   - Handle scroll position and loading states
   - Use `BracketListApi` for data fetching
   - Manage loading indicators
   - Handle filter state
   - Integration points:
     - Mount in existing bracket list container
     - Preserve existing filter buttons
     - Handle filter state changes
     - Maintain compatibility with existing React components
     - Use existing `WpHttpClient` for API calls
     - Follow existing API class pattern

3. `plugin/Public/Partials/BracketBoard/BracketBoardPage.php`
   - Update to use React component for list container
   - Keep existing filter buttons
   - Add container for React component
   - Integration points:
     - Replace static list with React container
     - Pass initial filter state
     - Maintain existing layout and styling

## Implementation Steps

### 1. Backend Foundation
1. Create Shared Services
   - Implement BracketQueryBuilder
   - Implement BracketListRenderer
   - Define shared types and constants
   - Add comprehensive tests

2. Update Existing Code
   - Refactor BracketHtmlApi to use shared services
   - Update BracketsCommon to use shared services
   - Update repository to work with query builder
   - Ensure backward compatibility
   - Add integration tests

3. Create HTML Fragment Base Controller
   - Set up base controller structure
   - Implement route registration
   - Add pagination handling
   - Add HTML response formatting
   - Add template integration

4. Create Bracket List Endpoint
   - Extend base controller
   - Add bracket-specific logic
   - Implement filter handling
   - Add template rendering
   - Add pagination metadata

5. Update Repository
   - Add pagination support
   - Add total count calculation
   - Update query building

6. Update Templates
   - Keep existing bracket item template
   - Add support for partial list rendering
   - Ensure proper HTML structure for React integration

### 2. Frontend Integration
1. Create BracketListApi
   - Implement new API class
   - Add methods for fetching HTML fragments
   - Handle pagination parameters
   - Define request/response types
   - Follow existing API pattern

2. Create React Component
   - Implement infinite scroll logic
   - Handle scroll position detection
   - Manage loading states
   - Use `BracketListApi` for data fetching
   - Append new content to list
   - Handle filter changes
   - Show loading indicators
   - Handle "no more content" state
   - Integration with existing React components:
     - Mount in existing container
     - Preserve filter button functionality
     - Handle filter state changes
     - Maintain event handling for bracket actions
     - Use existing `WpHttpClient` for API calls
     - Follow existing API class pattern

3. Update BracketBoardPage
   - Add React component container
   - Keep existing filter buttons
   - Pass initial data to React component
   - Handle filter state changes
   - Integration points:
     - Replace static list with React container
     - Maintain existing layout
     - Preserve filter button functionality

### 3. Testing & Validation
1. Test Backend
   - Pagination functionality
   - Filter state persistence
   - HTML fragment generation
   - Error handling

2. Test Frontend
   - Infinite scroll behavior
   - Loading states
   - Filter functionality
   - Mobile responsiveness
   - React component integration
   - Existing functionality preservation
   - REST API integration
   - Authentication handling
   - API class pattern compliance

## Key Decisions
1. Initial batch size (default: 10)?
2. Scroll threshold for loading more?
3. Server-side caching strategy?
4. How to handle "no more content"?
5. How to handle filter state changes?
6. Error state handling approach?
7. Integration with existing React components:
   - How to handle bracket action events?
   - How to maintain filter state?
   - How to preserve existing functionality?
8. REST API integration:
   - How to handle authentication?
   - How to maintain existing API endpoints?
   - How to structure the new HTML fragment endpoint?
9. API class structure:
   - How to organize HTML fragment endpoints?
   - What types to define for request/response?
   - How to handle pagination in the API class?
10. Shared Services Structure:
    - How to handle service dependencies?
    - What level of abstraction is appropriate?
    - How to maintain backward compatibility?
    - How to handle template variations?
11. Query Builder Design:
    - How to make it extensible for future query types?
    - How to handle custom query modifications?
    - What level of type safety to enforce?
    - How to handle query optimization?
12. Renderer Service Design:
    - How to handle template variations?
    - How to manage partial rendering?
    - How to handle custom rendering needs?
    - How to optimize rendering performance?

## Dependencies
- WordPress REST API
- Existing bracket templates
- Existing filter functionality
- React (already included in the project)
- Tailwind CSS (already included in the project)
- WordPress nonce authentication
- Existing REST API endpoints
- TypeScript type definitions
- `WpHttpClient` class 