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
final class GrabbagTest extends TestCase
{

    /**
     *  Test result class object
     */
    public function testGrabberGrabReturnResult()
    {
        $testObject = sourceDataHelper::getDataIndexedL2();
        $g = new Grabbag($testObject);
        $result = $g->resolve('objects');

        $this->assertInstanceOf(
            ResolverItems::class, $result
        );
    }
}
