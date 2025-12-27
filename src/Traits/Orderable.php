<?php

namespace Companue\SharedUtilities\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Orderable Trait
 * 
 * Provides ordering capabilities to Eloquent models.
 * Models using this trait can define custom ordering attributes and scopes.
 * 
 * Usage:
 *   1. Add to model: use Orderable;
 *   2. Define orderingAttr() method returning the column name (e.g., 'priority', 'display_order')
 *   3. Ensure the column exists and has appropriate constraints (unique if needed)
 * 
 * Example:
 *   class Joblevel extends Model {
 *       use Orderable;
 *       
 *       public function orderingAttr(): string {
 *           return 'priority';
 *       }
 *   }
 * 
 * Available methods:
 *   - ordered() scope: Order by the ordering attribute
 *   - orderedDesc() scope: Order in reverse by the ordering attribute
 *   - getOrderingValue(): Get the ordering value for this instance
 *   - setOrderingValue(value): Set the ordering value
 *   - reorderBatch(array): Bulk reorder items with unique constraint handling
 *   - reorderSingle(id, position): Move single item and shift others
 */
trait Orderable
{
    /**
     * The ordering attribute name (column name)
     * 
     * @var string
     */
    protected string $ordering_attr = 'display_order';

    /**
     * Get the ordering attribute name for this model
     * 
     * Override the $ordering_attr property in your model to specify a custom ordering attribute
     * 
     * @return string The name of the ordering column
     */
    public function orderingAttr(): string
    {
        return $this->ordering_attr;
    }

    /**
     * Scope to order query results by the ordering attribute
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy($this->orderingAttr());
    }

    /**
     * Scope to order in reverse (descending) by the ordering attribute
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeOrderedDesc(Builder $query): Builder
    {
        return $query->orderByDesc($this->orderingAttr());
    }

    /**
     * Check if the ordering attribute has a unique constraint
     * 
     * @return bool
     */
    public function hasUniqueOrdering(): bool
    {
        // Check if the table has a unique index on the ordering attribute
        $indexes = \DB::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableIndexes($this->getTable());

        $orderingAttr = $this->orderingAttr();
        
        foreach ($indexes as $index) {
            if ($index->isUnique() && in_array($orderingAttr, $index->getColumns())) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get the next available ordering value
     * 
     * @return int
     */
    public function getNextOrderingValue(): int
    {
        $orderingAttr = $this->orderingAttr();
        $maxValue = $this->max($orderingAttr) ?? 0;
        return $maxValue + 1;
    }

    /**
     * Reorder multiple items, handling unique constraint conflicts
     * 
     * This method uses a two-pass approach to avoid unique constraint violations:
     * 1. First pass: Assign temporary negative values to free up the constraint
     * 2. Second pass: Apply the actual ordering values
     * 
     * @param array $items Array of ['id' => value, 'orderingAttr' => value]
     * @return void
     * @throws \Throwable
     */
    public static function reorderBatch(array $items): void
    {
        $model = new static();
        $orderingAttr = $model->orderingAttr();

        \DB::beginTransaction();
        try {
            // First pass: set all items to temporary negative values
            foreach ($items as $index => $itemData) {
                $id = $itemData['id'] ?? null;
                if ($id) {
                    static::find($id)?->update([
                        $orderingAttr => -($index + 1)
                    ]);
                }
            }

            // Second pass: set actual ordering values
            foreach ($items as $itemData) {
                $id = $itemData['id'] ?? null;
                $orderValue = $itemData[$orderingAttr] ?? null;
                
                if ($id && $orderValue !== null) {
                    static::find($id)?->update([
                        $orderingAttr => $orderValue
                    ]);
                }
            }

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            throw $th;
        }
    }

    /**
     * Reorder a single item, adjusting others as needed
     * 
     * Moves an item to a new position and shifts other items accordingly
     * 
     * @param int $itemId The ID of the item to move
     * @param int $newPosition The new position (1-based)
     * @return void
     */
    public static function reorderSingle(int $itemId, int $newPosition): void
    {
        $model = new static();
        $orderingAttr = $model->orderingAttr();

        \DB::beginTransaction();
        try {
            $item = static::find($itemId);
            if (!$item) {
                return;
            }

            $currentPosition = $item->{$orderingAttr};
            $direction = $newPosition > $currentPosition ? 'down' : 'up';

            if ($direction === 'down') {
                // Shift items down (decrease order)
                static::whereBetween($orderingAttr, [$currentPosition + 1, $newPosition])
                    ->decrement($orderingAttr);
            } else {
                // Shift items up (increase order)
                static::whereBetween($orderingAttr, [$newPosition, $currentPosition - 1])
                    ->increment($orderingAttr);
            }

            // Set the item to its new position
            $item->update([$orderingAttr => $newPosition]);

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            throw $th;
        }
    }

    /**
     * Get the ordering value for this model instance
     * 
     * @return mixed
     */
    public function getOrderingValue()
    {
        return $this->{$this->orderingAttr()};
    }

    /**
     * Set the ordering value for this model instance
     * 
     * @param mixed $value
     * @return $this
     */
    public function setOrderingValue($value)
    {
        $this->{$this->orderingAttr()} = $value;
        return $this;
    }
}
