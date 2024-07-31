<?php

namespace Commands;

use Cubic\Cli\Command;
use Services\CsvConversionService;

class CsvConvert extends Command
{
    public string $command = "convert";
    public string $signature = "converter ?input ?output --test:t";

    public function __construct(private CsvConversionService $csvConversionService)
    {
    }

    public function handle()
    {
        $this->setOptionsOnConversionService();

        try {
            $this->csvConversionService->run();
            $this->log("Conversion complete!");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            exit(E_ERROR);
        }
    }

    private function setOptionsOnConversionService(): void
    {
        $this->csvConversionService->setConverterScript($this->argument('converter'));

        $input = $this->argument('input');
        if ($input) {
            $this->csvConversionService->setInputFile($input);
        }

        $output = $this->argument('output');
        if ($output) {
            $this->csvConversionService->setOutputFile($output);
        }

        $test = $this->option('test');
        if ($test) {
            $this->log("TEST MODE\n");
            $test = is_numeric($test) ? $test : 1;
            $this->csvConversionService->enableTestMode($test);
        }
    }
}