<?php

namespace Orbiter\AnnotationsUtil;

use PhpParser\Node;

/**
 * Data Handling Object, result is cached and re-initiated in CodeInfo
 *
 * @package Orbiter\AnnotationsUtil
 */
class CodeInfoData implements CodeInfoDataInterface {
    public $classes = [];
    public $classes_simplified = [];

    /**
     * Should be used to set any property with the cached version
     *
     * @param $attr
     * @param $val
     */
    public function setAttribute($attr, $val) {
        if(property_exists($this, $attr)) {
            $this->$attr = $val;
        }
    }

    /**
     * Returns the found full qualified class names in this group of folders
     *
     * @param string $group
     *
     * @return array
     */
    public function getClassNames($group) {
        return isset($this->classes_simplified[$group]) ? $this->classes_simplified[$group] : [];
    }

    /**
     * Used in the NodeWalker, so easy to extend the code info parser at all
     *
     * @param string $group
     * @param Node $node
     */
    public function parse($group, Node $node) {
        if($node instanceof Node\Stmt\Class_ && $node->namespacedName) {
            $this->parseClass($group, $node);
        }
    }

    /**
     * Add the class data, a simplified version of class names each classes methods and properties
     *
     * @param $group
     * @param \PhpParser\Node\Stmt\Class_ $node
     */
    protected function parseClass($group, Node\Stmt\Class_ $node) {
        if(!isset($this->classes[$group]) || !is_array($this->classes[$group])) {
            $this->classes[$group] = [];
        }
        $this->classes[$group][] = $node->namespacedName;

        $simple = $this->simplifyClasses($node->namespacedName);

        if($simple) {
            if(!isset($this->classes_simplified[$group]) || !is_array($this->classes_simplified[$group])) {
                $this->classes_simplified[$group] = [];
            }
            $this->classes_simplified[$group][] = $simple;
        }
    }

    /**
     * Uses the parsed class data and returns their names
     *
     * @param Node\Name $class
     *
     * @return string
     */
    protected function simplifyClasses(Node\Name $class) {
        if(!empty($class->parts)) {
            return implode('\\', $class->parts);
        }
        return null;
    }
}
