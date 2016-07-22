<?php

namespace ConnectHolland\RabbitMQMessageEnrichBundle\Util;

use stdClass;

/**
 * Utility to test and access properties in an object
 *
 * @author Ron Rademaker
 */
class PropertyAccessor
{
    /**
     * The object to test.
     *
     * @var object
     */
    private $object;

    /**
     * Create a ProperyAccessor for $object
     *
     * @param object $object
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    /**
     * Checks if a property exists, dot-notation is allowed for nesting.
     *
     * @param string $property
     * @return bool
     */
    public function exists($property)
    {
        return $this->testPropertyExists(explode('.', $property), $this->object);
    }

    /**
     * Sets $value at property.
     *
     * @param string $property
     * @param mixed $value
     */
    public function set($property, $value)
    {
        return $this->setPropertyValue(explode('.', $property), $this->object, $value);
    }

    /**
     * Gets the value og property.
     *
     * @param string $property
     */
    public function get($property)
    {
        return $this->getPropertyValue(explode('.', $property), $this->object);
    }

    /**
     * Get the object.
     *
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Tests if a property exists at the given path.
     *
     * @param array $path
     * @param object $object
     *
     * @return bool
     */
    private function testPropertyExists(array $path, $object)
    {
        $property = array_shift($path);
        if (property_exists($object, $property)) {
            if (count($path) > 0) {
                return $this->testPropertyExists($path, $object->$property);
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets a property value
     *
     * @param array $path
     * @param object $object
     *
     * @return mixed
     */
    private function getPropertyValue(array $path, $object)
    {
        $property = array_shift($path);
        if (property_exists($object, $property)) {
            if (count($path) > 0) {
                return $this->getPropertyValue($path, $object->$property);
            } else {
                return $object->$property;
            }
        }

        return null;
    }

    /**
     * Sets a property value
     *
     * @param array $path
     * @param object $object
     * @param mixed $value
     */
    private function setPropertyValue(array $path, $object, $value)
    {
        $property = array_shift($path);
        if (count($path) === 0) {
            $object->$property = $value;
        } elseif (property_exists($object, $property)) {
            $this->setPropertyValue($path, $object->$property, $value);
        } else {
            $object->$property = new stdClass();
            $this->setPropertyValue($path, $object->$property, $value);
        }
    }
}
