<?php
/**
 * Created by PhpStorm.
 * User: slavielle
 * Date: 13/09/17
 * Time: 23:02
 */

namespace Grabbag\tests;

use Grabbag\exceptions\PathException;
use Grabbag\Path;
use Grabbag\Resolver;
use Grabbag\tests\sourceData\SourceDataHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers Grabbag\Resolver
 */
final class ResolverTest extends TestCase
{

    /**
     * Test resolving path with malformed path.
     */
    public function testGrabberGrabWithMalformedPath()
    {
        $testObject = SourceDataHelper::getDataIndexedL1();
        $resolver = new Resolver($testObject);
        $this->expectException(PathException::class);
        $resolver->resolve(new Path('getAllObjects/ something'));
    }

    /**
     *  Test result when requesting with a valid but non-matching path
     */
    public function testResolveWithValidButNonMatchingPath()
    {

        $testObject = SourceDataHelper::getDataIndexedL2();

        $resolver = new Resolver($testObject);

        // Must return NULL when no default value is provided.
        $result = $resolver->resolve(new Path('badpath'));
        $this->assertEquals(
            NULL, $result->getValue()
        );

        // Must return provided default value when passing it using method argument.
        $defaultValueSet = [
            NULL,
            'Default String',
            192,
            ['test' => 'A', 'my' => 2, 'array' => [1, 2, 3]]
        ];
        foreach ($defaultValueSet as $defaultValue) {
            $resolver = new Resolver($testObject, $defaultValue);
            $result = $resolver->resolve(new Path('badpath'));
            $this->assertEquals($defaultValue, $result->getValue());
        }
    }

    /**
     * Test if resolving with a non matching path raise an exception.
     * Case :
     *  - ResolverException
     *  - Error code 4
     */
    public function testResolveWithBadPathReturnException()
    {
        $testObject = SourceDataHelper::getDataIndexedL2();
        $expectedException = NULL;

        // Must raise an exception.
        try {
            $resolver = new Resolver($testObject, NULL, TRUE);
            $resolver->resolve(new Path('badpath'));
        } catch (\Exception $e) {
            $expectedException = $e;
        }
        $this->assertEquals('Grabbag\exceptions\ResolverException', get_class($expectedException));
        $this->assertEquals(4, $expectedException->getCode());
        $this->assertEquals('Can\'t resolve "badpath" on item.', $expectedException->getMessage());
    }

    /**
     * Test if resolving with a non matching path raise an exception.
     * Case :
     *  - ResolverException
     *  - Error code 2
     */
    public function testResolveWithBadPathReturnException2()
    {
        $testObject = SourceDataHelper::getDataIndexedL2();
        $expectedException = NULL;

        // Must raise an exception.
        try {
            $resolver = new Resolver($testObject, NULL, TRUE);
            $resolver->resolve(new Path('getAllObjects/badpath'));
        } catch (\Exception $e) {
            $expectedException = $e;
        }
        $this->assertEquals('Grabbag\exceptions\ResolverException', get_class($expectedException));
        $this->assertEquals(5, $expectedException->getCode());
        $this->assertEquals('Can\'t resolve "badpath" on array.', $expectedException->getMessage());
    }

    /**
     * Test if resolving with a non matching path raise an exception.
     * Case :
     *  - ResolverException
     *  - Error code 1
     */
    public function testResolveWithBadPathReturnException3()
    {
        $testObject = SourceDataHelper::getDataIndexedL2();
        $expectedException = NULL;

        // Must raise an exception.
        try {
            $resolver = new Resolver($testObject, NULL, TRUE);
            $resolver->resolve(new Path('getAllObjects/#3/getName/not_existing_item'));
        } catch (\Exception $e) {
            $expectedException = $e;
        }
        $this->assertEquals('Grabbag\exceptions\ResolverException', get_class($expectedException));
        $this->assertEquals(1, $expectedException->getCode());
        $this->assertEquals('Can\'t resolve.', $expectedException->getMessage());
    }

