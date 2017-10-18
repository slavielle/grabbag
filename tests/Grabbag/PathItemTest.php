<?php
/**
 * Created by PhpStorm.
 * User: slavielle
 * Date: 18/10/17
 * Time: 23:26
 */

namespace Grabbag;

use PHPUnit\Framework\TestCase;

class PathItemTest extends \PHPUnit_Framework_TestCase
{
    function test()
    {
        $pathItem = new PathItem('', 'key', '');
        $this->assertEquals(
            'key', $pathItem->getKey()
        );

    }
}
