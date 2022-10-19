<?php

namespace Orbiter\AnnotationsUtil;

use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;

class CodeAnalyzer {
    /**
     * Analyze code and get the classes out of each files contents in a folder, recursive
     *
     * @param string $path
     * @param array $source_info
     * @return CodeInfoDataInterface|null
     */
    public function parseFolder(string $path, array $source_info): ?CodeInfoDataInterface {
        $scanned_dir = [];
        if(!is_dir($path)) {
            return null;
        }
        $info_data = new CodeInfoData();
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);

        foreach($iterator as $file) {
            if($file->isFile() && in_array($file->getExtension(), $source_info['extensions'], true)) {
                $scanned_dir[] = $file->getPathname();
            }
        }

        foreach($scanned_dir as $php_file) {
            // todo: here could be a good place for a root-folder independent `already scanned` cache check
            if(is_file($php_file)) {
                $info_data = $this->analyzeCode($info_data, file_get_contents($php_file));
            }
        }

        return $info_data;
    }

    /**
     * Analyze file content/code and get info out of it.
     *
     * @param CodeInfoDataInterface $info_data
     * @param string $code
     * @return CodeInfoDataInterface
     */
    protected function analyzeCode(CodeInfoDataInterface $info_data, string $code): CodeInfoDataInterface {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        $ast = $parser->parse($code);

        $nameResolver = new NameResolver();
        $traverser = new NodeTraverser();
        $traverser->addVisitor($nameResolver);
        $traverser->addVisitor($info_data);
        $traverser->traverse($ast);

        return $info_data;
    }
}