    /**
     * Test if resolving with a non matching path raise an exception.
     * Case :
     *  - ResolverException
     *  - Error code 2
     */
    public function testResolveWithBadPathReturnException4()
    {
        $testObject = SourceDataHelper::getDataIndexedL2();
        $expectedException = NULL;

        // Must raise an exception.
        try {
            $resolver = new Resolver($testObject, NULL, TRUE);
            $resolver->resolve(new Path('getAllObjects/#3/getName/%any'));
        } catch (\Exception $e) {
            $expectedException = $e;
        }
        $this->assertEquals('Grabbag\exceptions\ResolverException', get_class($expectedException));
        $this->assertEquals(2, $expectedException->getCode());
        $this->assertEquals('Trying to apply %any on non traversable value.', $expectedException->getMessage());
    }

    /**
     * Test if resolving with a non matching path raise an exception.
     * Case :
     *  - ResolverException
     *  - Error code 3
     */
    public function testResolveWithBadPathReturnException5()
    {
        $testObject = SourceDataHelper::getDataNamedL1();
        $expectedException = NULL;

        // Must raise an exception when exception activated and path not found.
        try {
            $resolver = new Resolver($testObject, NULL, TRUE);
            $resolver->resolve(new Path('getOneObject("non existing item")'));
        } catch (\Exception $e) {
            $expectedException = $e;
        }
        $this->assertEquals('Grabbag\exceptions\ResolverException', get_class($expectedException));
        $this->assertEquals(3, $expectedException->getCode());
        $this->assertEquals('Parameters passed to method thrown an exception.', $expectedException->getMessage());
    }

    /**
     * Test resolving with numerical index only as a path.
     */

    public function testResolveWithSingleIndex()
    {
        // One level structure test.
        $testObject = ['A', 'B', 'C'];

        $resolver = new Resolver($testObject);
        $result = $resolver->resolve(new Path('#2'));
        $this->assertEquals('C', $result->getValue());
    }

    /**
     * Test resolving with numerical index values in path.
     */
    public function testResolveWithIndex()
    {
        // One level structure test.
        $testObject = SourceDataHelper::getDataIndexedL1();
        $pathVariants = ['getAllObjects/#3/getName', 'allObjects/#3/name', 'objects/#3/myName'];
        foreach ($pathVariants as $pathVariant) {
            $resolver = new Resolver($testObject);
            $result = $resolver->resolve(new Path($pathVariant));
            $this->assertEquals('test 3', $result->getValue());
        }
    }


    /**
     * Test resolving with invalid numerical index values in path.
     */
    public function testResolveWithInvalidIndex()
    {
        // One level structure test.
        $testObject = SourceDataHelper::getDataIndexedL1();
        $pathVariants = ['getAllObjects/#100000/getName', 'allObjects/#100000/name', 'objects/#100000/myName'];
        foreach ($pathVariants as $pathVariant) {
            $resolver = new Resolver($testObject, 'my-default-value');
            $result = $resolver->resolve(new Path($pathVariant));
            $this->assertEquals('my-default-value', $result->getValue());
        }
    }

    /**
     * Test resolving with numerical index (2 levels) values in path.
     */
    public function testResolveWithIndexOn2Levels()
    {
        // Two level structure test.
        $testObject = SourceDataHelper::getDataIndexedL2();
        $pathVariants = ['getAllObjects/#3/getAllObjects/#2/getName', 'allObjects/#3/allObjects/#2/name', 'objects/#3/objects/#2/myName'];
        foreach ($pathVariants as $pathVariant) {
            $resolver = new Resolver($testObject);
            $result = $resolver->resolve(new Path($pathVariant));
            $this->assertEquals('test 3.2', $result->getValue());
        }
    }

    /**
     * Test resolving any on a non traversable object
     */
    public function testResolveWithIndexOn2Levels2()
    {
        // Two level structure test.
        $testObject = SourceDataHelper::getDataIndexedL2();

        $resolver = new Resolver($testObject);
        $result = $resolver->resolve(new Path('getAllObjects/#3/getName/%any'));
        $this->assertEquals([], $result->getValue());
    }

