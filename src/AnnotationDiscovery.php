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

    protected $discoverers = [];

    protected $discovered = [];

    /**
     * AnnotationDiscovery constructor.
     *
     * @param \Orbiter\AnnotationsUtil\CodeInfo $code_info
     */
    public function __construct(CodeInfo $code_info) {
        $this->code_info = $code_info;
    }

    public function addDiscover($annotation) {
        $this->discoverers[$annotation] = true;
    }

    public function setDiscovered(array $discovered) {
        return $this->discovered = $discovered;
    }

    public function getDiscovered() {
        return $this->discovered;
    }

    public function discoverByAnnotation($info_group) {
        $annotated = $this->code_info->getClassNames($info_group);

        foreach($annotated as $annotated_class) {
            $class_annotation = AnnotationsUtil::getClassAnnotations($annotated_class);
            foreach($class_annotation as $annotation_class => $class_anno) {
                if(isset($this->discoverers[$annotation_class])) {
                    $this->discovered[$annotation_class][] = [
                        'class' => $annotated_class,
                        'annotation' => $class_anno,
                    ];
                }
            }
        }

        $annotated_methods = $this->code_info->getClassMethods($info_group);
        foreach($annotated_methods as $class_name => $annotated_class_methods) {
            if(!is_array($annotated_class_methods)) {
                continue;
            }
            $methods = [];
            array_push($methods, ...$annotated_class_methods['public']);
            array_push($methods, ...$annotated_class_methods['static']);
            foreach($methods as $method) {
                $method_annotation = AnnotationsUtil::getMethodAnnotations($class_name, $method);
                foreach($method_annotation as $annotation_class => $method_anno) {
                    if(isset($this->discoverers[$annotation_class])) {
                        $this->discovered[$annotation_class][] = [
                            'class' => $class_name,
                            'method' => $method,
                            'annotation' => $method_anno,
                        ];
                    }
                }
            }
        }
    }
}
