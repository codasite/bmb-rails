# Bracket Board Filter Buttons Implementation Plan

## Overview
Implement filter buttons in BracketBoardPage similar to TournamentsPage, using the existing TournamentFilterInterface infrastructure.

## Implementation Progress

### ✅ Completed:
1. **`plugin/Includes/Service/TournamentFilter/Public/PublicBracketsQuery.php`**
   - ✅ Created simple, focused implementation for public bracket filtering
   - ✅ ~40 lines vs DashboardTournamentsQuery's 200+ lines
   - ✅ Uses existing `BracketQueryBuilder` - no custom SQL needed
   - ✅ No user-specific logic or complex joins

2. **`plugin/Includes/Service/TournamentFilter/Public/PublicBracketFilter.php`**
   - ✅ Implements `TournamentFilterInterface`
   - ✅ Handles filtering public brackets by status (live, upcoming, in progress, completed)
   - ✅ Uses `BracketQueryBuilder` for querying public brackets

3. **`plugin/Includes/Service/FilterPageService.php`**
   - ✅ Created service-based solution for common filter page functionality
   - ✅ Eliminates duplication between BracketBoardPage and TournamentsPage
   - ✅ Provides reusable filter initialization, active state management, and rendering
   - ✅ **Improved**: Consolidated initialization logic into single `init()` method
   - ✅ **Improved**: Constructor-based configuration for better cohesion

4. **`plugin/Public/Partials/BracketBoard/BracketBoardPage.php`** ✅
   - ✅ Refactored to use FilterPageService
   - ✅ Reduced from ~150 lines to ~80 lines
   - ✅ Removed all duplicated filter logic
   - ✅ Maintains same functionality with cleaner code
   - ✅ **Improved**: Uses consolidated FilterPageService initialization
   - ✅ **Enhanced**: Filter buttons now include jump links to bypass Featured section

5. **`plugin/Public/Partials/dashboard/TournamentsPage.php`** ✅
   - ✅ Refactored to use FilterPageService
   - ✅ Reduced from ~200 lines to ~100 lines
   - ✅ Removed all duplicated filter logic
   - ✅ Maintains role-specific functionality
   - ✅ **Resolved**: URL generation method separation for role vs filter buttons
   - ✅ **Improved**: Uses consolidated FilterPageService initialization

### ✅ Implementation Complete!
- All core functionality implemented and tested
- Service-based refactoring successfully eliminates duplication
- Both pages maintain full functionality with cleaner code

### ⏳ Future Enhancements:
- Integration testing with existing React components
- Performance testing with large datasets
- Documentation updates for developers

## ✅ Resolved: Duplication Issue

### Problem (RESOLVED):
After initial implementation, we had significant duplication between:
- **`BracketBoardPage.php`** - Public bracket filtering
- **`TournamentsPage.php`** - Dashboard tournament filtering

### Solution Implemented: Service-Based Approach ✅
**Chose Option 2** - Created `FilterPageService` that provides common functionality:
- **Filter initialization** with factory pattern
- **Active filter management** 
- **Filter button rendering**
- **URL generation** with callable functions
- **Pagination handling**

### Results:
- **BracketBoardPage**: ~80 lines (vs original ~150 lines) - **47% reduction**
- **TournamentsPage**: ~100 lines (vs original ~200 lines) - **50% reduction**
- **FilterPageService**: ~120 lines of reusable code
- **Total code reduction**: ~170 lines eliminated
- **Maintainability**: Single source of truth for filter logic

### Benefits Achieved:
- **Maximum code reuse** through service composition
- **Flexibility** - each page can customize filter creation and URL generation
- **Consistent behavior** - all filter pages work the same way
- **Easy to extend** - new filter pages just use the service
- **Clean separation** - common logic in service, page-specific logic in pages

## ✅ Resolved: URL Generation Conflict

### Problem (RESOLVED):
The `get_filtered_url` method was originally designed for both:
- **Role buttons** (hosting/playing) - needs `role` and `status` parameters
- **Filter buttons** (live/upcoming/etc) - only needs `status` parameter

When refactored for the filter service, it now only takes `status` and uses instance `$this->role`, breaking the role button functionality.

