# #key keyword

Get the key value of an element if it has a key.

## Example

#### source data
```
[
    'fruits' => [
        'orange',
        'banana',
        'apple',
    ],
    'vegetables' => [
        'carrot',
        'tomato',
        'potato'
    ]
];
```
#### Query
```
['#any/#any' => [
    'food:.',
    'food-group:../#key'
]]
```
#### Result
```
[
    [
        'food' => 'orange',
        'food-group' => 'fruits',
    ],
    [
        'food' => 'banana',
        'food-group' => 'fruits',
    ],
    [
        'food' => 'apple',
        'food-group' => 'fruits',
    ],
    [
        'food' => 'carrot',
        'food-group' => 'vegetables',
    ],
    [
        'food' => 'tomato',
        'food-group' => 'vegetables',
    ],
    [
        'food' => 'potato',
        'food-group' => 'vegetables',
    ],
];
```

see [example](../../examples/use_key_keyword)