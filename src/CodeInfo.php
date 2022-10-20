<?php

namespace Orbiter\AnnotationsUtil;

/**
 * Static Code Analyze Helper
 */
class CodeInfo {
    /**
     * absolute path to cache file
     */
    protected ?string $file_cache;

    /**
     * @var CodeInfoSource[]
     */
    protected array $sources_to_parse = [];
    /**
     * @var CodeInfoData[]
     */
    protected array $info_data = [];
    /**
     * @var int[][] `key => [ &value ]` = `FLAG => [ &CodeInfoData, &CodeInfoData ]`
     */
    protected array $info_data_flag_refs = [];
    /**
     * @var string[]
     */
    protected array $flags = [];

    protected CodeAnalyzer $analyzer;

    /**
     * @param CodeAnalyzer $analyzer
     * @param string|null $file_cache
     */
    public function __construct(
        CodeAnalyzer $analyzer,
        ?string      $file_cache = null,
    ) {
        $this->analyzer = $analyzer;
        $this->file_cache = $file_cache === null ? null :
            (str_ends_with($file_cache, '.php') ? $file_cache : $file_cache . '.php');
    }

    /**
     * @param string $file_cache
     */
    public function enableFileCache(string $file_cache): void {
        $this->file_cache = $file_cache;
    }

    public function defineSource(CodeInfoSource $source): void {
        $this->sources_to_parse[] = $source;
    }

    /**
     * Uses the parsed class data and returns their names
     *
     * @param string[] ...$flag
     * @return string[]
     */
    public function getClassNames(string ...$flag) {
        $refs = array_reduce($flag, fn($frefs, $f) => [...$frefs, ...$this->info_data_flag_refs[$f] ?? []], []);
        /**
         * @var $r int
         */
        return array_values(array_unique(array_reduce($refs, fn($all, $r) => [...$all, ...$this->info_data[$r]->getClassNames()], [])));
    }

    /**
     * @return string[]
     */
    public function getFlags(): array {
        return $this->flags;
    }

    protected function shouldCache(): bool {
        return $this->file_cache !== null;
    }

    protected function hasCache(): bool {
        $dir = dirname($this->file_cache);
        if(!is_dir($dir) && !mkdir($dir, 0775, true)) {
            throw new \RuntimeException('file_cache dir can not be created: ' . $dir);
        }
        return is_file($this->file_cache);
    }

    /**
     * Process the defined parsings or re-initiate data from cache
     */
    public function process(): void {
        if($this->shouldCache() && $this->hasCache()) {
            $this->restoreCache();

            return;
        }

        $folders = [];
        foreach($this->sources_to_parse as $source_to_parse) {
            $folder = $source_to_parse->getFolderToParse();
            if(!isset($folders[$folder])) {
                $folders[$folder] = [
                    'extensions' => [],
                    'flags' => [],
                ];
            }
            $folders[$folder]['extensions'] = [...$folders[$folder]['extensions'], ...$source_to_parse->getExtensions()];
            $folders[$folder]['flags'] = [...$folders[$folder]['flags'], ...$source_to_parse->getFlags()];
        }

        foreach($folders as $folder => $folder_sources) {
            $folder_sources['extensions'] = array_values(array_unique($folder_sources['extensions']));
            $folder_sources['flags'] = array_values(array_unique($folder_sources['flags']));
            $code_data = $this->analyzer->parseFolder($folder, $folder_sources);
            $this->info_data[] = $code_data;
            $i = count($this->info_data) - 1;
            foreach($folder_sources['flags'] as $flag) {
                if(!isset($this->info_data_flag_refs[$flag])) {
                    $this->info_data_flag_refs[$flag] = [];
                }
                $this->info_data_flag_refs[$flag][] = $i;
            }
            $this->flags = [...$this->flags, ...$folder_sources['flags']];
        }
        $this->flags = array_values(array_unique($this->flags));

        if($this->shouldCache()) {
            file_put_contents(
                $this->file_cache,
                "<?php\n\nreturn " .
                var_export([
                    'info_data' => $this->info_data,
                    'info_data_flag_refs' => $this->info_data_flag_refs,
                ], true) . ';',
            );
        }
    }

    protected function restoreCache(): void {
        $cache_content = require($this->file_cache);
        $this->info_data_flag_refs = $cache_content['info_data_flag_refs'];
        $this->info_data = $cache_content['info_data'];
    }
}
