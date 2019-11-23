<?php

namespace Orbiter\AnnotationsUtil;

use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 *
 * @package Orbiter\AnnotationsUtil
 */
class CodeInfo {
    /**
     * @var string|null when string absolute path to cache file
     */
    protected $file_cache;

    protected $dirs_to_parse = [];

    /**
     * @var \Orbiter\AnnotationsUtil\CodeInfoData
     */
    protected $data;

    public function __construct(CodeInfoData $data_obj = null) {
        $this->data = $data_obj ?: new CodeInfoData();
    }

    /**
     * @param string $file_cache
     */
    public function enableFileCache($file_cache) {
        $this->file_cache = $file_cache;
    }

    /**
     * @param string $group
     * @param string[] $dirs
     */
    public function defineDirs($group, $dirs) {
        $this->dirs_to_parse[$group] = $dirs;
    }

    /**
     * Uses the parsed class data and returns their names
     *
     * @param $group
     *
     * @return array
     */
    public function getClassNames($group) {
        return $this->data->getClassNames($group);
    }

    /**
     * Analyze code and get the classes out of the file contents of a folder
     *
     * @param $group
     * @param array $dirs
     */
    protected function parseDirs($group, $dirs) {
        $scanned_dir = [];
        foreach($dirs as $dir) {
            if(!is_dir($dir)) {
                continue;
            }
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::SELF_FIRST);

            foreach($iterator as $file) {
                if($file->isFile() && in_array($file->getExtension(), ['php', 'php5'])) {
                    $scanned_dir[] = $file->getPathname();
                }
            }
        }

        foreach($scanned_dir as $php_file) {
            if(is_file($php_file)) {
                $this->analyzeCode($group, file_get_contents($php_file));
            }
        }
    }

    /**
     * @return \Orbiter\AnnotationsUtil\CodeInfoData the parsed classes
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Analyze file content/code and get info out of it.
     * - currently only class names
     * - returns all full qualified class names
     *
     * @param string $group
     * @param string $code
     */
    protected function analyzeCode($group, $code) {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        $ast = $parser->parse($code);// this may throw

        $nameResolver = new NameResolver;
        $traverser = new NodeTraverser();
        $traverser->addVisitor($nameResolver);

        $traverser->addVisitor(new class($this->data, $group) extends NodeVisitorAbstract {
            /**
             * @var \Orbiter\AnnotationsUtil\CodeInfoData
             */
            protected $data;
            /**
             * @var string
             */
            protected $group;

            public function __construct(&$data, $group) {
                $this->data =& $data;
                $this->group = $group;
            }

            public function enterNode(Node $node) {
                $this->data->parse($this->group, $node);
            }
        });

        $traverser->traverse($ast);
    }

    /**
     * Process the defined parsings or re-initiate data from cache
     */
    public function process() {
        if(
            !$this->file_cache || (
                $this->file_cache && !is_file($this->file_cache) && (
                    is_dir(dirname($this->file_cache)) ||
                    (!is_dir(dirname($this->file_cache)) && mkdir(dirname($this->file_cache), 0777, true) && is_dir(dirname($this->file_cache)))
                )
            )
        ) {
            foreach($this->dirs_to_parse as $group => $to_load_dirs) {
                $this->parseDirs($group, $to_load_dirs);
            }
            if($this->file_cache) {
                file_put_contents($this->file_cache, json_encode($this->data));
            }
        } else if(is_file($this->file_cache)) {
            $cache_content = file_get_contents($this->file_cache);
            $cache_content_parsed = json_decode($cache_content, true);

            $this->mapCache($cache_content_parsed);
        }
    }

    protected function mapCache($cache) {
        if(isset($cache['classes'])) {
            $this->data->classes = $cache['classes'];
        }
        if(isset($cache['classes_simplified'])) {
            $this->data->classes_simplified = $cache['classes_simplified'];
        }
    }
}
