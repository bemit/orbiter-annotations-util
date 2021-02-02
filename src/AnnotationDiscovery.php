<?php

namespace Orbiter\AnnotationsUtil;

/**
 * Registry, Discovery and Announcement for Annotations
 *
 * @package Orbiter\AnnotationsUtil
 */
class AnnotationDiscovery {

    /**
     * @var \Orbiter\AnnotationsUtil\CodeInfo
     */
    protected $code_info;

    /**
     * @var \Orbiter\AnnotationsUtil\AnnotationResult
     */
    protected $discovered = [];

    /**
     * @var \Orbiter\AnnotationsUtil\AnnotationResultClass[]
     */
    protected $discovered_classes = [];

    /**
     * @var \Orbiter\AnnotationsUtil\AnnotationResultMethod[]
     */
    protected $discovered_methods = [];

    /**
     * @var \Orbiter\AnnotationsUtil\AnnotationResultProperty[]
     */
    protected $discovered_properties = [];

    protected AnnotationReader $reader;

    public function __construct(CodeInfo $code_info, AnnotationReader $reader) {
        $this->code_info = $code_info;
        $this->reader = $reader;
    }

    public function setDiscovered(array $discovered) {
        return $this->discovered = $discovered;
    }

    /**
     * @return \Orbiter\AnnotationsUtil\AnnotationResultClass[]|\Orbiter\AnnotationsUtil\AnnotationResultMethod[]|\Orbiter\AnnotationsUtil\AnnotationResultProperty[]
     */
    public function getAll(): array {
        return $this->discovered;
    }

    /**
     * @param $annotation_class
     *
     * @return \Orbiter\AnnotationsUtil\AnnotationResult[]
     */
    public function getDiscovered($annotation_class) {
        if(!isset($this->discovered[$annotation_class])) {
            return [];
        }

        return $this->discovered[$annotation_class];
    }

    /**
     * @param $class
     * @param $annotation_class
     *
     * @return \Orbiter\AnnotationsUtil\AnnotationResultMethod[]
     */
    public function getDiscoveredMethods($class, $annotation_class) {
        if(is_object($class)) {
            $class = get_class($class);
        }

        if(!isset($this->discovered_methods[$class][$annotation_class])) {
            return [];
        }

        return $this->discovered_methods[$class][$annotation_class];
    }

    /**
     * @param $class
     * @param $annotation_class
     *
     * @return \Orbiter\AnnotationsUtil\AnnotationResultProperty[]
     */
    public function getDiscoveredProperties($class, $annotation_class) {
        if(is_object($class)) {
            $class = get_class($class);
        }

        if(!isset($this->discovered_properties[$class][$annotation_class])) {
            return [];
        }

        return $this->discovered_properties[$class][$annotation_class];
    }

    public function discoverByAnnotation($info_group) {
        $annotated = $this->code_info->getClassNames($info_group);

        foreach($annotated as $annotated_class) {
            $class_annotations = $this->reader->getClassAnnotations($annotated_class);
            foreach($class_annotations as $annotation_class => $annotation) {
                $res = new AnnotationResultClass();
                $res->setClass($annotated_class);
                $res->setAnnotation($annotation);
                $this->discovered_classes[$annotated_class] = $this->discovered[$annotation_class][] = $res;
            }

            $methods = CachedReflection::getMethods($annotated_class);
            foreach($methods as $method) {
                $method_annotations = $this->reader->getMethodAnnotations($annotated_class, $method->name);
                foreach($method_annotations as $annotation_class => $annotation) {
                    $res = new AnnotationResultMethod();
                    $res->setClass($annotated_class);
                    $res->setMethod($method->name);
                    $res->setAnnotation($annotation);
                    $res->setStatic($method->isStatic());
                    $res->setPrivate($method->isPrivate());
                    $this->discovered_methods[$annotated_class][$annotation_class][] = $this->discovered[$annotation_class][] = $res;
                }
            }

            $properties = CachedReflection::getProperties($annotated_class);
            foreach($properties as $property) {
                $property_annotations = $this->reader->getPropertyAnnotations($annotated_class, $property->name);
                foreach($property_annotations as $annotation_class => $annotation) {
                    $res = new AnnotationResultProperty();
                    $res->setClass($annotated_class);
                    $res->setProperty($property->name);
                    $res->setAnnotation($annotation);
                    $res->setStatic($property->isStatic());
                    $res->setPrivate($property->isPrivate());
                    $this->discovered_properties[$annotated_class][$annotation_class][] = $this->discovered[$annotation_class][] = $res;
                }
            }
        }
    }
}
