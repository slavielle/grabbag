<?php

namespace Grabbag;

use Grabbag\exceptions\ModifierException;

class ModifiersTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDefault()
    {
        $modifier = new Modifiers([
            '?call' => function () {
            },
            '?consider' => function () {
            },
            '?debug',
            '?default-value' => 'my default_value',
            '?exception-enabled',
            '?keep-array',
            '?transform' => function () {
            },
            '?unique',
        ]);
        $this->assertEquals(
            'Closure', get_class($modifier->getDefault('call'))
        );
        $this->assertEquals(
            'Closure', get_class($modifier->getDefault('consider'))
        );
        $this->assertEquals(
            TRUE, $modifier->getDefault('debug')
        );
        $this->assertEquals(
            'my default_value', $modifier->getDefault('default-value')
        );
        $this->assertEquals(
            TRUE, $modifier->getDefault('exception-enabled')
        );
        $this->assertEquals(
            TRUE, $modifier->getDefault('keep-array')
        );
        $this->assertEquals(
            'Closure', get_class($modifier->getDefault('transform'))
        );
        $this->assertEquals(
            TRUE, $modifier->getDefault('unique')
        );
    }

    public function testNonExistingModifiers()
    {
        $expectedException = NULL;
        try {
            $modifier = new Modifiers([
                '?non-existing-modifier'
            ]);
        } catch (ModifierException $e) {
            $expectedException = $e;
        }
        $this->assertEquals(get_class($expectedException), 'Grabbag\exceptions\ModifierException');
        $this->assertEquals($expectedException->getCode(), 2);
        $this->assertEquals($expectedException->getMessage(), 'Unknown modifier "non-existing-modifier".');
    }

    public function testModifierOverride()
    {
        $expectedException = NULL;

        $modifier = new Modifiers([
            '?unique' => FALSE,
            '?unique',
        ]);

        $this->assertEquals(TRUE, $modifier->get('unique'));
    }

    public function testModifierWithIdOverride()
    {
        $expectedException = NULL;
        try {
            $modifier = new Modifiers([
                '?debug@testId' => FALSE,
                '?debug@testId',
            ]);
        } catch (ModifierException $e) {
            $expectedException = $e;
        }

        $this->assertEquals(TRUE, $modifier->get('debug', 'testId'));
    }

    public function testGet()
    {

        $modifier = new Modifiers([
            '?call' => function () {
                return 'call';
            },
            '?call@testId' => function () {
                return 'call@testId';
            },
        ]);
        $callable = $modifier->get('call', 'undefinedTest1');
        $this->assertEquals('call', $callable());
        $callable = $modifier->get('call', 'testId');
        $this->assertEquals('call@testId', $callable());
    }
}
