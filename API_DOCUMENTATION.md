# API Documentation - WordPress vs Rails Migration

This document provides comprehensive API documentation for both the former WordPress plugin system and the new Rails application.

## Table of Contents
- [WordPress Plugin API (Legacy)](#wordpress-plugin-api-legacy)
- [Rails API (Current)](#rails-api-current)
- [Migration Mapping](#migration-mapping)
- [Breaking Changes](#breaking-changes)
- [Authentication](#authentication)

---

## WordPress Plugin API (Legacy)

### Base URL
```
https://your-wordpress-site.com/wp-json/
```

### API Namespaces

#### Main Plugin API: `wp-bracket-builder/v1`
Core bracket and tournament functionality

#### Mobile/Push API: `bmb/v1`  
Mobile app specific features and push notifications

---

### WordPress Endpoints

#### 🏆 Brackets API
**Namespace:** `wp-bracket-builder/v1`

| Method | Endpoint | Description | Permission |
|--------|----------|-------------|------------|
| `GET` | `/wp-json/wp-bracket-builder/v1/brackets` | List all brackets | Customer |
| `POST` | `/wp-json/wp-bracket-builder/v1/brackets` | Create new bracket | Create permission |
| `GET` | `/wp-json/wp-bracket-builder/v1/brackets/{id}` | Get specific bracket | Customer |
| `PUT` | `/wp-json/wp-bracket-builder/v1/brackets/{id}` | Update bracket | Customer |
| `DELETE` | `/wp-json/wp-bracket-builder/v1/brackets/{id}` | Delete bracket | Customer |

**Example Response:**
```json
{
  "id": 123,
  "title": "March Madness 2024",
  "slug": "march-madness-2024", 
  "num_teams": 64,
  "is_voting": false,
  "fee": null,
  "matches": [...],
  "results": [...]
}
```

#### 🎮 Bracket Plays API
**Namespace:** `wp-bracket-builder/v1`

| Method | Endpoint | Description | Permission |
|--------|----------|-------------|------------|
| `GET` | `/wp-json/wp-bracket-builder/v1/plays` | List plays (with bracket_id filter) | Customer |
| `POST` | `/wp-json/wp-bracket-builder/v1/plays` | Create/submit bracket play | Customer |
| `GET` | `/wp-json/wp-bracket-builder/v1/plays/{id}` | Get specific play | Customer |
| `PUT` | `/wp-json/wp-bracket-builder/v1/plays/{id}` | Update play | Customer |
| `DELETE` | `/wp-json/wp-bracket-builder/v1/plays/{id}` | Delete play | Customer |
| `POST` | `/wp-json/wp-bracket-builder/v1/plays/{id}/generate-images` | Generate bracket images | Customer |

**Query Parameters for GET /plays:**
- `bracket_id` (integer): Filter plays by bracket ID

#### 💳 Stripe Payments API
**Namespace:** `wp-bracket-builder/v1`

| Method | Endpoint | Description | Permission |
|--------|----------|-------------|------------|
| `POST` | `/wp-json/wp-bracket-builder/v1/stripe/webhook` | Stripe webhook handler | None (webhook) |
| `POST` | `/wp-json/wp-bracket-builder/v1/stripe/payment-intent` | Create payment intent | Customer |
| `POST` | `/wp-json/wp-bracket-builder/v1/stripe/onboarding-link` | Get onboarding link | Customer |
| `POST` | `/wp-json/wp-bracket-builder/v1/stripe/payments-link` | Create payment link | Customer |
| `GET` | `/wp-json/wp-bracket-builder/v1/stripe/account` | Get Stripe account info | Customer |

#### 🔔 Notifications API  
**Namespace:** `wp-bracket-builder/v1`

| Method | Endpoint | Description | Permission |
|--------|----------|-------------|------------|
| `GET` | `/wp-json/wp-bracket-builder/v1/notifications` | List notifications | Customer |
| `POST` | `/wp-json/wp-bracket-builder/v1/notifications` | Create notification | Create permission |
| `GET` | `/wp-json/wp-bracket-builder/v1/notifications/{id}` | Get specific notification | Customer |
| `PUT` | `/wp-json/wp-bracket-builder/v1/notifications/{id}` | Update notification | Customer |
| `DELETE` | `/wp-json/wp-bracket-builder/v1/notifications/{id}` | Delete notification | Customer |
| `POST` | `/wp-json/wp-bracket-builder/v1/notifications/{id}/read` | Mark notification as read | Customer |
| `POST` | `/wp-json/wp-bracket-builder/v1/notifications/read-all` | Mark all notifications as read | Customer |

#### 📧 Notification Subscriptions API
**Namespace:** `wp-bracket-builder/v1`

| Method | Endpoint | Description | Permission |
|--------|----------|-------------|------------|
| `GET` | `/wp-json/wp-bracket-builder/v1/notification-subscriptions` | List subscriptions | Customer |
| `POST` | `/wp-json/wp-bracket-builder/v1/notification-subscriptions` | Create subscription | Create permission |
| `GET` | `/wp-json/wp-bracket-builder/v1/notification-subscriptions/{id}` | Get specific subscription | Customer |
| `PUT` | `/wp-json/wp-bracket-builder/v1/notification-subscriptions/{id}` | Update subscription | Customer |
| `DELETE` | `/wp-json/wp-bracket-builder/v1/notification-subscriptions/{id}` | Delete subscription | Customer |

#### 🗳️ Voting Brackets API
**Namespace:** `wp-bracket-builder/v1` 

| Method | Endpoint | Description | Permission |
|--------|----------|-------------|------------|
| `POST` | `/wp-json/wp-bracket-builder/v1/brackets/{id}/vote` | Submit vote for voting bracket | Customer |

#### 📱 Push Notifications API (Mobile)
**Namespace:** `bmb/v1`

| Method | Endpoint | Description | Permission |
|--------|----------|-------------|------------|
| `POST` | `/wp-json/bmb/v1/fcm/token/sync` | Sync FCM token for push notifications | Customer |
| `DELETE` | `/wp-json/bmb/v1/fcm/token` | Delete FCM token | Customer |

**FCM Token Sync Body:**
```json
{
  "token": "firebase-fcm-token",
  "device_id": "unique-device-id",
  "platform": "ios|android",
  "user_id": 123
}
```

#### 🌐 HTML Fragments API
**Namespace:** `wp-bracket-builder/v1`

| Method | Endpoint | Description | Permission |
|--------|----------|-------------|------------|
| `GET` | `/wp-json/wp-bracket-builder/v1/html-fragments` | Get HTML fragments for bracket display | Customer |

---

## Rails API (Current)

### Base URL
```
http://localhost:3000/api/v1/
```

### Authentication
- **Web Pages:** Session-based with CSRF protection
- **API Endpoints:** CSRF protection disabled for JSON requests
- **Future:** JWT or API token authentication planned

---

### Rails Endpoints

#### 🏆 Brackets API

| Method | Endpoint | Description | Response |
|--------|----------|-------------|----------|
| `GET` | `/api/v1/brackets` | List all brackets | Array of brackets |
| `POST` | `/api/v1/brackets` | Create new bracket | Created bracket |  
| `GET` | `/api/v1/brackets/{slug}` | Get bracket by slug | Bracket with matches & results |
| `PUT` | `/api/v1/brackets/{slug}` | Update bracket | Updated bracket |
| `DELETE` | `/api/v1/brackets/{slug}` | Delete bracket | 204 No Content |

**Query Parameters for GET /brackets:**
- `year` (string): Filter by year
- `month` (string): Filter by month  
- `voting` (boolean): Filter by voting type (`true`/`false`)

**Example Response:**
```json
{
  "brackets": [
    {
      "id": 1,
      "title": "March Madness 2024",
      "slug": "march-madness-2024",
      "month": "March", 
      "year": "2024",
      "num_teams": 32,
      "num_plays": 0,
      "fee": null,
      "is_voting": false,
      "live_round_index": 0,
      "created_at": "2025-09-04T23:11:07.136Z",
      "updated_at": "2025-09-04T23:11:07.136Z"
    }
  ],
  "meta": {}
}
```

**Detailed Bracket Response (GET /brackets/{slug}):**
```json
{
  "bracket": {
    "id": 1,
    "title": "March Madness 2024",
    "slug": "march-madness-2024",
    "round_names": ["Round 1", "Round 2", "Finals"],
    "matches": [
      {
        "id": 1,
        "round": 1,
        "position": 1,
        "team1": {"id": 1, "name": "Duke", "seed": 1},
        "team2": {"id": 2, "name": "UNC", "seed": 8},
        "winner": null,
        "match_date": null,
        "completed": false
      }
    ],
    "results": [
      {
        "id": 1,
        "round": 1,
        "position": 1,
        "team": {"id": 1, "name": "Duke", "seed": 1},
        "is_result": true
      }
    ]
  }
}
```

#### 🎮 Plays API (User Bracket Submissions)

| Method | Endpoint | Description | Response |
|--------|----------|-------------|----------|
| `GET` | `/api/v1/brackets/{bracket_id}/plays` | List plays for bracket | Array of plays |
| `POST` | `/api/v1/brackets/{bracket_id}/plays` | Submit new play | Created play |
| `GET` | `/api/v1/brackets/{bracket_id}/plays/{id}` | Get specific play | Play with picks |
| `PUT` | `/api/v1/brackets/{bracket_id}/plays/{id}` | Update play | Updated play |
| `DELETE` | `/api/v1/brackets/{bracket_id}/plays/{id}` | Delete play | 204 No Content |

**Example Play Response:**
```json
{
  "play": {
    "id": 1,
    "user": {
      "id": 1,
      "username": "johndoe",
      "display_name": "John Doe"
    },
    "score": 85,
    "completed_at": "2024-03-15T10:30:00Z",
    "is_paid": false,
    "picks_data": {
      "round_1": [1, 2, 1, 2],
      "round_2": [1, 2],
      "finals": [1]
    },
    "picks": [
      {
        "id": 1,
        "round": 1,
        "position": 1,
        "team": {"id": 1, "name": "Duke", "seed": 1}
      }
    ]
  }
}
```

#### 👥 Teams API

| Method | Endpoint | Description | Response |
|--------|----------|-------------|----------|
| `GET` | `/api/v1/teams` | List all teams | Array of teams |
| `POST` | `/api/v1/teams` | Create new team | Created team |
| `GET` | `/api/v1/teams/{id}` | Get specific team | Team details |
| `PUT` | `/api/v1/teams/{id}` | Update team | Updated team |
| `DELETE` | `/api/v1/teams/{id}` | Delete team | 204 No Content |

**Query Parameters for GET /teams:**
- `seeded` (boolean): Filter by seeded teams (`true`/`false`)

**Example Response:**
```json
{
  "teams": [
    {
      "id": 1,
      "name": "Duke",
      "seed": 1,
      "display_name": "1. Duke",
      "logo_url": null,
      "color": null,
      "seeded": true
    }
  ]
}
```

#### 👤 Users API

| Method | Endpoint | Description | Response |
|--------|----------|-------------|----------|
| `GET` | `/api/v1/users` | List users | Array of users |
| `POST` | `/api/v1/users` | Create user | Created user |
| `GET` | `/api/v1/users/{id}` | Get user details | User details |
| `PUT` | `/api/v1/users/{id}` | Update user | Updated user |

---

### Static Pages (Rails Web Interface)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/` | Home page with React bracket builder |
| `GET` | `/about` | About page |
| `GET` | `/privacy-policy` | Privacy policy page |

---

## Migration Mapping

### Endpoint Migration Table

| WordPress Endpoint | Rails Endpoint | Status | Notes |
|-------------------|----------------|---------|--------|
| `wp-bracket-builder/v1/brackets` | `api/v1/brackets` | ✅ Migrated | Uses slug instead of ID |
| `wp-bracket-builder/v1/plays` | `api/v1/brackets/{id}/plays` | ✅ Migrated | Nested under brackets |
| `wp-bracket-builder/v1/notifications` | Not implemented | ❌ Pending | Future feature |
| `wp-bracket-builder/v1/notification-subscriptions` | Not implemented | ❌ Pending | Future feature |
| `wp-bracket-builder/v1/stripe/*` | Not implemented | ❌ Pending | Future feature |
| `bmb/v1/fcm/token/*` | Not implemented | ❌ Pending | Mobile app feature |
| `wp-bracket-builder/v1/html-fragments` | Static pages | ✅ Replaced | Now server-rendered HTML |

### Data Structure Changes

#### Brackets
- **WordPress:** Uses numeric IDs in URLs
- **Rails:** Uses slugs in URLs (SEO-friendly)
- **WordPress:** Custom serialization format
- **Rails:** Standard REST JSON format with consistent structure

#### Plays  
- **WordPress:** Flat structure with `bracket_id` filter
- **Rails:** Nested under brackets (`/brackets/{id}/plays`)
- **WordPress:** Complex custom data structure
- **Rails:** Simplified structure with JSON serialization

#### Users
- **WordPress:** WordPress user system integration
- **Rails:** Custom user model with secure authentication

---

## Breaking Changes

### ⚠️ URL Structure Changes

#### Bracket Access
```diff
- GET /wp-json/wp-bracket-builder/v1/brackets/123
+ GET /api/v1/brackets/march-madness-2024
```

#### Plays Access
```diff
- GET /wp-json/wp-bracket-builder/v1/plays?bracket_id=123
+ GET /api/v1/brackets/march-madness-2024/plays
```

### ⚠️ Authentication Changes
```diff
- WordPress user authentication & nonces
+ Rails session-based auth (future: JWT tokens)
```

### ⚠️ Response Format Changes

#### Error Responses
```diff
# WordPress
{
  "code": "rest_bracket_invalid",
  "message": "Invalid bracket ID",
  "data": {"status": 404}
}

# Rails
{
  "error": "Record not found"
}
```

#### Success Response Structure
```diff
# WordPress - Direct array or object
[{...bracket_data...}]

# Rails - Wrapped with metadata
{
  "brackets": [...],
  "meta": {}
}
```

### ⚠️ Field Name Changes
```diff
# WordPress
{
  "item_id": 123,
  "post_title": "Bracket Name"
}

# Rails  
{
  "id": 1,
  "title": "Bracket Name",
  "slug": "bracket-name"
}
```

---

## Authentication

### WordPress (Legacy)
- **Method:** WordPress nonce system + cookies
- **Headers:** `X-WP-Nonce: abc123`
- **Permissions:** WordPress capability system
- **User Context:** WordPress user object

### Rails (Current)
- **Method:** Session-based with CSRF tokens
- **CSRF:** Disabled for JSON API requests
- **Headers:** `Content-Type: application/json`
- **Future:** JWT or API token authentication

### Migration Authentication Strategy
```javascript
// Old WordPress API call
fetch('/wp-json/wp-bracket-builder/v1/brackets', {
  headers: {
    'X-WP-Nonce': wpApiSettings.nonce,
    'Content-Type': 'application/json'
  }
})

// New Rails API call  
fetch('/api/v1/brackets.json', {
  headers: {
    'Content-Type': 'application/json'
  }
})
```

---

## API Testing Examples

### WordPress (Legacy)
```bash
# Get brackets
curl -H "X-WP-Nonce: abc123" \
     https://site.com/wp-json/wp-bracket-builder/v1/brackets

# Create play
curl -X POST \
     -H "X-WP-Nonce: abc123" \
     -H "Content-Type: application/json" \
     -d '{"bracket_id":123,"picks":[...]}' \
     https://site.com/wp-json/wp-bracket-builder/v1/plays
```

### Rails (Current)
```bash
# Get brackets
curl -H "Accept: application/json" \
     http://localhost:3000/api/v1/brackets.json

# Create play
curl -X POST \
     -H "Content-Type: application/json" \
     -d '{"user_id":1,"picks_data":{...}}' \
     http://localhost:3000/api/v1/brackets/march-madness-2024/plays.json
```

---

## Migration Checklist

### ✅ Completed Features
- [x] Brackets CRUD API
- [x] Teams CRUD API  
- [x] Plays CRUD API
- [x] Users API (basic)
- [x] Static page rendering
- [x] Database models & relationships
- [x] API error handling
- [x] Responsive web interface

### ⏳ Pending Features
- [ ] User authentication & authorization
- [ ] Notification system
- [ ] Push notification integration
- [ ] Stripe payment processing
- [ ] Image generation for brackets
- [ ] Voting bracket functionality
- [ ] Email notifications
- [ ] Mobile app API compatibility
- [ ] Webhook system
- [ ] Advanced filtering & search

### 🔄 React App Integration
- [ ] Update API calls from WordPress to Rails endpoints
- [ ] Handle new response formats
- [ ] Update authentication flow
- [ ] Test all bracket functionality
- [ ] Update error handling

---

## Conclusion

The Rails API provides a cleaner, more RESTful interface compared to the WordPress plugin system. Key improvements include:

- **Better URL Structure:** SEO-friendly slugs vs numeric IDs
- **Consistent Response Format:** Standardized JSON structure
- **Nested Resources:** Logical relationship modeling
- **Modern Authentication:** Session-based with future JWT support
- **Performance:** Native Ruby/Rails performance vs PHP/WordPress
- **Maintainability:** Clean MVC architecture vs WordPress plugin structure

The migration maintains core functionality while providing a foundation for future enhancements and better mobile app integration.