### ✅ Solution Implemented: Separate URL Generation Methods
Created two distinct methods to handle different URL generation needs:

```php
class TournamentsPage {
  // For filter service (status-only URLs)
  public function get_filtered_url(string $status): string {
    return add_query_arg(
      ['role' => $this->role, 'status' => $status],
      get_permalink() . 'tournaments'
    );
  }
  
  // For role buttons (role + status URLs)
  public function get_role_filtered_url(string $role, string $status): string {
    return add_query_arg(
      ['role' => $role, 'status' => $status],
      get_permalink() . 'tournaments'
    );
  }
}
```

**Benefits Achieved**:
- ✅ **Maintains filter service integration** - `get_filtered_url` works for filter buttons
- ✅ **Preserves role button functionality** - `get_role_filtered_url` works for hosting/playing buttons
- ✅ **Clear separation of concerns** - each method has a single responsibility
- ✅ **Minimal code duplication** - both methods use similar logic but different parameters
- ✅ **Easy to understand** - method names clearly indicate their purpose
- ✅ **Correct URL structure** - uses `/dashboard/tournaments?role=X&status=Y` format with "tournaments" as part of the path

## New Files Created:

1. **`plugin/Includes/Service/TournamentFilter/Public/PublicBracketFilter.php`**
   - Implements `TournamentFilterInterface`
   - Handles filtering public brackets by status (live, upcoming, in progress, completed)
   - Uses `BracketQueryBuilder` for querying public brackets

2. **`plugin/Includes/Service/TournamentFilter/Public/PublicBracketsQuery.php`**
   - Simple, focused implementation for public bracket filtering
   - ~30-40 lines vs DashboardTournamentsQuery's 200+ lines
   - Uses existing `BracketQueryBuilder` - no custom SQL needed
   - No user-specific logic or complex joins

3. **`plugin/Includes/Service/FilterPageService.php`**
   - Service-based solution for common filter page functionality
   - Eliminates duplication between filter pages
   - Provides reusable filter initialization, active state management, and rendering
   - ✅ **Improved**: Consolidated initialization logic into single `init()` method
   - ✅ **Improved**: Constructor-based configuration for better cohesion

## Implementation Strategy:

### Why Not Reuse DashboardTournamentsQuery:
- **Too closely coupled** to dashboard-specific needs
- **Role-based logic** (hosting vs playing) not needed for public brackets
- **User-specific queries** with complex SQL joins
- **Dashboard-specific status mapping** includes private brackets
- **Different domains** - dashboard vs public have different requirements

### Benefits of Separate Implementation:
- **Cleaner, focused code** - single responsibility principle
- **Easier to understand and maintain** - no complex abstractions
- **Better performance** - simpler queries for public brackets
- **Future flexibility** - can evolve independently
- **Leverages existing infrastructure** - uses BracketQueryBuilder

## Changes to Existing Files:

1. **`plugin/Public/Partials/BracketBoard/BracketBoardPage.php`** ✅
   - Refactored to use FilterPageService
   - Removed duplicated filter logic
   - Maintains same functionality with cleaner code
   - ✅ **Improved**: Uses consolidated FilterPageService initialization
   - ✅ **Enhanced**: Filter buttons now include jump links to bypass Featured section

2. **`plugin/Public/Partials/dashboard/TournamentsPage.php`** ✅
   - Refactored to use FilterPageService
   - Removed duplicated filter logic
   - Maintains role-specific functionality
   - ✅ **Resolved**: URL generation method separation for role vs filter buttons
   - ✅ **Improved**: Uses consolidated FilterPageService initialization

3. **`plugin/Public/Partials/shared/FilterButton.php`**
   - No changes needed - already works with `TournamentFilterInterface`

4. **`plugin/Features/Bracket/Domain/BracketQueryTypes.php`**
   - No changes needed - already has the necessary status mappings

5. **`plugin/Features/Bracket/Infrastructure/BracketQueryBuilder.php`**
   - No changes needed - already supports the required filtering

## Key Implementation Details:

