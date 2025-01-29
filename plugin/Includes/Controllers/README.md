# REST API Controllers

This directory contains base classes and traits for implementing REST API endpoints in the WordPress Bracket Builder plugin.

## RestApiBase

`RestApiBase` is an abstract base class that provides common functionality for REST API endpoints. It handles route registration, serialization, and basic CRUD operations.

### Basic Usage

To create a new API endpoint:

1. Create a class that extends `RestApiBase`
2. Use the appropriate traits for the operations you want to support
3. Implement required methods and override optional ones for customization

```php
class MyApi extends RestApiBase {
    use RestGetCollectionTrait;
    use RestDeleteItemTrait;

    protected $rest_base = 'my-endpoint';

    public function __construct() {
        parent::__construct([
            'rest_base' => $this->rest_base,
            'serializer' => new MySerializer(),
            'repository' => new MyRepository(),
        ]);
    }
}
```

### Available Traits

- `RestGetCollectionTrait`: GET /my-endpoint - List all items
- `RestGetItemTrait`: GET /my-endpoint/{id} - Get a single item
- `RestCreateItemTrait`: POST /my-endpoint - Create a new item
- `RestUpdateItemTrait`: PUT/PATCH /my-endpoint/{id} - Update an item
- `RestDeleteItemTrait`: DELETE /my-endpoint/{id} - Delete an item

### Customizing Behavior

#### Collection Endpoints

Override these methods to customize collection behavior:

```php
// Customize query filters for collection endpoints
protected function get_collection_filters(
    int $page,
    int $per_page,
    string $search
): array {
    return [
        'user_id' => get_current_user_id(),  // Filter by current user
        'orderby' => 'created_at',           // Custom sorting
        'order' => 'DESC',
    ];
}

// Custom permission check for collection endpoints
public function get_items_permissions_check($request): bool {
    return current_user_can('read_items');
}
```

#### Single Item Endpoints

Override these methods to customize single item behavior:

```php
// Customize query filters for single item endpoints
protected function get_single_item_filters(int $id): array {
    return array_merge(parent::get_single_item_filters($id), [
        'user_id' => get_current_user_id(),  // Scope to current user
    ]);
}

// Custom permission check for delete operations
public function delete_item_permissions_check($request): bool|WP_Error {
    // Return WP_Error with 404 to hide existence of items user can't access
    if (!$this->user_can_access($request['id'])) {
        return new WP_Error(
            'rest_not_found',
            __('Not found.'),
            ['status' => 404]
        );
    }
    return true;
}
```

### Security Considerations

1. Always implement permission checks
2. Use 404 Not Found (instead of 403 Forbidden) when you don't want to leak information about resource existence
3. Scope queries to the current user when appropriate
4. Validate and sanitize input data

### Response Format

The base class handles response formatting through serializers:

1. Collection endpoints return an array of serialized items
2. Single item endpoints return a single serialized item
3. Delete endpoints return a response with `deleted: true` and the previous item state

### Error Handling

- Return `WP_Error` objects with appropriate status codes and messages
- Use WordPress's built-in error codes when possible
- Consider security implications when crafting error messages

### Example Implementation

```php
class NotificationApi extends RestApiBase {
    use RestGetCollectionTrait;
    use RestDeleteItemTrait;

    protected $rest_base = 'notifications';

    public function __construct() {
        parent::__construct([
            'rest_base' => $this->rest_base,
            'serializer' => new NotificationSerializer(),
            'repository' => new NotificationRepo(),
        ]);
    }

    protected function get_collection_filters(
        int $page,
        int $per_page,
        string $search
    ): array {
        return [
            'user_id' => get_current_user_id(),
            'orderby' => 'timestamp',
            'order' => 'DESC',
        ];
    }

    public function delete_item_permissions_check($request): bool|WP_Error {
        $id = (int) $request['id'];
        $items = $this->repository->get([
            'id' => $id,
            'user_id' => get_current_user_id(),
            'single' => true,
        ]);

        if (empty($items)) {
            return new WP_Error(
                'rest_notification_not_found',
                __('Not found.'),
                ['status' => 404]
            );
        }

        return true;
    }
}
```
