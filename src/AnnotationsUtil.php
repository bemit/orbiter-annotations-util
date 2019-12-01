<?php

namespace Orbiter\AnnotationsUtil;

use Doctrine\Common\Annotations;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache;
use ScaleUpStack\Reflection\Reflection;

/**
 * Helper Class to speed up setup of Doctrine\Annotation and ease the usage.
 *
 * - uses cached reflection by `scaleupstack/reflection`
 *
 * @package Orbiter\AnnotationsUtil
 */
class AnnotationsUtil {

    /**
     * @var Reader
     */
    protected static $reader_instance;

    public static function useReader(Reader $reader_instance) {
        static::$reader_instance = $reader_instance;
    }

    /**
     * @return Reader
     */
    public static function reader() {
        return static::$reader_instance;
    }

    /**
     * @param string $cache
     * @param Cache\Cache|null $cache_obj
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @return \Doctrine\Common\Annotations\Reader
     */
    public static function createReader($cache, $cache_obj = null) {
        $reader = new Annotations\IndexedReader(new Annotations\AnnotationReader());

        if(!$cache && null === $cache_obj) {
            return new Annotations\CachedReader(
                new Annotations\IndexedReader(new Annotations\AnnotationReader()),
                $cache_obj ?: new Cache\ArrayCache()
            );
        }

        return new Annotations\CachedReader(
            new Annotations\IndexedReader(new Annotations\AnnotationReader()),
            $cache_obj ?: new Cache\ChainCache([
                new Cache\ArrayCache(),
                new Cache\PhpFileCache($cache),
            ])
        );
    }

    /**
     * @param string|object $class
     *
     * @return \ReflectionClass
     */
    public static function getClass($class) {
        if(is_string($class)) {
            return Reflection::classByName($class);
        }
        return Reflection::classByObject($class);
    }

    /**
     * @param string|object $class
     *
     * @return array|\ReflectionMethod[]
     */
    public static function getMethods($class) {
        if(is_string($class)) {
            return Reflection::allMethodsOfClass($class);
        }
        return Reflection::allMethodsOfObject($class);
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

    /**
     * @param $class
     * @param $property
     * @param $annotationName
     *
     * @return object|null
     */
    public static function getPropertyAnnotation($class, $property, $annotationName) {
        return static::$reader_instance->getPropertyAnnotation(static::getProperty($class, $property), $annotationName);
    }

    /**
     * @param $class
     * @param $property
     *
     * @return array
     */
    public static function getPropertyAnnotations($class, $property) {
        return static::$reader_instance->getPropertyAnnotations(static::getProperty($class, $property));
    }

    /**
     * @param $class
     * @param $annotation_name
     *
     * @return object|null
     */
    public static function getClassAnnotation($class, $annotation_name) {
        return static::$reader_instance->getClassAnnotation(static::getClass($class), $annotation_name);
    }

    /**
     * @param $class
     *
     * @return array
     */
    public static function getClassAnnotations($class) {
        return static::$reader_instance->getClassAnnotations(static::getClass($class));
    }

    /**
     * @param $class
     * @param $method
     * @param $annotation_name
     *
     * @return object|null
     */
    public static function getMethodAnnotation($class, $method, $annotation_name) {
        return static::$reader_instance->getMethodAnnotation(static::getMethod($class, $method), $annotation_name);
    }

    /**
     * @param $class
     * @param $method
     *
     * @return array
     */
    public static function getMethodAnnotations($class, $method) {
        return static::$reader_instance->getMethodAnnotations(static::getMethod($class, $method));
    }

    /**
     * Registers a PSR-4 autoloading namespace for annotations, as the default only includes an PSR-0 compatible
     *
     * @param $namespace
     * @param $dir
     */
    public static function registerPsr4Namespace($namespace, $dir) {
        Annotations\AnnotationRegistry::registerLoader(static function($class) use ($namespace, $dir) {
            if(strpos($class, $namespace) === 0) {
                // be sure to that it fails silently
                $file = rtrim($dir, "/\\") . '/' . str_replace("\\", DIRECTORY_SEPARATOR, str_replace($namespace, '', $class)) . '.php';

                if(file_exists($file)) {
                    require_once $file;

                    return true;
                }
                // it must fail silently
            }
        });
    }
}
