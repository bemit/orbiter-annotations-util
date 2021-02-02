<?php

namespace Orbiter\AnnotationsUtil;

use PhpParser\Node;

/**
 * Data Handling Object Interface
 *
 * Describes a class that contains the static code analyze result,
 * automatic mapped either from cache or in-code
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
    public function setAttribute(string $attr, $val): void;

    /**
     * @param $group
     *
     * @return array
     */
    public function getClassNames(string $group): array;

    /**
     * Used in the NodeWalker, so easy to extend the code info parser at all
     *
     * @param string $group
     * @param Node $node
     */
    public function parse(string $group, Node $node): void;
}
