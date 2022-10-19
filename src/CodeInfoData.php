<?php

namespace Orbiter\AnnotationsUtil;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Data Handling Object, result is cached and re-initiated in CodeInfo
 *
 * @package Orbiter\AnnotationsUtil
 */
class CodeInfoData extends NodeVisitorAbstract implements CodeInfoDataInterface, \JsonSerializable {
    protected $classes_simplified = [];

    public static function __set_state(array $cached) {
        $next = new self();
        if(isset($cached['classes_simplified'])) {
            $next->classes_simplified = $cached['classes_simplified'];
        }
        return $next;
    }

    public function jsonSerialize(): array {
        return [
            'classes_simplified' => $this->classes_simplified,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getClassNames(): array {
        return $this->classes_simplified;
    }

    /**
     * @inheritdoc
     */
    public function enterNode(Node $node) {
        $this->parse($node);
    }

    /**
     * Used in the NodeWalker, so easy to extend the code info parser at all
     *
     * @param Node $node
     */
    public function parse(Node $node): void {
        if($node instanceof Node\Stmt\Class_ && $node->namespacedName) {
            $this->parseClass($node);
        }
    }

    /**
     * Add the class data, a simplified version of class names each classes methods and properties
     *
     * @param \PhpParser\Node\Stmt\Class_ $node
     */
    protected function parseClass(Node\Stmt\Class_ $node) {
        $simple = $this->simplifyClassName($node->namespacedName);

        if($simple !== null) {
            $this->classes_simplified[] = $simple;
        }
    }

    /**
     * Uses the parsed class data and returns their names
     */
    protected function simplifyClassName(Node\Name $class): ?string {
        if(!empty($class->parts)) {
            return implode('\\', $class->parts);
        }
        return null;
    }
}
