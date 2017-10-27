<?php

namespace Grabbag;

use Grabbag\exceptions\ModifierException;

class ModifiersTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $modifier = new Modifiers([
            '?call' => function () {
            },
            '?consider' => function () {
            },
            '?debug' => function () {

            },
            '?default-value' => 'my default_value',
            '?exception-enabled' => TRUE,
            '?keep-array' => TRUE,
            '?transform' => function () {
            },
            '?unique' => TRUE,
        ]);
        $this->assertEquals(
            'Closure', get_class($modifier->getDefault('call'))
        );
        $this->assertEquals(
            'Closure', get_class($modifier->getDefault('consider'))
        );
        $this->assertEquals(
            'Closure', get_class($modifier->getDefault('debug'))
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

    /**
     * Test modifier parameter is set to TRUE by default.
     */
    public function testDefaultValue()
    {
        $modifier = new Modifiers([
            '?exception-enabled',
            '?keep-array',
            '?unique',
        ]);
        $this->assertEquals(TRUE, $modifier->get('exception-enabled'));
        $this->assertEquals(TRUE, $modifier->get('keep-array'));
        $this->assertEquals(TRUE, $modifier->get('unique'));
    }

    /**
     * Use a an unknown modifier in path array must throw an error
     */
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

    /**
     * Test modifier override : If modifier is defined twice, only last modifier is used.
     */
    public function testModifierOverride()
    {

        $modifier = new Modifiers([
            '?unique' => FALSE,
            '?unique',
        ]);

        $this->assertEquals(TRUE, $modifier->get('unique'));
    }

    /**
     * Test modifier (with id) override : If modifier is defined twice, only last modifier is used.
     */
    public function testModifierWithIdOverride()
    {

        $modifier = new Modifiers([
            '?default-value@testId' => FALSE,
            '?default-value@testId',
        ]);

        $this->assertEquals(TRUE, $modifier->get('default-value', 'testId'));
    }

    public function testGetDefault()
    {

        $modifier = new Modifiers([
            '?default-value' => 'test-val',
        ]);
        $this->assertEquals('test-val', $modifier->getDefault('default-value'));
    }

    public function testGetDefaultOnNonExistingModifier()
    {

        $modifier = new Modifiers([
            '?default-value' => 'test-val',
        ]);

        $expectedException = NULL;
        try {
            $modifier->getDefault('unique');
        } catch (ModifierException $e) {
            $expectedException = $e;
        }
        $this->assertEquals('Grabbag\exceptions\ModifierException', get_class($expectedException));
        $this->assertEquals(4, $expectedException->getCode());
        $this->assertEquals('Undefined modifier "unique".', $expectedException->getMessage());


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

    public function testGet2()
    {
        $modifier = new Modifiers([
            '?unique'
        ]);

        $expectedException = NULL;
        try {
            $modifier->get('default-value');
        } catch (ModifierException $e) {
            $expectedException = $e;
        }
        $this->assertEquals('Grabbag\exceptions\ModifierException', get_class($expectedException));
        $this->assertEquals(4, $expectedException->getCode());
        $this->assertEquals('Undefined modifier "default-value".', $expectedException->getMessage());
    }

    public function testParameterType()
    {
        $expectedException = NULL;
        try {
            $modifier = new Modifiers([
                '?call'
            ]);
        } catch (ModifierException $e) {
            $expectedException = $e;
        }

        $this->assertEquals(get_class($expectedException), 'Grabbag\exceptions\ModifierException');
        $this->assertEquals($expectedException->getCode(), 3);
        $this->assertEquals($expectedException->getMessage(), 'Bad parameter type on "?call" modifier. Expected : Closure.');
    }
}
