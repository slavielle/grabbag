## Why choosing Linux path-like syntax ?

When the path syntax was designed, I was looking for something that looks familiar to developers.
It was basically a choice in between a dot syntax approach (used by many language such as JS) or a linux path-like syntax.

Early in the development phase, came the question of accessing upper elements from an element using a path, as we can access to an upper directory from a directory with linux paths:


```php
$result = Grabbag::grab($subject, '../../my-grandpa-dir/my-grandpa-child-dir/');
```
This point was not quite compatible with dot syntax approach, so linux path-like was chosen.

## Why only one parameter for method path-items ?

[@todo To be answered]