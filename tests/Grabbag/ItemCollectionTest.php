<?php
/*
// Testable classes
require_once __DIR__ . '/../vendor/autoload.php';

// Test useful classes
require_once 'sourceData/Leaf1.php';
require_once 'sourceData/List1.php';
require_once 'sourceData/SourceDataHelper.php';
require_once 'testData/TestDataHelper.php';
*/

use PHPUnit\Framework\TestCase;
use Grabbag\Grabbag;
use Grabbag\Path;
use Grabbag\PathItem;
use Grabbag\Resolver;
use Grabbag\Item;
use Grabbag\ItemCollection;
use Grabbag\ItemAccessor;
use Grabbag\VoidDefaultValue;
use Grabbag\NullDefaultValue;
use Grabbag\tests\sourceData\SourceDataHelper;
use Grabbag\tests\testData\TestDataHelper;
use Grabbag\exceptions\NotAdressableException;
use Grabbag\exceptions\PathException;
use Grabbag\exceptions\ResolveItemStackEmptyException;
use Grabbag\exceptions\CantApplyConsiderModifierException;


/**
 * @covers Resolver
 */
final class ItemCollectionTest extends TestCase
{

    /**
     * Implement PHPUnit Setup;
     */
    public function setUp()
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            self::error_storage('push', $errno . '-' . $errstr);
        });
    }

    /**
     * Implement PHPUnit tearDown;
     */
    public function tearDown()
    {
        restore_error_handler();
    }

    /**
     * Handle PHP errors, warnings, notices etc ...
     * @param string $op
     * @param null $value
     * @return array
     */
    public static function error_storage($op = 'get', $value = NULL)
    {
        static $errors = [];
        switch ($op) {
            case 'get':
                return $errors;
            case 'push':
                $errors[] = $value;
                break;
            case 'flush':
                $errors = [];
                break;
        }

    }

    /**
     * Test the getValue result when a $resolverItems had not been resolved.
     * The result value must be the input value.
     */
    public function testGetValueWithoutResolving()
    {
        $myInputValue = ['test 1', 'test 2'];
        $myOutputValue = ['test 1', 'test 2'];
        $resolverItems = new ItemCollection($myInputValue);
        $this->assertEquals(
            $myOutputValue, $resolverItems->getValue()
        );
    }

    /**
     * Test default result when requesting with a valid but non-matching path
     * Similar to ResolverTest::testResolveWithValidButNonMatchingPath but use default-value modifier.
     */
    public function testResolveWithValidButNonMatchingPath1()
    {

        $testObject = SourceDataHelper::getDataIndexedL2();

        // Must return provided default value when passing it using a modifier.
        $defaultValueSet = [
            ['in' => NULL, 'out' => NULL],
            ['in' => new NullDefaultValue(), 'out' => NULL],
            ['in' => 'Default String', 'out' => 'Default String'],
            ['in' => 192, 'out' => 192],
            ['in' => ['test' => 'A', 'my' => 2, 'array' => [1, 2, 3]], 'out' => ['test' => 'A', 'my' => 2, 'array' => [1, 2, 3]]],
            ['in' => new VoidDefaultValue(), 'out' => NULL],
            ['in' => new VoidDefaultValue('Default String'), 'out' => 'Default String']
        ];

        foreach ($defaultValueSet as $defaultValue) {
            $resolverItems = new ItemCollection($testObject);
            $resolverItems->resolve([
                'badpath',
                '?default-value' => $defaultValue['in']
            ]);

            $this->assertEquals(
                $defaultValue['out'], $resolverItems->getValue()
            );
        }

        foreach ($defaultValueSet as $defaultValue) {
            $resolverItems = new ItemCollection($testObject);
            $resolverItems->resolve([
                '~myId:badpath',
                '?default-value@~myId' => $defaultValue['in']
            ]);

            $this->assertEquals(
                $defaultValue['out'], $resolverItems->getValue()
            );
        }
    }

    /**
     * Test default result when requesting with a valid but non-matching path
     * using embedded path-array.
     */
    public function testResolveWithValidButNonMatchingPathOnEmbeddedPathArray()
    {

        $testObject = SourceDataHelper::getDataIndexedL2();


        // The provided default value (in) must produce the result (out).
        $defaultValueSet = [

            // All unmatching paths must produce a string (out) when new this string is provided using ?default-value modifier (in).
            [
                'in' => 'Default String',
                'out' => [
                    'I\'m a Leaf2 !',
                    'Default String',
                    'Default String',
                    'Default String',
                    'Default String'
                ]
            ],

            // All unmatching paths must produce an integer (out) when new this integer is provided using ?default-value modifier (in).
            [
                'in' => 192,
                'out' =>
                    [
                        'I\'m a Leaf2 !',
                        192,
                        192,
                        192,
                        192
                    ]
            ],

            // All unmatching paths must produce an array (out) when new this array is provided using ?default-value modifier (in).
            [
                'in' => ['test' => 'A', 'my' => 2, 'array' => [1, 2, 3]],
                'out' => [
                    'I\'m a Leaf2 !',
                    ['test' => 'A', 'my' => 2, 'array' => [1, 2, 3]],
                    ['test' => 'A', 'my' => 2, 'array' => [1, 2, 3]],
                    ['test' => 'A', 'my' => 2, 'array' => [1, 2, 3]],
                    ['test' => 'A', 'my' => 2, 'array' => [1, 2, 3]]
                ]
            ],

            // All unmatching paths must produce NULL (out) when new NullDefaultValue() is provided using ?default-value modifier (in).
            [
                'in' => new NullDefaultValue(),
                'out' => [
                    'I\'m a Leaf2 !',
                    NULL,
                    NULL,
                    NULL,
                    NULL
                ]
            ],

            // All unmatching paths must produce nothing (out) when new VoidDefaultValue() is provided using ?default-value modifier (in).
            [
                'in' => new VoidDefaultValue(),
                'out' => [
                    'I\'m a Leaf2 !'
                ]
            ],

            // All unmatching paths must produce nothing (out) when new NULL is provided using ?default-value modifier (in).
            [
                'in' => NULL,
                'out' => [
                    'I\'m a Leaf2 !'
                ]
            ]
        ];
        foreach ($defaultValueSet as $defaultValue) {
            $resolverItems = new ItemCollection($testObject);
            $resolverItems->resolve([
                'getAllObjects/#3' => [
                    'getAllObjects/%any/myLeaf2Secret',
                    '?default-value' => $defaultValue['in']
                ],
            ]);
            $this->assertEquals(
                $defaultValue['out'], $resolverItems->getValue()
            );

        }
    }

    /**
     * Test if resolving with a non matching path raise a NotAdressableException exception.
     * Similar to ResolverTest::testResolveWithBadPathReturnException but using exception-enabled modifier.
     */
    public function testResolveWithBadPathReturnException()
    {
        $testObject = SourceDataHelper::getDataIndexedL2();
        $resolverItems = new ItemCollection($testObject);
        // Must raise an exception when exception enabled and path not found.
        $expectedException = NULL;
        try {
            $resolverItems->resolve(['badpath', '?exception-enabled']);
        } catch (\Exception $e) {
            $expectedException = $e;
        }

        $this->assertEquals(get_class($expectedException), 'Grabbag\exceptions\NotAdressableException');
        $this->assertEquals($expectedException->getCode(), 4);
        $this->assertEquals($expectedException->getMessage(), 'Can\'t resolve "badpath" on item.');

    }

    /**
     * Test if resolving with a non matching path raise a NotAdressableException exception.
     * Similar to ResolverTest::testResolveWithBadPathReturnException but using exception-enabled modifier.
     */
    public function testResolveWithBadPathReturnException2()
    {
        $testObject = SourceDataHelper::getDataIndexedL1();
        $resolverItems = new ItemCollection($testObject);
        // Must raise an exception when exception enabled and path not found.
        $expectedException = NULL;
        try {
            $resolverItems->resolve(['allObjects/#25', '?exception-enabled']);
        } catch (\Exception $e) {
            $expectedException = $e;
        }

        $this->assertEquals(get_class($expectedException), 'Grabbag\exceptions\NotAdressableException');
        $this->assertEquals($expectedException->getCode(), 5);
        $this->assertEquals($expectedException->getMessage(), 'Can\'t resolve "25" on array.');

    }

    /**
     * Test if resolving with a non matching path raise a NotAdressableException exception.
     * Test ?exception-enabled propagating to upper level
     */
    public function testResolveWithBadPathReturnException3()
    {
        $testObject = SourceDataHelper::getDataIndexedL2();

        $resolverItems = new ItemCollection($testObject);

        // Must raise an exception when exception activated and path not found.
        // Test propagate on upper
        $expectedException = NULL;
        try {
            $resolverItems->resolve([
                'getAllObjects/#3' => [
                    'getAllObjects/%any/myLeaf2Secret'
                ],
                '?exception-enabled'
            ]);
        } catch (\Exception $e) {
            $expectedException = $e;
        }

        $this->assertEquals(get_class($expectedException), 'Grabbag\exceptions\NotAdressableException');
        $this->assertEquals($expectedException->getCode(), 4);
        $this->assertEquals($expectedException->getMessage(), 'Can\'t resolve "myLeaf2Secret" on item.');
    }

    /**
     * Test if resolving with a non matching path do not raise a NotAdressableException exception.
     * Test ?exception-enabled propagating to upper level and can is overriden.
     */
    public function testResolveWithBadPathReturnException4()
    {
        $testObject = SourceDataHelper::getDataIndexedL2();

        $resolverItems = new ItemCollection($testObject);

        $resolverItems->resolve([
            'getAllObjects/#3' => [
                'getAllObjects/%any/myLeaf2Secret',
                '?exception-enabled' => FALSE
            ],
            '?exception-enabled'
        ]);
        $this->assertEquals(["I'm a Leaf2 !"], $resolverItems->getValue());
    }

    /**
     * Test resolving with numerical index values in path.
     * Similar test to ResolverTest::testResolveWithIndex but on a set of Item.
     */
    public function testResolveWithIndex()
    {
        // One level structure test.
        $testObject = SourceDataHelper::getDataIndexedL1();
        $pathVariants = ['getAllObjects/#3/getName', 'allObjects/#3/name', 'objects/#3/myName'];
        foreach ($pathVariants as $pathVariant) {
            $resolverItems = new ItemCollection([
                new Item($testObject),
                new Item($testObject)
            ], FALSE);
            $resolverItems->resolve($pathVariant);
            $this->assertEquals([
                'test 3',
                'test 3'
            ], $resolverItems->getValue());
        }

    }

    /**
     * Test resolving with numerical index (2 levels) values in path.
     * Similar test to ResolverTest::testResolveWithIndexOn2Levels but on a set of Item.
     */
    public function testResolveWithIndexOn2Levels()
    {

        // Two level structure test.
        $testObject = SourceDataHelper::getDataIndexedL2();
        $pathVariants = ['getAllObjects/#3/getAllObjects/#2/getName', 'allObjects/#3/allObjects/#2/name', 'objects/#3/objects/#2/myName'];
        foreach ($pathVariants as $pathVariant) {
            $resolverItems = new ItemCollection([
                new Item($testObject),
                new Item($testObject),
            ], FALSE);
            $resolverItems->resolve($pathVariant);
            $this->assertEquals([
                'test 3.2',
                'test 3.2'
            ], $resolverItems->getValue());
        }
    }

    /**
     * Test resolving with numerical index (2 levels) values in path.
     * Similar test to ResolverTest::testResolveWithIndexOn2Levels but with embedded path-array producing single value.
     */
    public function testResolveWithIndexOn2Levels2()
    {

        // Two level structure test.
        $testObject = SourceDataHelper::getDataIndexedL2();

        $pathVariants = [

            // Using full named getter
            ['getAllObjects/#3' => ['getAllObjects' => ['#2/getName']]],
            ['getAllObjects' => ['#3' => ['getAllObjects' => ['#2' => 'getName']]]],


            // Using short named getter
//            ['AllObjects / 3' => ['AllObjects' => ['2 / getName']]],

            // Using direct object access
//            ['objects / 3' => ['objects' => ['2 / getName']]],
        ];
        foreach ($pathVariants as $pathVariant) {
            $resolverItems = new ItemCollection([
                new Item($testObject),
            ], FALSE);
            $resolverItems->resolve($pathVariant);
            $this->assertEquals('test 3.2', $resolverItems->getValue());
        }
    }

    /**
     * Test resolving with key index values in path.
     * Similar test to ResolverTest::testResolveWithKey but on a set of Item.
     */
    public function testResolveWithKey()
    {
        $testObject = SourceDataHelper::getDataNamedL1();

        $pathVariants = ['getAllObjects/my_value_2/getName', 'allObjects/my_value_2/name', 'objects/my_value_2/myName'];
        foreach ($pathVariants as $pathVariant) {
            $resolverItems = new ItemCollection([
                new Item($testObject),
                new Item($testObject)
            ], FALSE);
            $resolverItems->resolve($pathVariant);
            $this->assertEquals([
                'test my_value_2',
                'test my_value_2'
            ], $resolverItems->getValue());
        }
    }

    /**
     * Test resolving with method + string parameter in path.
     * Similar test to ResolverTest::testResolveWithGetMethodWithStringParameter but on a set of Item.
     */
    public function testResolveWithGetMethodWithStringParameter()
    {
        $testObject = SourceDataHelper::getDataNamedL1();
        $resolverItems = new ItemCollection([
            new Item($testObject),
            new Item($testObject)
        ], FALSE);

        // With string parameter
        $pathVariants = [
            ['path' => 'getOneObject("my_value_2")/myName', 'expected_value' => 'test my_value_2'],
        ];
        foreach ($pathVariants as $pathVariant) {
            $resolverItems->resolve($pathVariant['path']);
            $this->assertEquals([
                $pathVariant['expected_value'],
                $pathVariant['expected_value']
            ], $resolverItems->getValue());
        }
    }

    /**
     * Test resolving with method + int parameter in path.
     * Similar test to ResolverTest::testResolveWithGetMethodWithIntParameter but on a set of Item.
     */
    public function testResolveWithGetMethodWithIntParameter()
    {

        // With Numeric parameter
        $testObject = SourceDataHelper::getDataIndexedL1();
        $resolverItems = new ItemCollection([
            new Item($testObject),
            new Item($testObject)
        ], FALSE);

        $pathVariants = [
            ['path' => 'getOneObject(1)/getName', 'expected_value' => 'test 1'],
            ['path' => 'getOneObject(10)/getName', 'expected_value' => NULL]
        ];
        foreach ($pathVariants as $pathVariant) {
            $resolverItems->resolve($pathVariant['path']);
            $this->assertEquals([
                $pathVariant['expected_value'],
                $pathVariant['expected_value']
            ], $resolverItems->getValue());
        }
    }

    /**
     * Test resolving path with %any keyword
     * Similar test to ResolverTest::testResolverWithAny but on a set of Item.
     */
    public function testResolverWithAny()
    {
        $testObject = SourceDataHelper::getDataIndexedL1();

        $pathList = [
            'getAllObjects/%any/getName',
            'allObjects/%any/getName',
            'objects/%any/getName',
        ];

        foreach ($pathList as $path) {
            $resolverItems = new ItemCollection([
                new Item($testObject),
                new Item($testObject),
            ], FALSE);
            $resolverItems->resolve($path);
            $this->assertEquals([
                ['test 0', 'test 1', 'test 2', 'test 3', 'test 4'],
                ['test 0', 'test 1', 'test 2', 'test 3', 'test 4']
            ], $resolverItems->getValue());
        }
    }

    /**
     * Test resolving query using %any
     */
    public function testResolverQueryWithAny2level()
    {
        $resolverItems = new ItemCollection(SourceDataHelper::getDataNamedL2());

        $resolverItems->resolve([
            'getAllObjects/%any' => [
                'id:myId',
                'name:getName',
                'content:getAllObjects/%any' => [
                    'id:getId',
                    'name:getName'
                ]
            ]
        ]);

        $this->assertEquals(
            TestDataHelper::getTestData1(), $resolverItems->getValue()
        );
    }

    /**
     * Test resolving query using %any
     */
    public function testResolverQueryWithAny2levelB()
    {
        $resolverItems = new ItemCollection(SourceDataHelper::getDataNamedL2());

        $expectedValue = [
            'my_value_1_1',
            'my_value_1_2',
            'my_value_1_3',
            'my_value_1_4',
            'my_value_1_5',
            'my_value_2_1',
            'my_value_2_2',
            'my_value_2_3',
            'my_value_2_4',
            'my_value_2_5',
            'my_value_3_1',
            'my_value_3_2',
            'my_value_3_3',
            'my_value_3_4',
            'my_value_3_5',
        ];

        $resolverItems->resolve('getAllObjects/%any/getAllObjects/%any/%key');


        $this->assertEquals(
            $expectedValue, $resolverItems->getValue()
        );

    }

    /*
    public function testResolverQueryWithAny3Level()
    {
        $testObject = SourceDataHelper::getDataNamedL3();
        $resolverItems = new ItemCollection($testObject);

        $resolverItems->resolve([
            'getAllObjects/%any' => [
                'id:myId',
                'name:getName',
                'content-L2:getAllObjects/%any' => [
                    'id:getId',
                    'name:getName',
                    'content-L3:getAllObjects/%any' => [
                        'id:getId',
                        'name:getName'
                    ]
                ]
            ]
        ]);

        //var_export ($resolverItems->getValue());
    }
    */

    /**
     * Test resolving query using ?transform modifier.
     */
    public function testResolverQueryWithTransformModifier()
    {

        $testObject = SourceDataHelper::getDataNamedL2();

        $expected = ['transformed ~myId-ID#0', 'transformed ~myId-ID#6', 'transformed ~myId-ID#12'];

        // Assertion using non-targeted modifier
        $resolverItems = new ItemCollection($testObject);
        $resolverItems->resolve([
            'getAllObjects/%any' => [
                '~myId:myId',
                '?transform' => function ($value, $id) {
                    return 'transformed ' . $id . '-' . $value;
                },
            ]
        ]);
        $this->assertEquals($expected, $resolverItems->getValue());

        // Assertion using targeted modifier
        $resolverItems = new ItemCollection($testObject);
        $resolverItems->resolve([
            'getAllObjects/%any' => [
                '~myId:myId',
                '?transform@~myId' => function ($value, $id) {
                    return 'transformed ' . $id . '-' . $value;
                },
            ]
        ]);
        $this->assertEquals($expected, $resolverItems->getValue());


    }

    /**
     *  Test resolving query using ?unique modifier.
     */
    public function testResolverQueryUniqueModifier()
    {

        $testObject = SourceDataHelper::getDataNamedL2();
        $resolverItems = new ItemCollection($testObject);

        $expected = ['ID#0', 'ID#6', 'ID#12'];

        $resolverItems->resolve([
            'getAllObjects/%any/getAllObjects/%any/../../myId',
            '?unique',
        ]);

        $this->assertEquals($expected, $resolverItems->getValue());

    }

    /**
     *  Test resolving query using ?unique modifier.
     *  This test produce a different internal case than testResolverQueryUniqueModifier but must produce a equivalent result.
     */
    public function testResolverQueryUniqueModifier2()
    {

        $testObject = SourceDataHelper::getDataNamedL2();
        $resolverItems = new ItemCollection($testObject);

        $expected = ['ID#0', 'ID#6', 'ID#12'];


        $resolverItems->resolve([
            'getAllObjects/%any/getAllObjects/%any/../../myId' => [
                '.',
                '?unique'
            ],
        ]);
        $this->assertEquals($expected, $resolverItems->getValue());

    }

    /**
     *  Test resolving query using ?unique modifier.
     *  This test shall raise a warning thrown as an error by
     */
    public function testResolverQueryUniqueModifier3()
    {
        self::error_storage('flush');
        $testObject = SourceDataHelper::getDataNamedL2();
        $resolverItems = new ItemCollection($testObject);

        $expected = [
            ['ID#0', 'ID#0'], ['ID#0', 'ID#0'], ['ID#0', 'ID#0'], ['ID#0', 'ID#0'], ['ID#0', 'ID#0'],
            ['ID#6', 'ID#6'], ['ID#6', 'ID#6'], ['ID#6', 'ID#6'], ['ID#6', 'ID#6'], ['ID#6', 'ID#6'],
            ['ID#12', 'ID#12'], ['ID#12', 'ID#12'], ['ID#12', 'ID#12'], ['ID#12', 'ID#12'], ['ID#12', 'ID#12']
        ];


        $resolverItems->resolve([
            'getAllObjects/%any/getAllObjects/%any/../../myId' => [
                '.',
                '.',
                '?unique'
            ],
        ]);

        $errors = self::error_storage('get');
        $this->assertEquals(['512-Unable to apply ?unique modifier on this result scope.'], $errors);

        $this->assertEquals($expected, $resolverItems->getValue());

    }

    /**
     *  Test resolving query using ?unique modifier.
     */
    public function testResolverQueryUniqueModifier4()
    {

        self::error_storage('flush');
        $testObject = SourceDataHelper::getDataNamedL2();
        $resolverItems = new ItemCollection($testObject);

        $expected = [
            ['my-id' => 'ID#0'], ['my-id' => 'ID#0'], ['my-id' => 'ID#0'], ['my-id' => 'ID#0'], ['my-id' => 'ID#0'],
            ['my-id' => 'ID#6'], ['my-id' => 'ID#6'], ['my-id' => 'ID#6'], ['my-id' => 'ID#6'], ['my-id' => 'ID#6'],
            ['my-id' => 'ID#12'], ['my-id' => 'ID#12'], ['my-id' => 'ID#12'], ['my-id' => 'ID#12'], ['my-id' => 'ID#12']
        ];

        $resolverItems->resolve([
            'getAllObjects/%any/getAllObjects/%any/../../myId' => [
                'my-id:.',
                '?unique'
            ],
        ]);

        $errors = self::error_storage('get');
        $this->assertEquals(['512-Unable to apply ?unique modifier on this result scope.'], $errors);

        $this->assertEquals($expected, $resolverItems->getValue());


    }

    /**
     * Test resolving with a consider modifier.
     */
    public function testResolverQueryConsiderModifier()
    {

        $testObject = SourceDataHelper::getDataNamedL2();

        $expected = ['ID#0', 'ID#12'];

        // Assertion using non-targeted modifier
        $resolverItems = new ItemCollection($testObject);
        $resolverItems->resolve([
            'getAllObjects/%any/getAllObjects/%any/../../myId' => [
                '~myId:.',
                '?unique',
                '?consider' => function ($item, $id) {
                    if ($id === "~myId") {
                        return $item->get() !== 'ID#6';
                    }
                },
            ],

        ]);
        $this->assertEquals($expected, $resolverItems->getValue());

        // Assertion using targeted modifier
        $resolverItems = new ItemCollection($testObject);
        $resolverItems->resolve([
            'getAllObjects/%any/getAllObjects/%any/../../myId' => [
                '~myId:.',
                '?unique',
                '?consider@~myId' => function ($item) {
                    return $item->get() !== 'ID#6';
                }

            ],
        ]);

        $this->assertEquals($expected, $resolverItems->getValue());
    }


    /**
     * Test consider argument passed to a modifier callback.
     */
    public function testResolverQueryConsiderModifier3()
    {

        $testObject = SourceDataHelper::getDataIndexedL2();

        $expected = [
            [
                'value' => 'ID#1',
                'id' => '~myId'
            ],
            [
                'value' => 'ID#2',
                'id' => '~myId2'
            ],
            [
                'value' => 'ID#3',
                'id' => '~myId3'
            ],
            [
                'value' => 'ID#4',
                'id' => NULL
            ],

        ];

        $collect = [];

        $resolverItems = new ItemCollection($testObject);
        $resolverItems->resolve([
            'getAllObjects/#0' => [
                '~myId:getAllObjects/#0/myId',
                '~myId2:getAllObjects/#1/myId',
                '~myId3:getAllObjects/#2/myId',

                // Case of a path without path-id.
                'getAllObjects/#3/myId',

                // Case of a multi-valued function
                '~myId5:getAllObjects/%any/myId',

                '?call' => function ($value, $id, $itemAccessor, $allItems) use (&$collect) {
                    $collect[] = [
                        'value' => $value,
                        'id' => $id,
                        'itemAccessor' => $itemAccessor,
                        'allItems' => $allItems,
                    ];
                },
            ],

        ]);

        $this->assertEquals(4, count($collect));
        foreach ($collect as $index => $collectedItem) {
            $this->assertEquals($expected[$index]['value'], $collectedItem['value']);
            $this->assertEquals($expected[$index]['id'], $collectedItem['id']);
            $this->assertTrue($collectedItem['itemAccessor'] instanceof ItemAccessor);
            $this->assertEquals(5, count($collectedItem['allItems']));
            $this->assertTrue($collectedItem['allItems']['~myId'] instanceof ItemAccessor);
            $this->assertTrue($collectedItem['allItems']['~myId2'] instanceof ItemAccessor);
            $this->assertTrue($collectedItem['allItems']['~myId3'] instanceof ItemAccessor);
            $this->assertTrue($collectedItem['allItems'][0] instanceof ItemAccessor);
            $this->assertTrue($collectedItem['allItems']['~myId5'] === NULL);
        }

    }

    /**
     * Test resolving with a consider modifier on a path's multi-valued result.
     * This case must throw an CantApplyConsiderModifierException exception.
     * Consider can't be applied on a multi-valued path result.
     */
    public function testResolverQueryConsiderModifier2()
    {

        $testObject = SourceDataHelper::getDataNamedL2();
        $resolverItems = new ItemCollection($testObject);
        $expectedException = NULL;

        try {
            $resolverItems->resolve([
                '~myId:getAllObjects/%any/getAllObjects/%any/../../myId',
                '?unique',
                '?consider' => function ($item, $id) {
                    if ($id === "~myId") {
                        return $item->get() !== 'ID#6';
                    }
                }
            ]);
        } catch (\Exception $e) {
            $expectedException = $e;
        }

        $this->assertEquals(get_class($expectedException), 'Grabbag\exceptions\CantApplyConsiderModifierException');
        $this->assertEquals($expectedException->getCode(), 1);
        $this->assertEquals($expectedException->getMessage(), 'Can\'t apply ?consider modifier in a multi-valued path result.');

    }

    /**
     * Test if a path-array having multiple path items but returning one single value
     * do return an array (in this case single value must not be returned itself but in an array containing the
     * single value).
     */
    public function testResolverMultiplePathItemMustNotReturnASingleValue()
    {

        $testObject = SourceDataHelper::getDataNamedL2();

        // Assertion using targeted modifier
        $resolverItems = new ItemCollection($testObject);
        $resolverItems->resolve([
            'getAllObjects/%any/getAllObjects/%any/../../myId' => [
                '~myId:.',
                '~myId2:.',
                '?consider@~myId' => function ($item) {
                    return $item->get() !== 'ID#6';
                },
                '?consider@~myId2' => function ($item) {
                    return $item->get() !== 'ID#12';
                }

            ],
        ]);
        $this->assertTrue(is_array($resolverItems->getValue()));
        foreach ($resolverItems->getValue() as $item) {
            $this->assertTrue(is_array($item));
        }
    }

    /**
     *  Test resolving query using ?debug modifier.
     */
    public function testResolverQueryWithDebugModifier()
    {
        $testObject = SourceDataHelper::getDataNamedL2();
        $resolverItems = new ItemCollection($testObject);
        $myDebugInfo = NULL;
        $resolverItems->resolve([
            'getAllObjects/%any' => [
                '~debug:.',
                '?debug' => function ($key, $debug) use (&$myDebugInfo) {
                    if ($key === '~debug') {
                        $myDebugInfo = $debug;
                    }
                }
            ]
        ]);

        $this->assertEquals([
            'class-name' => 'Grabbag\tests\sourceData\List1',
            'method' => [
                '__construct',
                'appendObject',
                'getAllObjects',
                'getOneObject',
                'getName',
                'getId',
                'resetId'
            ],
            'object-var' => [
                'objects',
                'myName',
                'myId',
            ]
        ], $myDebugInfo);

    }

    /**
     *  Test resolving query using . symbols.
     */
    public function testResolverQueryWithDotSymbol()
    {

        $testObject = SourceDataHelper::getDataNamedL2();
        $resolverItems = new ItemCollection($testObject);

        $resolverItems->resolve([
            'getAllObjects/%any/objects/%any/myId' => [
                'myId:.'
            ]
        ]);
        $this->assertEquals(
            TestDataHelper::getTestData2(), $resolverItems->getValue()
        );
    }

    /**
     *  Test resolving query using valid .. symbols.
     */
    public function testResolverQueryWithBoubleDotSymbol()
    {
        $testObject = SourceDataHelper::getDataNamedL2();
        $resolverItems = new ItemCollection($testObject);
        $resolverItems->resolve([
            'getAllObjects/%any' => [
                'id:myId',
                'name:getName',
                'content:getAllObjects/%any' => [
                    'id:getId',
                    'name:getName',
                    'parent-id:../../myId'
                ]
            ]
        ]);

        $this->assertEquals(
            TestDataHelper::getTestData1(TRUE), $resolverItems->getValue()
        );
    }

    /**
     *  Test resolving query using to much .. symbols (Trying to access object down over the root object)
     */
    public function testResolverQueryWithToMuchBoubleDotSymbol()
    {
        $testObject = SourceDataHelper::getDataNamedL2();
        $resolverItems = new ItemCollection($testObject);

        $this->expectException(ResolveItemStackEmptyException::class);

        $resolverItems->resolve([
            'getAllObjects/%any' => [
                'id:myId',
                'name:getName',
                'content:getAllObjects/%any' => [
                    'id:getId',
                    'name:getName',
                    'parent-id:../../../../../../../myId'
                ]
            ]
        ]);
    }

}
