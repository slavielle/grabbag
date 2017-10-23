# ?transform modifier

Allows to transform a result value using a callback function that shall return the callback value.

## Type
[Path modifier](../path-and-query.md#modifier-types)

## example
[See example](../../examples/2-use-key-keyword/example.php)

## Parameter
**Parameter type** : Callback
```
function($value, $pathId, $valueAccessor, $allValuesAccessors){}
```
* **$value**(mixed): Value to transform.
* **$pathId**(string): Id of the path the value had been matched with.
* **$valueAccessor**(ItemAccessor): Item accessor for the value.
* **$allValuesAccessors**(ItemAccessor[]): All items accessors for values in the result scope keyed by id.

## Behaviors

This modifier is invoked only on path having a path id.

Callback function always have to return a value event for untouched values.
If not, the value for this path-id would be replaced by NULL.



