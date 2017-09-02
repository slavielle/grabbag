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
final class ResolverTest extends TestCase
{

    /**
     *  Test result class object
     */
    public function testGrabberGrabReturnResult()
    {
        $testObject = sourceDataHelper::getDataIndexedL2();
        $g = new Grabbag($testObject);
        $result = $g->grab('objects');

        $this->assertInstanceOf(
            ResolverItems::class, $result
        );
    }

    /**
     *  Test result when requesting with a valid but non-matching path
     */
    public function testGrabberGrabWithBadPathReturnNullByDefault()
    {

        $testObject = sourceDataHelper::getDataIndexedL2();
        $g = new Grabbag($testObject);

        // Must return NULL when no default value is provided.
        $result = $g->grab('badpath');
        $result->getValue();
        $this->assertEquals(
            NULL, $result->getValue()
        );

        // Must return provided default value if any.
        $defaultValueSet = [
            NULL,
            'Default String',
            192,
            ['test' => 'A', 'my' => 2, 'array' => [1, 2, 3]]
        ];
        foreach ($defaultValueSet as $defaultValue) {
            $result = $g->grab(new Path('badpath'), $defaultValue);
            $this->assertEquals(
                $defaultValue, $result->getValue()
            );
        }
    }

    public function testGrabberGrabWithBadPathReturnException(){
        $testObject = sourceDataHelper::getDataIndexedL2();
        // Must raise an exception when exception activated and path not found.
        $exceptionActivated = TRUE;
        $this->expectException(PropertyNotFoundException::class);

        $g = new Grabbag($testObject);

        $g->grab(['badpath','?exception-enabled']);
    }

    public function testGrabberGrabWithBadPath()
    {

        $testObject = sourceDataHelper::getDataNamedL2();
        $g = new Grabbag($testObject);

        $expected = [];
        for ($i = 0; $i <= 3; $i++) {
            $expected[] = 'custom_default_value';
        }
        $expected = ['transformed ~myId-ID#0', 'transformed ~myId-ID#6', 'transformed ~myId-ID#12'];

        $result = $g->grab([
            'getAllObjects/#any' => [
                '~myId:myId',
                '?default-value' => 'custom_default_value',
                '?transform' => function($value, $id){
                    return 'transformed ' . $id . '-' . $value;
                },
            ]
        ]);

        $this->assertEquals($expected, $result->getValue());

    }

    public function testGrabberGrabUniqueValues()
    {

        $testObject = sourceDataHelper::getDataNamedL2();
        $g = new Grabbag($testObject);

        $expected = ['ID#0', 'ID#6', 'ID#12'];

        $result = $g->grab([
            'getAllObjects/#any/getAllObjects/#any/../../myId',
            '?unique'=>TRUE,
        ]);

        $this->assertEquals($expected, $result->getValue());

    }

    public function testGrabberGrabWithIndex()
    {

        // One level structure test.
        $testObject = sourceDataHelper::getDataIndexedL1();
        $g = new Grabbag($testObject);
        $pathVariants = ['getAllObjects/3/getName', 'allObjects/3/name', 'objects/3/myName'];
        foreach ($pathVariants as $pathVariant) {
            $result1 = $g->grab($pathVariant);
            $this->assertEquals(
                'test 3', $result1->getValue()
            );
        }

        // Two level structure test.
        $testObjectL2 = sourceDataHelper::getDataIndexedL2();
        $gL2 = new Grabbag($testObjectL2);
        $pathVariantsL2 = [['getAllObjects/3/getAllObjects/2/getName', '?unique'=>TRUE], 'allObjects/3/allObjects/2/name', 'objects/3/objects/2/myName'];
        foreach ($pathVariantsL2 as $pathVariantL2) {
            $resultL2 = $gL2->grab($pathVariantL2);
            $this->assertEquals(
                'test 3.2', $resultL2->getValue()
            );
        }

    }

    public function testGrabberGrabWithKey()
    {
        $testObject = sourceDataHelper::getDataNamedL1();
        $g = new Grabbag($testObject);

        $pathVariants = ['getAllObjects/my_value_2/getName', 'allObjects/my_value_2/name', 'objects/my_value_2/myName'];
        foreach ($pathVariants as $pathVariant) {
            $result1 = $g->grab($pathVariant);
            $this->assertEquals(
                'test my_value_2', $result1->getValue()
            );
        }
    }

    public function testGrabberGrabWithGetMethod()
    {
        $testObject = sourceDataHelper::getDataNamedL1();
        $g = new Grabbag($testObject);

        // With string parameter
        $pathVariants = [
            ['path' => 'getOneObject("my_value_2")/myName', 'expected_value' => 'test my_value_2'],
            ['path' => 'getOneObject("unknown")/myName', 'expected_value' => NULL]
        ];
        foreach ($pathVariants as $pathVariant) {
            $result1 = $g->grab($pathVariant['path']);
            $this->assertEquals(
                $pathVariant['expected_value'], $result1->getValue()
            );
        }

        // With Numeric parameter 
        $testObject2 = sourceDataHelper::getDataIndexedL1();
        $g2 = new Grabbag($testObject2);
        $pathVariants2 = [
            ['path' => 'getOneObject(1)/getName', 'expected_value' => 'test 1'],
            ['path' => 'getOneObject(10)/getName', 'expected_value' => NULL]
        ];
        foreach ($pathVariants2 as $pathVariant2) {
            $result2 = $g2->grab($pathVariant2['path']);
            $this->assertEquals(
                $pathVariant2['expected_value'], $result2->getValue()
            );
        }
    }

    public function testGrabberGrabWithUnknownKeyword()
    {
        $testObject = sourceDataHelper::getDataIndexedL1();
        $g = new Grabbag($testObject);
        $this->expectException(UnknownPathKeywordException::class);
        $g->grab('getAllObjects/#unknownkeyword');
    }

    public function testGrabberGrabWithMalformedPath()
    {
        $testObject = sourceDataHelper::getDataIndexedL1();
        $g = new Grabbag($testObject);
        $this->expectException(PathParsingException::class);
        $g->grab('getAllObjects/ something');
    }

    public function testGrabberGrabWithEach()
    {
        $testObject = sourceDataHelper::getDataIndexedL1();
        $g = new Grabbag($testObject);

        // Access using method
        $result1 = $g->grab('getAllObjects/#any/getName');
        $this->assertEquals(
            ['test 0', 'test 1', 'test 2', 'test 3', 'test 4'], $result1->getValue()
        );

        // Access using implied method
        $result2 = $g->grab('allObjects/#any/getName');
        $this->assertEquals(
            ['test 0', 'test 1', 'test 2', 'test 3', 'test 4'], $result2->getValue()
        );

        // Access using object property
        $result3 = $g->grab('objects/#any/getName');
        $this->assertEquals(
            ['test 0', 'test 1', 'test 2', 'test 3', 'test 4'], $result3->getValue()
        );
    }

    public function testResolveEach()
    {
        $testObject = sourceDataHelper::getDataNamedL2();

        $g = new Grabbag($testObject);
        $result1 = $g->grab([
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
            TestDataHelper::getTestData1(), $result1->getValue()
        );

    }

    public function testSymbol()
    {

        $testObject = sourceDataHelper::getDataNamedL2();
        $g = new Grabbag($testObject);

        $result1 = $g->grab([
            'getAllObjects/#any/objects/#any/myId' => [
                'myId:.'
            ]
        ]);

        $this->assertEquals(
            TestDataHelper::getTestData2(), $result1->getValue()
        );

        $result2 = $g->grab([
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
            TestDataHelper::getTestData1(TRUE), $result2->getValue()
        );
    }

}
