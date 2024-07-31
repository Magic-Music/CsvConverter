<?php

namespace Cubic;

use Cubic\Cli\Cli;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class File
{
    private string $rootFolder;

    public function __construct()
    {
        $this->rootFolder = dirname(__dir__) . DIRECTORY_SEPARATOR;
    }

    public function rootFolder(): string
    {
        return $this->rootFolder ?? DIRECTORY_SEPARATOR;
    }

    public function search(string $folder, string|array|null $extensions = null): array
    {
        $files = [];
        $extensions = array_wrap($extensions);

        $iterator = new RecursiveDirectoryIterator($this->rootFolder . $folder);
        foreach(new RecursiveIteratorIterator($iterator) as $file) {
            if (!$extensions || in_array($file->getExtension(), $extensions)) {
                $files[] = [
                    'path' => str_replace($this->rootFolder, '', $file->getPath()),
                    'file' => $file->getFilename(),
                    'file_no_extension' => str_replace('.' . $file->getExtension(), '', $file->getFilename()),
                ];
            }
        }

        return $files;
    }
}