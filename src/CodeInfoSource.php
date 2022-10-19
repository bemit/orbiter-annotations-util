<?php

namespace Orbiter\AnnotationsUtil;

class CodeInfoSource {
    /**
     * @var string[]
     */
    protected array $extensions = [];
    /**
     * @var string[]
     */
    protected array $flags = [];

    protected string $folder_to_parse;

    public function __construct(
        string $folder_to_parse,
        array  $flags,
        array  $extensions = ['php'],
    ) {
        $this->folder_to_parse = $folder_to_parse;
        $this->flags = $flags;
        $this->extensions = $extensions;
    }

    public function getFolderToParse(): string {
        return $this->folder_to_parse;
    }

    public function getExtensions(): array {
        return $this->extensions;
    }

    public function getFlags(): array {
        return $this->flags;
    }
}
