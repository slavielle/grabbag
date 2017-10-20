<?php
/**
 * Created by PhpStorm.
 * User: slavielle
 * Date: 18/10/17
 * Time: 22:05
 */

namespace Grabbag;

use PHPUnit\Framework\TestCase;

class PathTest extends \PHPUnit_Framework_TestCase
{
    function testNextRewind()
    {
        $path = new Path('this/is/my/path');
        for ($x = 0; $x < 2; $x++) {
            $this->assertEquals(
                "this", $path->next()->getKey()
            );
            $this->assertEquals(
                "is", $path->next()->getKey()
            );
            $this->assertEquals(
                "my", $path->next()->getKey()
            );
            $this->assertEquals(
                "path", $path->next()->getKey()
            );
            $this->assertEquals(
                NULL, $path->next()
            );
            $path->rewind();
        }
    }

    function testGetPathId()
    {
        // Path without id
        $path = new Path('this/is/my/path');
        $this->assertEquals(
            NULL, $path->getPathId()
        );

        // Path with id
        $path = new Path('myId:this/is/my/path');
        $this->assertEquals(
            'myId', $path->getPathId()
        );

        // Path with invalid id
        $expectedException = NULL;
        try {
            $path = new Path('my+Id:this/is/my/path');
        } catch (\Exception $e) {
            $expectedException = $e;
        }
        $this->assertEquals(get_class($expectedException), 'Grabbag\exceptions\PathException');
        $this->assertEquals($expectedException->getCode(), 3);
        $this->assertEquals($expectedException->getMessage(), 'Can \'t parse path near "+Id:this/is/my/path"');


    }
}
