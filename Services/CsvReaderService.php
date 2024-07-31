<?php

namespace Services;

use Exceptions\InvalidFileException;

class CsvReaderService
{
    private $handle;
    private bool $endOfFile = true;
    private string $filename;
    private array $headers = [];
    private string $separator = ',';
    private string $enclosure = '"';


    public function openFile($file): void
    {
        $this->filename = app_root() . config('csv.folders.input') . $file;

        throw_if(
            !file_exists($this->filename),
            new InvalidFileException("File {$this->filename} does not exist")
        );

        $this->handle = fopen($this->filename, 'r');
        throw_if(
            !$this->handle,
            new InvalidFileException("Error while opening file {$this->filename}")
        );

        $this->endOfFile = false;
        $this->headers = array_map('strtolower', fgetcsv($this->handle));
    }

    public function endOfFile(): bool
    {
        return $this->endOfFile;
    }

    public function getRow(): array|bool
    {
        $row = $this->readRow();
        if (!$row) {
            $this->endOfFile = true;
            fclose($this->handle);
            $this->handle = null;

            return false;
        }

        return array_combine($this->headers, $row);
    }

    public function closeFile(): void
    {
        if ($this->handle) {
            fclose($this->handle);
            $this->handle = null;
        }
    }

    private function readRow(): array|bool
    {
        $row = fgetcsv(
            stream: $this->handle,
            separator: $this->separator,
            enclosure: $this->enclosure,
        );

        throw_if(
            is_null($row),
            new InvalidFileException("Error while accessing file {$this->filename}")
        );

        return $row;
    }
}