- **Filter Statuses**: Live, Upcoming, In Progress, Completed (matching current `BracketsCommon::bracket_filter_buttons()`)
- **Colors**: Green for Live, Yellow for Upcoming, Blue for In Progress, White for Completed
- **Active State Logic**: Similar to `TournamentsPage` - show first filter with results, or queried filter if it has results
- **URL Structure**: Use existing query parameter structure with `status` parameter
- **Integration**: Leverage existing `BracketQueryBuilder` and `BracketQueryTypes` for consistency

## Benefits:

- Consistent UI/UX with TournamentsPage
- Reuses existing filter infrastructure
- Maintains current functionality while adding enhanced filtering
- Leverages existing query building and status mapping logic
- **Simple, focused implementation** - easier to understand and maintain
- **Better performance** - optimized queries for public bracket needs
- **Future flexibility** - can evolve independently of dashboard needs
- **Eliminated duplication** - ~170 lines of code removed
- **Service-based architecture** - clean separation of concerns

## Implementation Summary

The implementation follows the same pattern as `TournamentsPage` but adapted for public brackets, ensuring consistency across the application while reusing the robust filtering infrastructure already in place. 

**Key Decision**: Chose the service-based approach (Option 2) over abstract base class or traits for maximum flexibility and clean separation of concerns.

**Result**: Successfully eliminated ~170 lines of duplicated code while maintaining all functionality and improving maintainability through the `FilterPageService`.

**✅ All Issues Resolved**: 
- URL generation method separation successfully implemented to handle both role buttons (hosting/playing) and filter buttons (live/upcoming/etc) without breaking either functionality
- **Correct URL structure** - uses `/dashboard/tournaments?role=X&status=Y` format with "tournaments" as part of the path
- Service-based refactoring eliminates code duplication while maintaining flexibility
- Both pages maintain full functionality with cleaner, more maintainable code

## ✅ Improved: FilterPageService Consolidation

### Problem (RESOLVED):
The `FilterPageService` had multiple initialization steps that were called separately:
- `init()` - Get query variables
- `init_filters()` - Create filters and buttons
- `set_active_filter()` - Set active filter

This required multiple method calls and made the service harder to use.

### ✅ Solution Implemented: Single Initialization Method
Consolidated all initialization logic into a single `init()` method and moved configuration to the constructor:

```php
// Before: Multiple method calls
$filter_service = new FilterPageService();
$filter_service->init_filters($filter_data, $factory, $url_generator);
$filter_service->set_active_filter();

// After: Single method call with constructor configuration
$filter_service = new FilterPageService($filter_data, $factory, $url_generator);
$filter_service->init();
```

**Benefits Achieved**:
- ✅ **Simplified usage** - Single `init()` method handles everything
- ✅ **Better cohesion** - Configuration in constructor, logic in init
- ✅ **Reduced complexity** - No need to remember multiple method calls
- ✅ **Cleaner code** - Less boilerplate in consuming classes
- ✅ **Private helper methods** - `set_active_filter()` is now private since it's called internally

## ✅ Fixed: Status Mapping Consolidation

### Problem (RESOLVED):
There were two separate and inconsistent mappings for bracket status filters:
- **`DashboardTournamentsQuery::$paged_status_mapping`** - Used WordPress post statuses directly
- **`BracketQueryTypes::getStatusQuery()`** - Used bracket status constants

This caused filter buttons not to work properly due to mismatched status mappings.

### ✅ Solution Implemented: Unified Status Mapping with Constants
Consolidated all status mappings to use `BracketQueryTypes::getStatusQuery()` and replaced all hardcoded strings with constants:

**Before (Inconsistent with hardcoded strings)**:
```php
// DashboardTournamentsQuery
'live' => ['publish', 'score'],
'complete' => ['complete'],

// BracketQueryTypes  
'live' => ['publish'],
'completed' => ['complete'],
```

**After (Unified with constants)**:
```php
// All queries now use BracketQueryTypes::getStatusQuery() with constants
BracketQueryTypes::FILTER_LIVE => [BracketQueryTypes::STATUS_PUBLISH],
BracketQueryTypes::FILTER_IN_PROGRESS => [BracketQueryTypes::STATUS_SCORE],
BracketQueryTypes::FILTER_COMPLETED => [BracketQueryTypes::STATUS_COMPLETE],
```

