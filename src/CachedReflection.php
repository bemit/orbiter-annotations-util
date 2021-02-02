<?php

namespace Orbiter\AnnotationsUtil;

use ScaleUpStack\Reflection\Reflection;

/**
 * Cached reflections by `scaleupstack/reflection` with auto detection of object/class-name
 *
 * @package Orbiter\AnnotationsUtil
 */
class CachedReflection {
    /**
     * @param string|object $class
     *
     * @return \ReflectionClass
     */
    public function getClass($class) {
        if(is_string($class)) {
            return Reflection::classByName($class);
        }
        return Reflection::classByObject($class);
    }

    /**
     * @param string|object $class
     *
     * @return \ReflectionMethod[]
     */
    public static function getMethods($class) {
        if(is_string($class)) {
            return Reflection::allMethodsOfClass($class);
        }
        return Reflection::allMethodsOfObject($class);
    }

    /**
     * @param string|object $class
     * @param string $method
     *
     * @return \ReflectionMethod
     */
    public static function getMethod($class, $method) {
        if(is_string($class)) {
            return Reflection::methodOfClass($class, $method);
        }
        return Reflection::methodOfObject($class, $method);
    }

    /**
     * @param string|object $class
     *
     * @return \ReflectionProperty[]
     */
    public static function getProperties($class) {
        if(is_string($class)) {
            return Reflection::allPropertiesOfClass($class);
        }
        return Reflection::allPropertiesOfObject($class);
    }

    /**
     * @param string|object $class
     * @param string $property
     *
     * @return \ReflectionProperty
     */
    public static function getProperty($class, $property) {
        if(is_string($class)) {
            return Reflection::propertyOfClass($class, $property);
        }
        return Reflection::propertyOfObject($class, $property);
    }

    public static function getPropertyValue(object $object, string $property) {
        return Reflection::getPropertyValue($object, $property);
    }

    public static function getStaticPropertyValue(string $class_name, string $property) {
        return Reflection::getStaticPropertyValue($class_name, $property);
    }

    public static function setPropertyValue(object $object, string $property_name, $value) {
        Reflection::setPropertyValue($object, $property_name, $value);
    }

    public static function setStaticPropertyValue(string $object, string $property_name, $value) {
        Reflection::setStaticPropertyValue($object, $property_name, $value);
    }

    public static function invokeMethod(object $object, string $methodName, array $arguments) {
        return Reflection::invokeMethod($object, $methodName, $arguments);
    }

    public static function invokeStaticMethod(string $className, string $methodName, array $arguments) {
        return Reflection::invokeStaticMethod($className, $methodName, $arguments);
    }
}
