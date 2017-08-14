<?php

require_once '../src/exceptions/NotAdressableException.php';
require_once '../src/exceptions/PropertyNotFoundException.php';
require_once '../src/exceptions/PathParsingException.php';
require_once '../src/exceptions/UnknownPathKeywordException.php';
require_once '../src/Path.php';
require_once '../src/PathItem.php';
require_once '../src/Result.php';
require_once '../src/ResolverInfoItem.php';
require_once '../src/ResolverInfo.php';
require_once '../src/Resolver.php';
require_once '../src/Grabber.php';

use PHPUnit\Framework\TestCase;
use slavielle\grabbag\exceptions\NotAdressableException;
use slavielle\grabbag\exceptions\PropertyNotFoundException;
use slavielle\grabbag\exceptions\PathParsingException;
use slavielle\grabbag\exceptions\UnknownPathKeywordException;
use slavielle\grabbag\Grabber;
use slavielle\grabbag\Path;
use slavielle\grabbag\PathItem;
use slavielle\grabbag\Resolver;
use slavielle\grabbag\ResolverInfo;
use slavielle\grabbag\ResolverInfoItem;
use slavielle\grabbag\Result;

class Test1 {

    public $myName;
    public $objects;
    public $namedObjects;

    public function __construct($name) {
        $this->myName = $name;
        $this->objects = [];
        $this->namedObjects = [];
    }

    public function appendObject($object, $name = NULL) {
        if($name === NULL){
            $this->objects[] = $object;
        }
        else{
            $this->objects[$name] = $object;
        }
    }

    public function appendNamedObject($object, $name) {
        $this->namedObjects[$name] = $object;
    }

    public function getAllObjects() {
        return $this->objects;
    }

    public function getOneObject($indexOrName) {
        return $this->objects[$indexOrName];
    }

}

class TestStruct {

    public static function getDataIndexedL1() {
        $o0 = new Test1('test');
        for ($x = 0; $x < 5; $x++) {
            $o0->appendObject('test ' . $x);
        }
        return $o0;
    }
    
    public static function getDataNamedL1() {
        $o0 = new Test1('test');
        $names = ['my_value_1', 'my_value_2', 'my_value_3' ];
        foreach($names as $name){
            $o0->appendObject('test ' . $name, $name);
        }
        return $o0;
    }
    
    public static function getData1() {
        $o0 = new Test1('test');
        for ($x = 0; $x < 5; $x++) {
            $o1 = new Test1('test ' . $x);
            for ($y = 0; $y < 5; $y++) {
                $o2 = new Test1('test ' . $x . '.' . $y);
                $o1->appendObject($o2);
            }
            $o0->appendObject($o1);
        }
        return $o0;
    }

}

/**
 * @covers Resolver
 */
final class ResolverTest extends TestCase {

    public function testGrabberGrabReturnResult() {
        $testObject = TestStruct::getData1();
        $grabber = new Grabber($testObject);
        $result = $grabber->grab('objects');

        $this->assertInstanceOf(
                Result::class, $result
        );
    }

    public function testGrabberGrabWithBadPathReturnNullByDefault() {
        $testObject = TestStruct::getData1();
        $grabber = new Grabber($testObject);
        $result = $grabber->grab('badpath');

        $this->assertEquals(
                NULL, 
                $result->getValue()
        );
    }

    public function testGrabberGrabWithBadPathReturnSpecifiedDefaultValue() {
        $testObject = TestStruct::getData1();
        $grabber = new Grabber($testObject);
        $defaultValueSet = [
            NULL,
            'Default String',
            192,
            ['test' => 'A', 'my' => 2, 'array' => [1, 2, 3]]
        ];
        foreach ($defaultValueSet as $defaultValue) {
            $result = $grabber->grab('badpath', $defaultValue);
            $this->assertEquals(
                    $defaultValue, $result->getValue()
            );
        }
    }

    public function testGrabberGrabWithIndex() {
        $testObject = TestStruct::getDataIndexedL1();
        $grabber = new Grabber($testObject);
        
        $pathVariants = ['getAllObjects.3', 'allObjects.3', 'objects.3'];
        foreach ($pathVariants as $pathVariant){
            $result1 = $grabber->grab($pathVariant);
            $this->assertEquals(
                    'test 3', 
                    $result1->getValue()
            );
        }
    }
    
    public function testGrabberGrabWithKey() {
        $testObject = TestStruct::getDataNamedL1();
        $grabber = new Grabber($testObject);
        
        $pathVariants = ['getAllObjects.my_value_2', 'allObjects.my_value_2', 'objects.my_value_2'];
        foreach ($pathVariants as $pathVariant){
            $result1 = $grabber->grab($pathVariant);
            $this->assertEquals(
                    'test my_value_2', 
                    $result1->getValue()
            );
        }
    }
    
    public function testGrabberGrabWithGetMethod() {
        $testObject = TestStruct::getDataNamedL1();
        $grabber = new Grabber($testObject);
        
        $pathVariants = ['getOneObject("my_value_2")'];
        foreach ($pathVariants as $pathVariant){
            $result1 = $grabber->grab($pathVariant);
            $this->assertEquals(
                    'test my_value_2', 
                    $result1->getValue()
            );
        }
    }
    
    public function testGrabberGrabWithUnknownKeyword() {
        $testObject = TestStruct::getDataIndexedL1();
        $grabber = new Grabber($testObject);
        $this->expectException(UnknownPathKeywordException::class);
        $grabber->grab('getAllObjects.#unknownkeyword');
    }
    
    public function testGrabberGrabWithMalformedPath() {
        $testObject = TestStruct::getDataIndexedL1();
        $grabber = new Grabber($testObject);
        $this->expectException(PathParsingException::class);
        $grabber->grab('getAllObjects. something');
    }
    
    public function testGrabberGrabWithEach() {
        $testObject = TestStruct::getDataIndexedL1();
        $grabber = new Grabber($testObject);

        // Access using method
        $result1 = $grabber->grab('getAllObjects.#each');
        $this->assertEquals(
                ['test 0', 'test 1', 'test 2', 'test 3', 'test 4'], 
                $result1->getValue()
        );

        // Access using implied method
        $result2 = $grabber->grab('allObjects.#each');
        $this->assertEquals(
                ['test 0', 'test 1', 'test 2', 'test 3', 'test 4'], 
                $result2->getValue()
        );

        // Access using object property
        $result3 = $grabber->grab('objects.#each');
        $this->assertEquals(
                ['test 0', 'test 1', 'test 2', 'test 3', 'test 4'], 
                $result3->getValue()
        );
    }

}
