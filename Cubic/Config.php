<?php

namespace Cubic;

class Config
{
    private ?array $config = null;

    public function __construct(private File $file)
    {
    }

    public function get($key): mixed
    {
        if (!$this->config) {
            $this->readConfig();
        }

        return array_dot($this->config, $key);
    }

    private function readConfig(): void
    {
        $this->config = [];

        $configFiles = $this->file->search('Config', 'php');
        foreach ($configFiles as $file) {
            $key = strtolower($file['file_no_extension']);
            $this->config = array_merge(
                $this->config,
                [$key => include(app_root() . $file['path']  . DIRECTORY_SEPARATOR . $file['file'])]
            );
        }
    }
}