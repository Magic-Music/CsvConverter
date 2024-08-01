<?php

namespace Services;

use Exceptions\InvalidConverterException;
use Resources\Conversion;

class CsvConversionService
{
    private Conversion $converterScript;
    private string $inputFile;
    private string $outputFile;

    public function __construct(
        private CsvReaderService $csvReaderService,
        private FileWriterService $fileWriterService,
    )
    {
    }

    public function setConverterScript(string $file): void
    {
        $class = "\Converters\\{$file}";

        throw_if(
            !class_exists($class),
            new InvalidConverterException("Invalid converter script $file")
        );

        $this->converterScript = create($class);

        $this->setInputFile($this->converterScript->config->inputFile);
        $this->setOutputFile($this->converterScript->config->outputFile);
    }

    public function setInputFile(string $file): void
    {
        $this->inputFile = $file;
    }

    public function setOutputFile(string $file): void
    {
        $this->outputFile = $file;
    }

    public function enableTestMode(int $outputLines): void
    {
        $this->fileWriterService->testMode($outputLines);
    }

    public function run(): void
    {
        $this->csvReaderService->openFile($this->inputFile);
        $this->fileWriterService->openFile($this->outputFile);

        $this->convertFile();

        $this->csvReaderService->closeFile();
        $this->fileWriterService->closeFile();
    }
    
    private function convertFile(): void
    {
        $continue = true;

        while ($continue && $row = $this->csvReaderService->getRow())
        {
            $continue = $this->fileWriterService->write($this->converterScript->convertRow($row));
        }
    }
}