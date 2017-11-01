<?php
/**
 * Created by PhpStorm.
 * User: slavielle
 * Date: 18/10/17
 * Time: 23:26
 */

namespace Grabbag;

use Grabbag\exceptions\PathException;
use PHPUnit\Framework\TestCase;

class PathItemTest extends \PHPUnit_Framework_TestCase
{
    function testGetKey()
    {
        $pathItem = new PathItem('', 'key', '');
        $this->assertEquals(
            'key', $pathItem->getKey()
        );

    }

    function testNumericKeyWithoutNumericalPrefix()
    {
        $expectedException = NULL;

        try {
            $pathItem = new PathItem('', '5', '');
        } catch (PathException $e) {
            $expectedException = $e;
        }

        $this->assertEquals('Grabbag\exceptions\PathException', get_class($expectedException));
        $this->assertEquals(1, $expectedException->getCode());
        $this->assertEquals('Numerical value encoutered without "#".', $expectedException->getMessage());

    }

    /**
     * Bad special char must throw an exception
     */
    function testBadSpecialChar()
    {
        $expectedException = NULL;

        try {
            $pathItem = new PathItem('!', 'key', '');
        } catch (PathException $e) {
            $expectedException = $e;
        }

        $this->assertEquals('Grabbag\exceptions\PathException', get_class($expectedException));
        $this->assertEquals(6, $expectedException->getCode());
        $this->assertEquals('Unknown special char "!".', $expectedException->getMessage());

    }

    /**
     * Bad parameter must throw an exception
     */
    function testBadParam()
    {
        $expectedException = NULL;

        try {
            $pathItem = new PathItem('', 'key', 'badparam');
        } catch (PathException $e) {
            $expectedException = $e;
        }

        $this->assertEquals('Grabbag\exceptions\PathException', get_class($expectedException));
        $this->assertEquals(2, $expectedException->getCode());
        $this->assertEquals('Can\'t parse parameter "badparam".', $expectedException->getMessage());

    }

    function testHasParam()
    {
        $pathItem = new PathItem('', 'key', '"param"');
        $this->assertEquals(TRUE, $pathItem->hasParam());

        $pathItem = new PathItem('', 'key', '');
        $this->assertEquals(FALSE, $pathItem->hasParam());
    }

    function testGetParam()
    {
        $pathItem = new PathItem('', 'key', '"param"');
        $this->assertEquals(['param'], $pathItem->getParams());

        $pathItem = new PathItem('', 'key', '5');
        $this->assertEquals([5], $pathItem->getParams());

        $pathItem = new PathItem('', 'key', '5.3');
        $this->assertEquals([5.3], $pathItem->getParams());

        $pathItem = new PathItem('', 'key', '0005.3000');
        $this->assertEquals([5.3], $pathItem->getParams());
    }

    function testIsKeyword()
    {
        $pathItem = new PathItem('%', 'key', '');
        $this->assertEquals(TRUE, $pathItem->isKeyword());

        $pathItem = new PathItem('#', 'key', '"param"');
        $this->assertEquals(FALSE, $pathItem->isKeyword());

        $pathItem = new PathItem('', 'key', '"param"');
        $this->assertEquals(FALSE, $pathItem->isKeyword());
    }

    function testKeywordError()
    {
        $expectedException = NULL;

        try {
            $pathItem = new PathItem('%', 'key', '"test"');
        } catch (PathException $e) {
            $expectedException = $e;
        }

        $this->assertEquals('Grabbag\exceptions\PathException', get_class($expectedException));
        $this->assertEquals(7, $expectedException->getCode());
        $this->assertEquals('Keyword "key" cannot have parameter "test".', $expectedException->getMessage());
    }

    function testIsSymbol()
    {
        $pathItem = new PathItem('', '..', '');
        $this->assertEquals(TRUE, $pathItem->isSymbol());

        $pathItem = new PathItem('', '.', '');
        $this->assertEquals(TRUE, $pathItem->isSymbol());

        $pathItem = new PathItem('', 'key', '"test"');
        $this->assertEquals(FALSE, $pathItem->isSymbol());
    }


    function testSymbolError()
    {


        $expectedException = NULL;

        try {
            $pathItem = new PathItem('', '..', '"test"');
        } catch (PathException $e) {
            $expectedException = $e;
        }

        $this->assertEquals('Grabbag\exceptions\PathException', get_class($expectedException));
        $this->assertEquals(8, $expectedException->getCode());
        $this->assertEquals('Symbol ".." cannot have parameter "test".', $expectedException->getMessage());

    }

}
