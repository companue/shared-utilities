# Shared Utilities

## Installation

Install the package via composer:

```bash
composer require companue/shared-utilities
```

## Setup

The package uses Laravel's auto-discovery feature, so no manual registration is needed.

## Features

### Orderable Trait

Provides reusable ordering/ranking capabilities for any Eloquent model.

#### Quick Start

```php
use Companue\SharedUtilities\Traits\Orderable;

class Product extends Model
{
    use Orderable;

    public function orderingAttr(): string
    {
        return 'display_order';
    }
}

// Query ordered items
$products = Product::ordered()->get();

// Reorder items
Product::reorderBatch([
    ['id' => 1, 'display_order' => 1],
    ['id' => 2, 'display_order' => 2],
]);
```

See [README.md](../README.md) for full documentation.
