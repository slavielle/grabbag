# ?consider modifier

## Type
[Path modifier](../path-and-query.md#modifier-types)

## Usage
Allows to test if an item must be kept or not in the result scope using a callback function.

## example
see [example](../../examples/3-json-friends-and-fruits/example-2.php)

## Parameter

**Parameter type** : Callback
```
function($value, $pathId, $valueAccessor, $allValuesAccessors){}
```
* **$value**(mixed): Path result value to consider.
* **$pathId**(string): Id of the path the value had been matched with.
* **$valueAccessor**(ItemAccessor): Item accessor for the value.
* **$allValuesAccessors**(ItemAccessor[]): All items accessors for values in the result scope keyed by id.

This modifier is invoked only on path having a path id.

