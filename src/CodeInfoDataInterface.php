<?php

namespace Orbiter\AnnotationsUtil;

use PhpParser\NodeVisitor;

/**
 * Data Handling Object Interface
 *
 * Describes a class that parsed ans stores the static code analyze result.
 *
 * @package Orbiter\AnnotationsUtil
 */
interface CodeInfoDataInterface extends NodeVisitor {
    /**
     * @return array
     */
    public function getClassNames(): array;
}
