# ?call modifier

## Type
[Path modifier](../path-and-query.md#modifier-types)

## Usage
Call is a way to call a callback function you can use to get path result value. It can then be used perform things using closure for instance.

## example
```php
$result = Grabbag::grab($stuff, [
    "this/is/%any/thing" => [
        "?call" => function($value) use ($myObject){
            $myObject->push($value);
        }
    ]
]);
```

## Parameter

**Parameter type** : Callback
```
function($value, $pathId, $valueAccessor, $allValuesAccessors){}
```
* **$value**(mixed): Path result value.
* **$pathId**(string): Id of the path the value had been matched with.
* **$valueAccessor**(ItemAccessor): Item accessor for the value.
* **$allValuesAccessors**(ItemAccessor[]): All items accessors for values in the result scope keyed by id.
