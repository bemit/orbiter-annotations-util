<?php

namespace Orbiter\AnnotationsUtil;

use PhpParser\Node;

/**
 * Data Handling Object Interface
 *
 * Describes a class that contains the static code analyze result,
 * either from automatic mapped from cache or live
 *
 * @todo add getAttribute, must call special implementations, when existing
 *
 * @package Orbiter\AnnotationsUtil
 */
interface CodeInfoDataInterface {

    /**
     * Should be used to set any property with the cached version
     *
     * @param string $attr
     * @param $val
     */
    public function setAttribute($attr, $val);

    /**
     * @param $group
     *
     * @return array
     */
    public function getClassNames($group);

    /**
     * Uses the parsed class data and returns their names
     *
     * @param Node\Name $class
     *
     * @return string
     */
    public function simplifyClasses(Node\Name $class);

    /**
     * Used in the NodeWalker, so easy to extend the code info parser at all
     *
     * @param string $group
     * @param Node $node
     */
    public function parse($group, Node $node);

    /**
     * Add the raw class data and a simplified version of class names
     *
     * @param string $group
     * @param Node\Name $class
     */
    public function addClassName($group, Node\Name $class);
}