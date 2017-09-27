# ?default-value modifier

Allows to define default value when a path cannot be resolved.

Grabbag avoid PHP exceptions when trying to resolve a path that cannot be resolved and have 
a mechanism allowing to define default values using *?default-value* modifier.



## Source data
```php
[
    (object)[
        'value' => 'My value 1',
    ],
    (object)[
        'no-value' => TRUE,
    ],
    (object)[
        'value' => 'My value 3'
    ],      
]
```

## Default behavior
Query :
```php
$result = Grabbag::grab($subject, [
    '%any/value',
    '?default-value' => $defaultValue['in']
]);

```
Result : 
```php
[
    'My value 1',
    'My value 3',
]

```
No value is produced for unmatching element.
## With *new VoidDefaultValue()* as ?default-value'
Query :
```php
$result = Grabbag::grab($subject, [
    '%any/value',
    '?default-value' => new VoidDefaultValue()
]);

```
Result : 
```php
$result = Grabbag::grab($subject, [
    'My value 1',
    'My value 3',
]);

```
As in default case, no value is produced for unmatching element. those 2 solutions are equivalent.
## With *new NullDefaultValue()* as ?default-value'
Query :
```php
$result = Grabbag::grab($subject, [
    '%any/value',
    '?default-value' => new NullDefaultValue()
]);

```
Result : 
```php
[
    'My value 1',
    NULL,
    'My value 3',
]
```
NULL is produced for unmatching element.
## With a variable as ?default-value'
Query :
```php
$result = Grabbag::grab($subject, [
    '%any/value',
    '?default-value' => 'No Value'
]);

```
Result : 
```php
[
    'My value 1',
    'No Value',
    'My value 3',
]
```
Specified value is produced for unmatching element.


 
