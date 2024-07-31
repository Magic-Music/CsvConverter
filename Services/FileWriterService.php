<?php

namespace Services;

use Cubic\Cli\Cli;
use Exceptions\InvalidFileException;

class FileWriterService
{
    private $handle;
    private string $filename;
    private bool $testMode = false;
    private int $maxTestModeLines = 1;
    private int $testModeLineCounter = 0;

    public function openFile(string $file): void
    {
        $this->filename = app_root() . config('csv.folders.output') . $file;

        if ($this->testMode) {
            Cli::log("Output file would be {$this->filename}\n");
            return;
        }

        $this->handle = fopen($this->filename, 'w');
        throw_if(
            !$this->handle,
            new InvalidFileException("Error while trying to open file for writing: {$this->filename}")
        );
    }

    public function testMode(int $maximumLines = 1): void
    {
        $this->testMode = true;
        $this->maxTestModeLines = $maximumLines;
        Cli::log("Maximum output lines: $maximumLines");
    }

    public function closeFile(): void
    {
        if ($this->testMode) {
            Cli::log("Output file closed\n");
            return;
        }

        fclose($this->handle);
    }

    public function write(string|array $line): bool
    {
        $outputLine = array_join('', $line);

        if ($this->testMode) {
            Cli::log(trim($outputLine));
            if (++$this->testModeLineCounter >= $this->maxTestModeLines) {
                Cli::log("\nMaximum output lines reached");
                return false;
            }

            return true;
        }

        try {
            fputs($this->handle, array_join('', $line));
        } catch (\Exception $e) {
            throw new InvalidFileException("Error while writing to file {$this->filename}: {$e->getMessage()}");
        }

        return true;
    }
}