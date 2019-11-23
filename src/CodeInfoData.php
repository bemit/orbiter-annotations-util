<?php

namespace Orbiter\AnnotationsUtil;

use PhpParser\Node;

/**
 * Data Handling Object, result is cached and re-initiated in CodeInfo
 *
 * @package Orbiter\AnnotationsUtil
 */
class CodeInfoData {
    public $classes = [];
    public $classes_simplified = [];

    /**
     * @param $group
     *
     * @return array
     */
    public function getClassNames($group) {
        return $this->classes_simplified[$group] ?: [];
    }

    /**
     * Uses the parsed class data and returns their names
     *
     * @param $class
     *
     * @return string
     */
    public function simplifyClasses(Node\Name $class) {
        if(!empty($class->parts)) {
            return implode('\\', $class->parts);
        }
        return null;
    }

    /**
     * Used in the NodeWalker, so easy to extend the code info parser at all
     *
     * @param $group
     * @param $node
     */
    public function parse($group, $node) {
        if($node instanceof Node\Stmt\Class_ && $node->namespacedName) {
            $this->addClassName($group, $node->namespacedName);
        }
    }

    /**
     * Add the raw class data and a simplified version of class names
     *
     * @param $group
     * @param $class
     */
    public function addClassName($group, $class) {
        if(!is_array($this->classes[$group])) {
            $this->classes[$group] = [];
        }
        $this->classes[$group][] = $class;

        $simple = $this->simplifyClasses($class);

        if($simple) {
            if(!is_array($this->classes_simplified[$group])) {
                $this->classes_simplified[$group] = [];
            }
            $this->classes_simplified[$group][] = $simple;
        }
    }
}
