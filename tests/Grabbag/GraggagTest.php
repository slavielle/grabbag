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

use Grabbag\Grabbag;
use Grabbag\ItemCollection;
use Grabbag\Resolver;
use Grabbag\tests\sourceData\SourceDataHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers Resolver
 */
final class GrabbagTest extends TestCase
{

    /**
     *  Test result class object
     */
    public function testResolveReturnResult()
    {
        $testObject = sourceDataHelper::getDataIndexedL2();
        $g = new Grabbag($testObject);
        $result = $g->resolve('objects');

        $this->assertInstanceOf(
            ItemCollection::class, $result
        );
    }


    public function testGrab()
    {
        $testObject = sourceDataHelper::getDataIndexedL1();
        $this->assertEquals('test 3', Grabbag::grab($testObject, 'getAllObjects/#3/getName'));
    }

}
