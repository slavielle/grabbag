# ?consider modifier

Allows to test if an item must be kept or not in the result scope using a callback function.

see [example](../../examples/3-json-friends-and-fruits/example-2.php)

Callback function prototype

function($value, $pathId){}

* $value: SerialResolver instance of the element matched.
* $pathId: Id of the path the value had been matched with.

This modifier is invoked only on path having a path id.

