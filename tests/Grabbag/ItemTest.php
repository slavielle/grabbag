<?php
/**
 * Created by PhpStorm.
 * User: slavielle
 * Date: 15/09/17
 * Time: 00:15
 */

namespace Grabbag\tests;

use Grabbag\Item;
use PHPUnit\Framework\TestCase;


class ItemTest extends \PHPUnit_Framework_TestCase
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
        $this->assertEquals($values[1], $resolverItem->get());
        $resolverItem->push($values[2]);
        $this->assertEquals($values[2], $resolverItem->get());

        $resolverItem->push($values[3]);
        $this->assertEquals($values[3], $resolverItem->get());

        $poppedValue = $resolverItem->pop();
        $this->assertEquals($values[2], $poppedValue);
        $this->assertEquals($values[2], $resolverItem->get());

        $poppedValue = $resolverItem->pop();
        $this->assertEquals($values[1], $poppedValue);
        $this->assertEquals($values[1], $resolverItem->get());

        $poppedValue = $resolverItem->pop();
        $this->assertEquals($values[0], $poppedValue);
        $this->assertEquals($values[0], $resolverItem->get());

        // If stack is empty it must raise an exception.
        $this->expectException(\Exception::class);
        $poppedValue = $resolverItem->pop();
    }

    public function testPushPopWithKey()
    {
        $values = [
            ['value' => 'value-1', 'key' => 'key-1'],
            ['value' => 'value-2', 'key' => 'key-2'],
            ['value' => 'value-3', 'key' => 'key-3'],
            ['value' => 'value-4', 'key' => 'key-4'],
        ];

        $resolverItem = new Item($values[0]['value'], $values[0]['key']);

        $resolverItem->push($values[1]['value'], $values[1]['key']);
        $this->assertEquals($values[1]['value'], $resolverItem->get());
        $this->assertEquals($values[1]['key'], $resolverItem->getKey());

        $resolverItem->push($values[2]['value'], $values[2]['key']);
        $this->assertEquals($values[2]['value'], $resolverItem->get());
        $this->assertEquals($values[2]['key'], $resolverItem->getKey());

        $resolverItem->push($values[3]['value'], $values[3]['key']);
        $this->assertEquals($values[3]['value'], $resolverItem->get());
        $this->assertEquals($values[3]['key'], $resolverItem->getKey());

        $poppedValue = $resolverItem->pop();
        $this->assertEquals($values[2]['value'], $poppedValue);
        $this->assertEquals($values[2]['value'], $resolverItem->get());
        $this->assertEquals($values[2]['key'], $resolverItem->getKey());

        $poppedValue = $resolverItem->pop();
        $this->assertEquals($values[1]['value'], $poppedValue);
        $this->assertEquals($values[1]['value'], $resolverItem->get());
        $this->assertEquals($values[1]['key'], $resolverItem->getKey());

        $poppedValue = $resolverItem->pop();
        $this->assertEquals($values[0]['value'], $poppedValue);
        $this->assertEquals($values[0]['value'], $resolverItem->get());
        $this->assertEquals($values[0]['key'], $resolverItem->getKey());

        // If stack is empty it must raise an exception.
        $this->expectException(\Exception::class);
        $poppedValue = $resolverItem->pop();
    }

    public function testUpdate()
    {
        $testValue = '';
        $resolverItem = new Item($testValue);
        $resolverItem->update('updated-value-1');
        $this->assertEquals('updated-value-1', $resolverItem->get());
        $resolverItem->push('');
        $resolverItem->update('updated-value-2');
        $this->assertEquals('updated-value-2', $resolverItem->get());
        $poppedValue = $resolverItem->pop();
        $this->assertEquals('updated-value-1', $poppedValue);
        $this->assertEquals('updated-value-1', $resolverItem->get());
    }

    /**
     * Test normalizeResolverItem helper.
     */
    public function testnormalizeResolverItem()
    {

        // Adding a value must return an array with one Item instance.
        $Item1 = Item::normalizeResolverItem("test-1");
        $this->assertEquals(TRUE, is_array($Item1));
        $this->assertEquals(1, count($Item1));
        $this->assertEquals("Grabbag\Item", get_class($Item1[0]));
        $this->assertEquals("test-1", $Item1[0]->get());

        // Adding an Item must return an array with this only this Item instance inside.
        $Item1 = Item::normalizeResolverItem(new Item("test-1"));
        $this->assertEquals(TRUE, is_array($Item1));
        $this->assertEquals(1, count($Item1));
        $this->assertEquals("Grabbag\Item", get_class($Item1[0]));
        $this->assertEquals("test-1", $Item1[0]->get());

    }
}
