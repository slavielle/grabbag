<?php
/**
 * Created by PhpStorm.
 * User: slavielle
 * Date: 15/09/17
 * Time: 00:15
 */

namespace Grabbag\tests;

use PHPUnit\Framework\TestCase;
use Grabbag\Item;


class ResolverItemTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {

        $testValue = 'test-value';

        $resolverItem = new Item($testValue);

        $this->assertEquals(
            $testValue, $resolverItem->get()
        );
    }

    public function testPopWithEmptyStack()
    {
        $testValue = 'test-value';
        $resolverItem = new Item($testValue);
        $this->expectException(\Exception::class);
        $resolverItem->pop();
    }

    public function testPushPop()
    {
        $values = [
            'value-1',
            'value-2',
            'value-3',
            'value-4',
        ];

        $resolverItem = new Item($values[0]);

        $resolverItem->push($values[1]);
        $this->assertEquals(
            $values[1], $resolverItem->get()
        );
        $resolverItem->push($values[2]);
        $this->assertEquals(
            $values[2], $resolverItem->get()
        );

        $resolverItem->push($values[3]);
        $this->assertEquals(
            $values[3], $resolverItem->get()
        );

        $poppedValue = $resolverItem->pop();
        $this->assertEquals(
            $values[2], $poppedValue
        );
        $this->assertEquals(
            $values[2], $resolverItem->get()
        );

        $poppedValue = $resolverItem->pop();
        $this->assertEquals(
            $values[1], $poppedValue
        );
        $this->assertEquals(
            $values[1], $resolverItem->get()
        );

        $poppedValue = $resolverItem->pop();
        $this->assertEquals(
            $values[0], $poppedValue
        );
        $this->assertEquals(
            $values[0], $resolverItem->get()
        );

        // If stack is empty it must raise an exception.
        $this->expectException(\Exception::class);
        $poppedValue = $resolverItem->pop();
    }

    public function testUpdate(){
        $testValue = '';
        $resolverItem = new Item($testValue);
        $resolverItem->update('updated-value-1');
        $this->assertEquals(
            'updated-value-1', $resolverItem->get()
        );
        $resolverItem->push('');
        $resolverItem->update('updated-value-2');
        $this->assertEquals(
            'updated-value-2', $resolverItem->get()
        );
        $poppedValue = $resolverItem->pop();
        $this->assertEquals(
            'updated-value-1', $poppedValue
        );
        $this->assertEquals(
            'updated-value-1', $resolverItem->get()
        );
    }

    //@todo test prepareResolverItem
}
