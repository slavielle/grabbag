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
use Grabbag\exceptions\NotAdressableException;
use Grabbag\exceptions\PropertyNotFoundException;
use Grabbag\exceptions\PathParsingException;
use Grabbag\exceptions\UnknownPathKeywordException;
use Grabbag\exceptions\ResolveItemStackEmptyException;
use Grabbag\Grabbag;
use Grabbag\Path;
use Grabbag\PathItem;
use Grabbag\Resolver;
use Grabbag\ResolverItem;
use Grabbag\ResolverItems;
use Grabbag\tests\sourceData\SourceDataHelper;
use Grabbag\tests\testData\TestDataHelper;


/**
 * @covers Resolver
 */
final class ResolverItemsTest extends TestCase
{

    /**
     * Test the getValue result when a $resolverItems had not been resolved.
     * The result value must be the input value.
     */
    public function testGetValueWithoutResolving(){
        $myInputValue = ['test 1', 'test 2'];
        $myOutputValue = ['test 1', 'test 2'];
        $resolverItems = new ResolverItems($myInputValue);
        $this->assertEquals(
            $myOutputValue, $resolverItems->getValue()
        );
    }

    /**
     * Test default result when requesting with a valid but non-matching path
     * Similar to ResolverTest::testResolveWithValidButNonMatchingPath but use default-value modifier.
     */
    public function testResolveWithValidButNonMatchingPath()
    {

        $testObject = SourceDataHelper::getDataIndexedL2();

        $resolverItems = new ResolverItems($testObject);

        // Must return provided default value when passing it using a modifier.
        $defaultValueSet = [
            NULL,
            'Default String',
            192,
            ['test' => 'A', 'my' => 2, 'array' => [1, 2, 3]]
        ];
        foreach ($defaultValueSet as $defaultValue) {
            $resolverItems->resolve([
                    'badpath',
                    '?default-value' => $defaultValue]
            );
            $this->assertEquals(
                $defaultValue, $resolverItems->getValue()
            );
        }
    }

    /**
     * Test if resolving with a non matching path raise a PropertyNotFoundException exception.
     * Similar to ResolverTest::testResolveWithBadPathReturnException but using exception-enabled modifier.
     */
    public function testResolveWithBadPathReturnException()
    {
        $testObject = SourceDataHelper::getDataIndexedL2();

        // Must raise an exception when exception activated and path not found.
        $this->expectException(PropertyNotFoundException::class);

        $resolverItems = new ResolverItems($testObject);

        $resolverItems->resolve(['badpath', '?exception-enabled']);
    }

