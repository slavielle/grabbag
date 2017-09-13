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
use Grabbag\Grabbag;
use Grabbag\Path;
use Grabbag\PathItem;
use Grabbag\Resolver;
use Grabbag\ResolverItems;


/**
 * @covers Resolver
 */
final class ResolverItemsTest extends TestCase
{

    /**
     *  Test result when requesting with a valid but non-matching path
     */
    public function testResolverWithValidButNonMatchingPath()
    {

        $testObject = sourceDataHelper::getDataIndexedL2();

        $resolverItems = new ResolverItems($testObject);

        // Must return NULL when no default value is provided.
        $resolverItems->resolve('badpath');
        $this->assertEquals(
            NULL, $resolverItems->getValue()
        );

        // Must return provided default value when passing it via method argument.
        $defaultValueSet = [
            NULL,
            'Default String',
            192,
            ['test' => 'A', 'my' => 2, 'array' => [1, 2, 3]]
        ];
        foreach ($defaultValueSet as $defaultValue) {
            $resolverItems->resolve(new Path('badpath'), $defaultValue);
            $this->assertEquals(
                $defaultValue, $resolverItems->getValue()
            );
        }

        // Must return provided default value when passing it as a modifier.
        $defaultValueSet = [
            NULL,
            'Default String',
            192,
            ['test' => 'A', 'my' => 2, 'array' => [1, 2, 3]]
        ];
        foreach ($defaultValueSet as $defaultValue) {
            $resolverItems->resolve(['badpath', '?default-value'=>$defaultValue]);
            $this->assertEquals(
                $defaultValue, $resolverItems->getValue()
            );
        }


    }

    public function testGrabberGrabWithBadPathReturnException(){
        $testObject = sourceDataHelper::getDataIndexedL2();

        // Must raise an exception when exception activated and path not found.
        $this->expectException(PropertyNotFoundException::class);

        $resolverItems = new ResolverItems($testObject);

        $resolverItems->resolve(['badpath','?exception-enabled']);
    }

    public function testGrabberGrabWithBadPath()
    {

        $testObject = sourceDataHelper::getDataNamedL2();
        $resolverItems = new ResolverItems($testObject);

        $expected = [];
        for ($i = 0; $i <= 3; $i++) {
            $expected[] = 'custom_default_value';
        }
        $expected = ['transformed ~myId-ID#0', 'transformed ~myId-ID#6', 'transformed ~myId-ID#12'];

        $resolverItems->resolve([
            'getAllObjects/#any' => [
                '~myId:myId',
                '?default-value' => 'custom_default_value',
                '?transform' => function($value, $id){
                    return 'transformed ' . $id . '-' . $value;
                },
            ]
        ]);

        $this->assertEquals($expected, $resolverItems->getValue());

    }

    public function testGrabberGrabUniqueValues()
    {

        $testObject = sourceDataHelper::getDataNamedL2();
        $resolverItems = new ResolverItems($testObject);

        $expected = ['ID#0', 'ID#6', 'ID#12'];

        $resolverItems->resolve([
            'getAllObjects/#any/getAllObjects/#any/../../myId',
            '?unique'=>TRUE,
        ]);

        $this->assertEquals($expected, $resolverItems->getValue());

    }

    public function testGrabberGrabWithIndex()
    {

        // One level structure test.
        $testObject = sourceDataHelper::getDataIndexedL1();
        $pathVariants = ['getAllObjects/3/getName', 'allObjects/3/name', 'objects/3/myName'];
        foreach ($pathVariants as $pathVariant) {
            $resolverItems = new ResolverItems($testObject);
            $resolverItems->resolve($pathVariant);
            $this->assertEquals(
                'test 3', $resolverItems->getValue()
            );
        }

    }

    public function testGrabberGrabWithIndexOn2Levels()
    {

        // Two level structure test.
        $testObject = sourceDataHelper::getDataIndexedL2();
        $pathVariants = [['getAllObjects/3/getAllObjects/2/getName', '?unique'=>TRUE], 'allObjects/3/allObjects/2/name', 'objects/3/objects/2/myName'];
        foreach ($pathVariants as $pathVariant) {
            $resolverItems = new ResolverItems($testObject);
            $resolverItems->resolve($pathVariant);
            $this->assertEquals(
                'test 3.2', $resolverItems->getValue()
            );
        }
    }

