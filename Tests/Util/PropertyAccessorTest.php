<?php

namespace ConnectHolland\RabbitMQMessageEnrichBundle\Util\Test;

use ConnectHolland\RabbitMQMessageEnrichBundle\Util\PropertyAccessor;
use PHPUnit_Framework_TestCase;
use stdClass;

/**
 * Unit test for the PropertyAccessor
 *
 * @author Ron Rademaker
 */
class PropertyAccessorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test the exists method
     *
     * @param object $object
     * @param string $property
     * @param bool $expected
     *
     * @dataProvider provideExistsData
     */
    public function testExists($object, $property, $expected)
    {
        $propertyAccessor = new PropertyAccessor($object);

        $this->assertEquals($expected, $propertyAccessor->exists($property));
    }

    /**
     * Test the setter and the getter.
     *
     * @param object $object
     * @param string $property
     * @param mixed $value
     *
     * @dataProvider provideGetSetData
     */
    public function testGetSet($object, $property, $value)
    {
        $propertyAccessor = new PropertyAccessor($object);
        $propertyAccessor->set($property, $value);

        $this->assertEquals($value, $propertyAccessor->get($property));
    }

    /**
     * Gets test data for the exists method.
     *
     * @return array
     */
    public function provideExistsData()
    {
        $object = new stdClass();
        $object->foobar = new stdClass();
        $object->foobar->baz = false;

        return [
            [clone $object, 'foobar', true],
            [clone $object, 'foobar.baz', true],
            [clone $object, 'foobar.bar', false],
            [clone $object, 'baz', false]
        ];
    }

    /**
     * Gets test data for the get and set methods.
     *
     * @return array
     */
    public function provideGetSetData()
    {
        $object = new stdClass();
        $object->foobar = new stdClass();
        $object->foobar->baz = false;

        return [
            [clone $object, 'foobar', 'foo'],
            [clone $object, 'foobar.baz', 'foo'],
            [clone $object, 'foobar.bar', ['foo']],
            [clone $object, 'baz', new stdClass()]
        ];
    }

}