    /**
     * Test resolving with key index values in path.
     */
    public function testResolveWithKey()
    {
        $testObject = SourceDataHelper::getDataNamedL1();
        $pathVariants = ['getAllObjects/my_value_2/getName', 'allObjects/my_value_2/name', 'objects/my_value_2/myName'];
        foreach ($pathVariants as $pathVariant) {
            $resolver = new Resolver($testObject);
            $result = $resolver->resolve(new Path($pathVariant));
            $this->assertEquals('test my_value_2', $result->getValue());
        }
    }

    /**
     * Test resolving with method + string parameter in path.
     */
    public function testResolveWithGetMethodWithStringParameter()
    {
        $testObject = SourceDataHelper::getDataNamedL1();
        $resolver = new Resolver($testObject);

        // With string parameter
        $pathVariants = [
            ['path' => 'getOneObject("my_value_2")/myName', 'expected_value' => 'test my_value_2'],
            ['path' => 'getOneObject("non-exist-value")/myName', 'expected_value' => NULL]
        ];
        foreach ($pathVariants as $pathVariant) {
            $result = $resolver->resolve(new Path($pathVariant['path']));
            $this->assertEquals($pathVariant['expected_value'], $result->getValue());
        }
    }

    /**
     * Test resolving with method + int parameter in path.
     */
    public function testResolveWithGetMethodWithIntParameter()
    {

        // With Numeric parameter
        $testObject = SourceDataHelper::getDataIndexedL1();
        $resolver = new Resolver($testObject);

        $pathVariants = [
            ['path' => 'getOneObject(1)/getName', 'expected_value' => 'test 1'],
            ['path' => 'getOneObject(10)/getName', 'expected_value' => NULL]
        ];
        foreach ($pathVariants as $pathVariant) {
            $result = $resolver->resolve(new Path($pathVariant['path']));
            $this->assertEquals($pathVariant['expected_value'], $result->getValue());
        }
    }

    /**
     * Test resolving path with unknown keyword.
     */
    public function testResolveWithUnknownKeyword()
    {
        $testObject = SourceDataHelper::getDataIndexedL1();
        $resolver = new Resolver($testObject);

        $expectedException = NULL;
        try {
            $resolver->resolve(new Path('getAllObjects/%unknownkeyword'));
        } catch (\Exception $e) {
            $expectedException = $e;
        }

        $this->assertEquals('Grabbag\exceptions\PathException', get_class($expectedException));
        $this->assertEquals(5, $expectedException->getCode());
        $this->assertEquals('Unknown keyword "#unknownkeyword" in path.', $expectedException->getMessage());

    }

    /**
     * Test resolving path with %any keyword
     */
    public function testResolverWithAnyOnArray()
    {
        $testObject = SourceDataHelper::getDataIndexedL1();

        $pathList = [
            'getAllObjects/%any/getName',
            'allObjects/%any/getName',
            'objects/%any/getName',
        ];

        foreach ($pathList as $path) {
            $resolver = new Resolver($testObject);
            $result = $resolver->resolve(new Path($path));
            $this->assertEquals(
                ['test 0', 'test 1', 'test 2', 'test 3', 'test 4'],
                $result->getValue()
            );
        }
    }

    /**
     * Test resolving path with %any keyword
     */
    public function testResolverWithAnyOnObject()
    {
        $testObject = [
            'values' => (object)[
                ['value' => 'test 0'],
                ['value' => 'test 1'],
                ['value' => 'test 2'],
                ['value' => 'test 3'],
                ['value' => 'test 4'],
            ]
        ];

        $pathList = [
            'values/%any/value'
        ];

        foreach ($pathList as $path) {
            $resolver = new Resolver($testObject);
            $result = $resolver->resolve(new Path($path));
            $this->assertEquals(
                ['test 0', 'test 1', 'test 2', 'test 3', 'test 4'],
                $result->getValue()
            );
        }
    }
}