    public function testGrabberGrabWithKey()
    {
        $testObject = sourceDataHelper::getDataNamedL1();

        $pathVariants = ['getAllObjects/my_value_2/getName', 'allObjects/my_value_2/name', 'objects/my_value_2/myName'];
        foreach ($pathVariants as $pathVariant) {
            $resolverItems = new ResolverItems($testObject);
            $resolverItems->resolve($pathVariant);
            $this->assertEquals(
                'test my_value_2', $resolverItems->getValue()
            );
        }
    }

    public function testGrabberGrabWithGetMethodWithStringParameter()
    {
        $testObject = sourceDataHelper::getDataNamedL1();
        $resolverItems = new ResolverItems($testObject);

        // With string parameter
        $pathVariants = [
            ['path' => 'getOneObject("my_value_2")/myName', 'expected_value' => 'test my_value_2'],
            ['path' => 'getOneObject("unknown")/myName', 'expected_value' => NULL]
        ];
        foreach ($pathVariants as $pathVariant) {
            $resolverItems->resolve($pathVariant['path']);
            $this->assertEquals(
                $pathVariant['expected_value'], $resolverItems->getValue()
            );
        }

    }

    public function testGrabberGrabWithGetMethodWithIntParameter()
    {

        // With Numeric parameter
        $testObject = sourceDataHelper::getDataIndexedL1();
        $resolverItems = new ResolverItems($testObject);

        $pathVariants = [
            ['path' => 'getOneObject(1)/getName', 'expected_value' => 'test 1'],
            ['path' => 'getOneObject(10)/getName', 'expected_value' => NULL]
        ];
        foreach ($pathVariants as $pathVariant) {
            $resolverItems->resolve($pathVariant['path']);
            $this->assertEquals(
                $pathVariant['expected_value'], $resolverItems->getValue()
            );
        }
    }

    public function testGrabberGrabWithUnknownKeyword()
    {
        $testObject = sourceDataHelper::getDataIndexedL1();
        $resolverItems = new ResolverItems($testObject);
        $this->expectException(UnknownPathKeywordException::class);
        $resolverItems->resolve('getAllObjects/#unknownkeyword');
    }

    public function testGrabberGrabWithMalformedPath()
    {
        $testObject = sourceDataHelper::getDataIndexedL1();
        $resolverItems = new ResolverItems($testObject);
        $this->expectException(PathParsingException::class);
        $resolverItems->resolve('getAllObjects/ something');
    }

    public function testGrabberGrabWithEach()
    {
        $testObject = sourceDataHelper::getDataIndexedL1();


        // Access using method
        $resolverItems = new ResolverItems($testObject);
        $resolverItems->resolve('getAllObjects/#any/getName');
        $this->assertEquals(
            ['test 0', 'test 1', 'test 2', 'test 3', 'test 4'], $resolverItems->getValue()
        );

        // Access using implied method
        $resolverItems = new ResolverItems($testObject);
        $resolverItems->resolve('allObjects/#any/getName');
        $this->assertEquals(
            ['test 0', 'test 1', 'test 2', 'test 3', 'test 4'], $resolverItems->getValue()
        );

        // Access using object property
        $resolverItems = new ResolverItems($testObject);
        $resolverItems->resolve('objects/#any/getName');
        $this->assertEquals(
            ['test 0', 'test 1', 'test 2', 'test 3', 'test 4'], $resolverItems->getValue()
        );
    }

    public function testPathArrayWithAny2Level()
    {
        $testObject = sourceDataHelper::getDataNamedL2();
        $resolverItems = new ResolverItems($testObject);

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

    public function testDebugModifier()
    {
        $testObject = sourceDataHelper::getDataNamedL2();
        $resolverItems = new ResolverItems($testObject);
        $myDebugInfo = NULL;
        $resolverItems->resolve([
            'getAllObjects/#any' => [
                '~debug:.',
                '?debug' => function($key,$debug) use (&$myDebugInfo) {
                    if($key === '~debug'){
                        $myDebugInfo=$debug;
                    }
                }
            ]
        ]);

        $this->assertEquals([
            'class-name' => 'List1',
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
        ],$myDebugInfo);

    }

    public function testPathArrayWithAny3Level()
    {
        $testObject = sourceDataHelper::getDataNamedL3();
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

    public function testSymbol()
    {

        $testObject = sourceDataHelper::getDataNamedL2();
        $resolverItems = new ResolverItems($testObject);


        $resolverItems->resolve([
            'getAllObjects/#any/objects/#any/myId' => [
                'myId:.'
            ]
        ]);
        $this->assertEquals(
            TestDataHelper::getTestData2(), $resolverItems->getValue()
        );

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

}
