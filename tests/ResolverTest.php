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

class Leaf1 {
    public $myName;
    public $myId;
    protected static $currentId = 0;

    public function __construct($name) {
        $this->myName = $name;
        $this->myId = 'ID#' . self::$currentId++;
    }
    
    public function getName(){
        return $this->myName;
    }
    
    public function getId(){
        return $this->myId;
    }
    
    public static function resetId(){
        self::$currentId = 0;
    }
}

class List1 extends Leaf1{

    public $objects;

    public function __construct($name) {
        parent::__construct($name);
        $this->objects = [];
    }

    public function appendObject($object, $key = NULL) {
        if($key === NULL){
            $this->objects[] = $object;
        }
        else{
            $this->objects[$key] = $object;
        }
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
        $o0 = new List1('test root');
        for ($x = 0; $x < 5; $x++) {
            $o0->appendObject(new Leaf1('test ' . $x));
        }
        return $o0;
    }
    
    public static function getDataNamedL1() {
        $o0 = new List1('test');
        $names = ['my_value_1', 'my_value_2', 'my_value_3' ];
        foreach($names as $name){
            $o0->appendObject(new Leaf1('test ' . $name), $name);
        }
        return $o0;
    }
    
    public static function getDataIndexedL2() {
        $o0 = new List1('test');
        for ($x = 0; $x < 5; $x++) {
            $o1 = new List1('test ' . $x);
            for ($y = 0; $y < 5; $y++) {
                $o1->appendObject(new Leaf1('test ' . $x . '.' . $y));
            }
            $o0->appendObject($o1);
        }
        return $o0;
    }

    public static function getDataNamedL2() {
        $o0 = new List1('test');
        $names = ['my_value_1', 'my_value_2', 'my_value_3' ];
        foreach($names as $name){
            $oL2 = new List1('test ' . $name);
            for ($x = 0; $x < 5; $x++) {
                $nameL2 = $name. '_' . $x;
                $oL2->appendObject(new Leaf1('test ' . $nameL2), $nameL2);
            }
            $o0->appendObject($oL2,$name);
        }
        return $o0;
    }

}

/**
 * @covers Resolver
 */
final class ResolverTest extends TestCase {

    public function testGrabberGrabReturnResult() {
        $testObject = TestStruct::getDataIndexedL2();
        $grabber = new Grabber($testObject);
        $result = $grabber->grab('objects');

        $this->assertInstanceOf(
                Result::class, $result
        );
    }

    public function testGrabberGrabWithBadPathReturnNullByDefault() {
        $testObject = TestStruct::getDataIndexedL2();
        $grabber = new Grabber($testObject);
        
        // $result->getValue() must be NULL 
        $result = $grabber->grab('badpath');
        $this->assertEquals(
                NULL, 
                $result->getValue()
        );
        
        // Must raise an exception when exception activated
        $exceptionActivated = TRUE;
        $this->expectException(PropertyNotFoundException::class);
        $grabber->grab('badpath', NULL, $exceptionActivated);
    }

    public function testGrabberGrabWithBadPathReturnSpecifiedDefaultValue() {
        $testObject = TestStruct::getDataIndexedL2();
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
        
        // One level structure test.
        $testObject = TestStruct::getDataIndexedL1();
        $grabber = new Grabber($testObject);
        $pathVariants = ['getAllObjects.3.getName', 'allObjects.3.name', 'objects.3.myName'];
        foreach ($pathVariants as $pathVariant){
            $result1 = $grabber->grab($pathVariant);
            $this->assertEquals(
                    'test 3', 
                    $result1->getValue()
            );
        }
        
        // Two level structure test.
        $testObjectL2 = TestStruct::getDataIndexedL2();
        $grabberL2 = new Grabber($testObjectL2);
        $pathVariantsL2 = ['getAllObjects.3.getAllObjects.2.getName', 'allObjects.3.allObjects.2.name', 'objects.3.objects.2.myName'];
        foreach ($pathVariantsL2 as $pathVariantL2){
            $resultL2 = $grabberL2->grab($pathVariantL2);
            $this->assertEquals(
                    'test 3.2', 
                    $resultL2->getValue()
            );
        }
    }
    
    public function testGrabberGrabWithKey() {
        $testObject = TestStruct::getDataNamedL1();
        $grabber = new Grabber($testObject);
        
        $pathVariants = ['getAllObjects.my_value_2.getName', 'allObjects.my_value_2.name', 'objects.my_value_2.myName'];
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
        
        // With string parameter
        $pathVariants = [
            ['path' => 'getOneObject("my_value_2").myName', 'expected_value' => 'test my_value_2'],
            ['path' => 'getOneObject("unknown").myName', 'expected_value' => NULL]
        ];
        foreach ($pathVariants as $pathVariant){
            $result1 = $grabber->grab($pathVariant['path']);
            $this->assertEquals(
                    $pathVariant['expected_value'], 
                    $result1->getValue()
            );
        }
        
        // With Numeric parameter 
        $testObject2 = TestStruct::getDataIndexedL1();
        $grabber2 = new Grabber($testObject2);
        $pathVariants2 = [
            ['path' => 'getOneObject(1).getName', 'expected_value' => 'test 1'],
            ['path' => 'getOneObject(10).getName', 'expected_value' => NULL]
        ];
        foreach ($pathVariants2 as $pathVariant2){
            $result2 = $grabber2->grab($pathVariant2['path']);
            $this->assertEquals(
                    $pathVariant2['expected_value'], 
                    $result2->getValue()
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
        $result1 = $grabber->grab('getAllObjects.#each.getName');
        $this->assertEquals(
                ['test 0', 'test 1', 'test 2', 'test 3', 'test 4'], 
                $result1->getValue()
        );

        // Access using implied method
        $result2 = $grabber->grab('allObjects.#each.getName');
        $this->assertEquals(
                ['test 0', 'test 1', 'test 2', 'test 3', 'test 4'], 
                $result2->getValue()
        );

        // Access using object property
        $result3 = $grabber->grab('objects.#each.getName');
        $this->assertEquals(
                ['test 0', 'test 1', 'test 2', 'test 3', 'test 4'], 
                $result3->getValue()
        );
    }
    
    public function testResolveEach(){
        $testObject = TestStruct::getDataNamedL2();
 
        $grabber = new Grabber($testObject);
        $result1 = $grabber
            ->grab(
                [
                    'getAllObjects.#each' =>[
                        'id:myId',
                        'content:getAllObjects.#each'=>[
                            'id:getId',
                            'name:getName'
                        ]
                    ]
                ]
            );
        var_export($result1->getValue());
    }

}
