# Bracket Board Filter Buttons Implementation Plan

## Overview
Implement filter buttons in BracketBoardPage similar to TournamentsPage, using the existing TournamentFilterInterface infrastructure.

## New Files to Create:

1. **`plugin/Includes/Service/TournamentFilter/Public/PublicBracketFilter.php`**
   - Implements `TournamentFilterInterface`
   - Handles filtering public brackets by status (live, upcoming, scored)
   - Uses `BracketQueryBuilder` for querying public brackets

2. **`plugin/Includes/Service/TournamentFilter/Public/PublicBracketsQuery.php`**
   - Simple, focused implementation for public bracket filtering
   - ~30-40 lines vs DashboardTournamentsQuery's 200+ lines
   - Uses existing `BracketQueryBuilder` - no custom SQL needed
   - No user-specific logic or complex joins

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

1. **`plugin/Public/Partials/BracketBoard/BracketBoardPage.php`**
   - Add filter button initialization similar to `TournamentsPage`
   - Replace the current `BracketsCommon::bracket_filter_buttons()` call
   - Add filter data array with live, upcoming, scored statuses
   - Add methods for rendering filter buttons and handling active states

2. **`plugin/Public/Partials/shared/FilterButton.php`**
   - No changes needed - already works with `TournamentFilterInterface`

3. **`plugin/Features/Bracket/Domain/BracketQueryTypes.php`**
   - No changes needed - already has the necessary status mappings

4. **`plugin/Features/Bracket/Infrastructure/BracketQueryBuilder.php`**
   - No changes needed - already supports the required filtering

## Key Implementation Details:

- **Filter Statuses**: Live, Upcoming, Scored (matching current `BracketsCommon::bracket_filter_buttons()`)
- **Colors**: Green for Live, Yellow for Upcoming, White for Scored
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

The implementation will follow the same pattern as `TournamentsPage` but adapted for public brackets, ensuring consistency across the application while reusing the robust filtering infrastructure already in place.
