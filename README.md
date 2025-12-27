# Companue Shared Utilities

Shared utilities, traits, and helpers for Companue service packages.

## Features

- **Orderable Trait**: Easy ordering/ranking capabilities for Eloquent models
  - Handles unique constraint conflicts with two-pass approach
  - Built-in scopes for querying ordered data
  - Bulk and single-item reordering support

## Installation

```bash
composer require companue/shared-utilities
```

## Usage

### Orderable Trait

Use the trait in your model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Companue\SharedUtilities\Traits\Orderable;

class Joblevel extends Model
{
    use Orderable;

    public function orderingAttr(): string
    {
        return 'priority'; // Your ordering column
    }
}
```

#### Available Methods

- `ordered()` - Scope to order query results
- `orderedDesc()` - Scope to reverse order
- `getOrderingValue()` - Get the ordering value
- `setOrderingValue($value)` - Set the ordering value
- `reorderBatch(array $items)` - Bulk reorder with unique constraint handling
- `reorderSingle($id, $position)` - Move single item and shift others
- `getNextOrderingValue()` - Get next available position

#### Example

```php
// Get ordered items
$items = Joblevel::ordered()->get();

// Reorder multiple items
Joblevel::reorderBatch([
    ['id' => 1, 'priority' => 1],
    ['id' => 2, 'priority' => 2],
    ['id' => 3, 'priority' => 3],
]);

// Move single item
Joblevel::reorderSingle(5, 2); // Move item 5 to position 2
```

## License

MIT
