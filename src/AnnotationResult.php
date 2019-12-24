<?php

namespace Orbiter\AnnotationsUtil;

/**
 * @package Orbiter\AnnotationsUtil
 */
class AnnotationResult {
    protected $class = '';
    protected $method = '';
    protected $property = '';

    protected $is_static;
    protected $is_private;

    protected $annotation;

    public function setClass($class) {
        $this->class = $class;
    }

    public function getClass() {
        return $this->class;
    }

    public function setMethod($method) {
        $this->method = $method;
    }

    public function getMethod() {
        return $this->method;
    }

    public function setProperty($property) {
        $this->property = $property;
    }

    public function getProperty() {
        return $this->property;
    }

    public function setStatic($static) {
        $this->is_static = $static;
    }

    public function getStatic() {
        return $this->is_static;
    }

    public function setPrivate($private) {
        $this->is_private = $static;
    }

    public function getPrivate() {
        return $this->is_private;
    }

    /**
     * @param \object $annotation
     */
    public function setAnnotation($annotation) {
        $this->annotation = $annotation;
    }

    /**
     * @return \object
     */
    public function getAnnotation() {
        return $this->annotation;
    }
}