**Files Updated**:
- ✅ **`BracketQueryTypes.php`** - Added constants for all statuses, filters, and roles
- ✅ **`DashboardTournamentsQuery.php`** - Removed duplicate mapping and roles, now uses `BracketQueryTypes` methods
- ✅ **`PublicBracketsQuery.php`** - Now uses `BracketQueryTypes::getValidFilters()`
- ✅ **`TournamentsPage.php`** - Updated to use constants throughout
- ✅ **`BracketBoardPage.php`** - Already using constants correctly

**Benefits Achieved**:
- ✅ **Consistent behavior** - All filter pages use the same status mapping
- ✅ **Single source of truth** - `BracketQueryTypes` is the authoritative mapping
- ✅ **Type safety** - Constants prevent typos and make refactoring easier
- ✅ **Fixed filter buttons** - Buttons now work correctly with proper status queries
- ✅ **Easier maintenance** - Only one place to update status mappings
- ✅ **Better IDE support** - Autocomplete and refactoring work with constants

## ✅ Refactored: Eliminated Duplication in BracketQueryTypes

### Problem (RESOLVED):
The `BracketQueryTypes` class had duplication between:
- **`getStatusQuery()`** - Used a match statement with hardcoded filter mappings
- **`getValidFilters()`** - Manually listed all filter constants
- **Validation logic** - Scattered across multiple classes

### ✅ Solution Implemented: Single Mapping Array
Refactored to use a single private mapping array as the source of truth:

```php
// Before: Duplicated filter lists
public static function getStatusQuery(string $filter): array {
  return match ($filter) {
    self::FILTER_LIVE => [self::STATUS_PUBLISH],
    // ... more mappings
  };
}

public static function getValidFilters(): array {
  return [
    self::FILTER_LIVE,
    self::FILTER_UPCOMING,
    // ... manually listed
  ];
}

// After: Single source of truth
private static array $filter_status_mapping = [
  self::FILTER_LIVE => [self::STATUS_PUBLISH],
  // ... all mappings in one place
];

public static function getStatusQuery(string $filter): array {
  return self::$filter_status_mapping[$filter] ?? [self::STATUS_PUBLISH];
}

public static function getValidFilters(): array {
  return array_keys(self::$filter_status_mapping);
}
```

**Benefits Achieved**:
- ✅ **No duplication** - Single mapping array is the source of truth
- ✅ **Automatic validation** - `getValidFilters()` derived from mapping keys
- ✅ **Consistent behavior** - All methods use the same mapping
- ✅ **Helper methods** - Added `isValidFilter()` and `isValidRole()` for cleaner validation
- ✅ **Easier maintenance** - Add a filter to the mapping, it's automatically valid
- ✅ **Better performance** - No need to scan arrays for validation

## ✅ Fixed: BracketQueryBuilder TypeError

### Problem (RESOLVED):
After refactoring, `BracketQueryBuilder::buildPublicBracketsQuery()` was calling `BracketQueryTypes::getStatusQuery()` with an array instead of a string, causing a TypeError.

### Root Cause:
The flow was:
1. `PublicBracketsQuery` calls `BracketQueryTypes::getStatusQuery($status)` → returns array
2. Passes that array to `buildPublicBracketsQuery(['status' => $statuses])`
3. `BracketQueryBuilder` calls `BracketQueryTypes::getStatusQuery($status_filter)` again on the array

### ✅ Solution Implemented: Direct Status Array
Updated `BracketQueryBuilder` to expect the status array directly:

```php
// Before: Double conversion
$status_filter = $opts['status'] ?? BracketQueryTypes::FILTER_LIVE;
'post_status' => BracketQueryTypes::getStatusQuery($status_filter),

// After: Direct array usage
$status_array = $opts['status'] ?? [BracketQueryTypes::STATUS_PUBLISH];
'post_status' => $status_array,
```

**Benefits Achieved**:
- ✅ **Fixed TypeError** - No more array-to-string conversion error
- ✅ **Cleaner flow** - Status conversion happens once in the query classes
- ✅ **Better separation** - QueryBuilder focuses on WP_Query args, not status mapping
- ✅ **Consistent behavior** - All query methods work correctly