    /**
     * Test resolving with numerical index values in path.
     * Similar test to ResolverTest::testResolveWithIndex but on a set of ResolverItem.
     */
    public function testResolveWithIndex()
    {
        // One level structure test.
        $testObject = SourceDataHelper::getDataIndexedL1();
        $pathVariants = ['getAllObjects/3/getName', 'allObjects/3/name', 'objects/3/myName'];
        foreach ($pathVariants as $pathVariant) {
            $resolverItems = new ResolverItems([
                new ResolverItem($testObject),
                new ResolverItem($testObject)
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
     * Similar test to ResolverTest::testResolveWithIndexOn2Levels but on a set of ResolverItem.
     */
    public function testResolveWithIndexOn2Levels()
    {

        // Two level structure test.
        $testObject = SourceDataHelper::getDataIndexedL2();
        $pathVariants = ['getAllObjects/3/getAllObjects/2/getName', 'allObjects/3/allObjects/2/name', 'objects/3/objects/2/myName'];
        foreach ($pathVariants as $pathVariant) {
            $resolverItems = new ResolverItems([
                new ResolverItem($testObject),
                new ResolverItem($testObject),
            ], FALSE);
            $resolverItems->resolve($pathVariant);
            $this->assertEquals([
                'test 3.2',
                'test 3.2'
            ], $resolverItems->getValue());
        }
    }

    /**
     * Test resolving with key index values in path.
     * Similar test to ResolverTest::testResolveWithKey but on a set of ResolverItem.
     */
    public function testResolveWithKey()
    {
        $testObject = SourceDataHelper::getDataNamedL1();

        $pathVariants = ['getAllObjects/my_value_2/getName', 'allObjects/my_value_2/name', 'objects/my_value_2/myName'];
        foreach ($pathVariants as $pathVariant) {
            $resolverItems = new ResolverItems([
                new ResolverItem($testObject),
                new ResolverItem($testObject)
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
     * Similar test to ResolverTest::testResolveWithGetMethodWithStringParameter but on a set of ResolverItem.
     */
    public function testResolveWithGetMethodWithStringParameter()
    {
        $testObject = SourceDataHelper::getDataNamedL1();
        $resolverItems = new ResolverItems([
            new ResolverItem($testObject),
            new ResolverItem($testObject)
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
     * Similar test to ResolverTest::testResolveWithGetMethodWithIntParameter but on a set of ResolverItem.
     */
    public function testResolveWithGetMethodWithIntParameter()
    {

        // With Numeric parameter
        $testObject = SourceDataHelper::getDataIndexedL1();
        $resolverItems = new ResolverItems([
            new ResolverItem($testObject),
            new ResolverItem($testObject)
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
     * Test resolving path with #any keyword
     * Similar test to ResolverTest::testResolverWithAny but on a set of ResolverItem.
     */
    public function testResolverWithAny()
    {
        $testObject = SourceDataHelper::getDataIndexedL1();

        $pathList = [
            'getAllObjects/#any/getName',
            'allObjects/#any/getName',
            'objects/#any/getName',
        ];

        foreach ($pathList as $path) {
            $resolverItems = new ResolverItems([
                new ResolverItem($testObject),
                new ResolverItem($testObject),
            ], FALSE);
            $resolverItems->resolve($path);
            $this->assertEquals([
                ['test 0', 'test 1', 'test 2', 'test 3', 'test 4'],
                ['test 0', 'test 1', 'test 2', 'test 3', 'test 4']
            ], $resolverItems->getValue());
        }
    }

    /**
     * Test resolving path array using #any
     */
    public function testResolverPathArrayWithAny2level()
    {
        $resolverItems = new ResolverItems(SourceDataHelper::getDataNamedL2());

        $resolverItems->resolve([
            'getAllObjects/#any' => [
                'id:myId',
                'name:getName',
                'content:getAllObjects/#any' => [
                    'id:getId',
                    'name:getName'
                ]
            ]
        ]);

        $this->assertEquals(
            TestDataHelper::getTestData1(), $resolverItems->getValue()
        );
    }

    /*
    public function testResolverPathArrayWithAny3Level()
    {
        $testObject = SourceDataHelper::getDataNamedL3();
        $resolverItems = new ResolverItems($testObject);

        $resolverItems->resolve([
            'getAllObjects/#any' => [
                'id:myId',
                'name:getName',
                'content-L2:getAllObjects/#any' => [
                    'id:getId',
                    'name:getName',
                    'content-L3:getAllObjects/#any' => [
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
     * Test resolving path array using ?transform modifier.
     */
    public function testResolverPathArrayWithTransformModifier()
    {

        $testObject = SourceDataHelper::getDataNamedL2();
        $resolverItems = new ResolverItems($testObject);

        $expected = ['transformed ~myId-ID#0', 'transformed ~myId-ID#6', 'transformed ~myId-ID#12'];

        $resolverItems->resolve([
            'getAllObjects/#any' => [
                '~myId:myId',
                '?transform' => function ($value, $id) {
                    return 'transformed ' . $id . '-' . $value;
                },
            ]
        ]);

        $this->assertEquals($expected, $resolverItems->getValue());

    }

    /**
     *  Test resolving path array using ?unique modifier.
     */
    public function testResolverPathArrayUniqueModifier()
    {

        $testObject = SourceDataHelper::getDataNamedL2();
        $resolverItems = new ResolverItems($testObject);

        $expected = ['ID#0', 'ID#6', 'ID#12'];

        $resolverItems->resolve([
            'getAllObjects/#any/getAllObjects/#any/../../myId',
            '?unique' => TRUE,
        ]);

        $this->assertEquals($expected, $resolverItems->getValue());

    }

    /**
     *  Test resolving path array using ?debug modifier.
     */
    public function testResolverPathArrayWithDebugModifier()
    {
        $testObject = SourceDataHelper::getDataNamedL2();
        $resolverItems = new ResolverItems($testObject);
        $myDebugInfo = NULL;
        $resolverItems->resolve([
            'getAllObjects/#any' => [
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
     *  Test resolving path array using . symbols.
     */
    public function testResolverPathArrayWithDotSymbol()
    {

        $testObject = SourceDataHelper::getDataNamedL2();
        $resolverItems = new ResolverItems($testObject);

        $resolverItems->resolve([
            'getAllObjects/#any/objects/#any/myId' => [
                'myId:.'
            ]
        ]);
        $this->assertEquals(
            TestDataHelper::getTestData2(), $resolverItems->getValue()
        );
    }

    /**
     *  Test resolving path array using valid .. symbols.
     */
    public function testResolverPathArrayWithBoubleDotSymbol()
    {
        $testObject = SourceDataHelper::getDataNamedL2();
        $resolverItems = new ResolverItems($testObject);
        $resolverItems->resolve([
            'getAllObjects/#any' => [
                'id:myId',
                'name:getName',
                'content:getAllObjects/#any' => [
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
     *  Test resolving path array using to much .. symbols (Trying to access object down over the root object)
     */
    public function testResolverPathArrayWithToMuchBoubleDotSymbol()
    {
        $testObject = SourceDataHelper::getDataNamedL2();
        $resolverItems = new ResolverItems($testObject);

        $this->expectException(ResolveItemStackEmptyException::class);

        $resolverItems->resolve([
            'getAllObjects/#any' => [
                'id:myId',
                'name:getName',
                'content:getAllObjects/#any' => [
                    'id:getId',
                    'name:getName',
                    'parent-id:../../../../../../../myId'
                ]
            ]
        ]);
    }

}
