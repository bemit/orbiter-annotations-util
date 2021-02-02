<?php

namespace Orbiter\AnnotationsUtil;

use Doctrine\Common\Annotations\Reader;

/**
 * Helper Class to for Doctrine\Annotation, using cached reflections
 *
 * @package Orbiter\AnnotationsUtil
 */
class AnnotationReader {

    /**
     * @var Reader
     */
    protected Reader $reader;

    public function __construct(Reader $reader) {
        $this->reader = $reader;
    }

    /**
     * @param $class
     * @param $property
     * @param $annotationName
     *
     * @return object|null
     */
    public function getPropertyAnnotation($class, $property, $annotationName) {
        return $this->reader->getPropertyAnnotation(CachedReflection::getProperty($class, $property), $annotationName);
    }

    /**
     * @param $class
     * @param $property
     *
     * @return array
     */
    public function getPropertyAnnotations($class, $property) {
        return $this->reader->getPropertyAnnotations(CachedReflection::getProperty($class, $property));
    }

    /**
     * @param $class
     * @param $annotation_name
     *
     * @return object|null
     */
    public function getClassAnnotation($class, $annotation_name) {
        return $this->reader->getClassAnnotation(CachedReflection::getClass($class), $annotation_name);
    }

    /**
     * @param $class
     *
     * @return array
     */
    public function getClassAnnotations($class) {
        return $this->reader->getClassAnnotations(CachedReflection::getClass($class));
    }

    /**
     * @param $class
     * @param $method
     * @param $annotation_name
     *
     * @return object|null
     */
    public function getMethodAnnotation($class, $method, $annotation_name) {
        return $this->reader->getMethodAnnotation(CachedReflection::getMethod($class, $method), $annotation_name);
    }

    /**
     * @param $class
     * @param $method
     *
     * @return array
     */
    public function getMethodAnnotations($class, $method) {
        return $this->reader->getMethodAnnotations(CachedReflection::getMethod($class, $method));
    }
}
