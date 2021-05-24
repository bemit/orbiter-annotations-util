<?php

namespace Orbiter\AnnotationsUtil;

use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * Static Code Analyze Helper
 *
 * @todo add already-parsed file cache or even already parsed folder cache
 * @todo add control of recursive scan of dir, editable per dir per group
 *
 * @package Orbiter\AnnotationsUtil
 */
class CodeInfo {
    /**
     * @var string|null when string absolute path to cache file
     */
    protected ?string $file_cache;

    protected array $dirs_to_parse = [];

    /**
     * @var \Orbiter\AnnotationsUtil\CodeInfoDataInterface
     */
    protected CodeInfoDataInterface $data;

    /**
     * @var array keys contains the extensions that should be used
     */
    protected array $extensions = [
        'php' => '',
    ];

    /**
     * @param string|null $file_cache for caching
     * @param \Orbiter\AnnotationsUtil\CodeInfoDataInterface|null $data_obj overwrite $data_obj with your own
     */
    public function __construct($file_cache = null, CodeInfoDataInterface $data_obj = null) {
        $this->data = $data_obj ?? new CodeInfoData();
        $this->file_cache = $file_cache;
    }

    /**
     * @param string $file_cache
     */
    public function enableFileCache(string $file_cache) {
        $this->file_cache = $file_cache;
    }

    /**
     * Add an extension that should be processed, default includes `php`
     *
     * @param string $ext
     */
    public function addExtension(string $ext) {
        $this->extensions[$ext] = '';
    }

    /**
     * Removes an extension from the processing, default includes `php`
     *
     * @param string $ext
     */
    public function rmExtension(string $ext) {
        if(isset($this->extensions[$ext])) {
            unset($this->extensions[$ext]);
        }
    }

    /**
     * Define a group of dirs to parse, select result is saved based on the groups, one dir can be in multiple groups.
     *
     * @param string $group
     * @param string[] $dirs
     */
    public function defineDirs(string $group, array $dirs) {
        $this->dirs_to_parse[$group] = $dirs;
    }

    /**
     * Uses the parsed class data and returns their names
     *
     * @param $group
     *
     * @return array
     */
    public function getClassNames(string $group) {
        return $this->data->getClassNames($group);
    }

    /**
     * Analyze code and get the classes out of each files contents in a folder, recursive
     *
     * @param $group
     * @param array $dirs
     */
    protected function parseDirs(string $group, array $dirs) {
        $scanned_dir = [];
        foreach($dirs as $dir) {
            if(!is_dir($dir)) {
                continue;
            }
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::SELF_FIRST);

            foreach($iterator as $file) {
                if($file->isFile() && array_key_exists($file->getExtension(), $this->extensions)) {
                    $scanned_dir[] = $file->getPathname();
                }
            }
        }

        foreach($scanned_dir as $php_file) {
            // todo: here could be a good place for a `already scanned` cache check
            if(is_file($php_file)) {
                $this->analyzeCode($group, file_get_contents($php_file));
            }
        }
    }

    /**
     * Analyze file content/code and get info out of it.
     * - currently only class names
     * - returns all full qualified class names
     *
     * @param string $group
     * @param string $code
     */
    protected function analyzeCode(string $group, string $code) {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        $ast = $parser->parse($code);// this may throw

        $nameResolver = new NameResolver;
        $traverser = new NodeTraverser();
        $traverser->addVisitor($nameResolver);

        $traverser->addVisitor(new class($this->data, $group) extends NodeVisitorAbstract {
            /**
             * @var \Orbiter\AnnotationsUtil\CodeInfoDataInterface
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
     *
     * @throws \Orbiter\AnnotationsUtil\CodeInfoCacheFileException
     * @throws \JsonException
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
            // when no cache is on
            // or cache is on: and cache file doesn't exist and (dir exists or could be created)
            foreach($this->dirs_to_parse as $group => $to_load_dirs) {
                $this->parseDirs($group, $to_load_dirs);
            }

            if($this->file_cache) {
                file_put_contents($this->file_cache, json_encode($this->data, JSON_THROW_ON_ERROR));
            }
            return;
        }

        if(is_file($this->file_cache)) {
            // when file cache exists
            $cache_content = file_get_contents($this->file_cache);
            $cache_content_parsed = json_decode($cache_content, true, 512, JSON_THROW_ON_ERROR);

            $this->mapCache($cache_content_parsed);

            return;
        }

        throw new CodeInfoCacheFileException('Can not process dirs, cache file not creatable: ' . $this->file_cache);
    }

    /**
     * Get cached version into CodeInfoDataInterface implementing object
     *
     * @param array $cache array representation of this class
     */
    protected function mapCache(array $cache) {
        foreach($cache as $attr => $value) {
            $this->data->setAttribute($attr, $value);
        }
    }
}
