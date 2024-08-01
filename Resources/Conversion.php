<?php

namespace Resources;

use Entities\ConversionConfig;
use Exceptions\InvalidConverterException;

abstract class Conversion
{
    //Override these in your conversion script if required
    private const DEFAULT_CONFIG = [
        'inputFile' => 'input.csv',
        'outputFile' => 'output.txt',
        'startDelimiter' => ':~',
        'endDelimiter' => '~:',
    ];

    private array $replacers = [];

    /**
     * Set conversion output statements
     * Update configurable conversion script parameters
     */
    abstract protected function configure(): void;

    public function __construct(public ConversionConfig $config)
    {
        $this->config->set(self::DEFAULT_CONFIG);
        $this->configure();

        throw_if(
            !$this->config->statements,
            new InvalidConverterException("No output statements set on conversion script")
        );

        $this->parsePlaceholders();
    }

    public function convertRow(array $row): string
    {
        $output = '';

        foreach ($this->config->statements as $statement) {
            foreach ($this->replacers as $replacer => $method) {
                $replaceText = $method
                    ? $this->$replacer($row)
                    : $row[strtolower($replacer)];

                $statement = str_replace(
                    $this->config->startDelimiter . $replacer . $this->config->endDelimiter,
                    $replaceText,
                    $statement
                );
            }
            $output .= $statement;
        }

        return $output;
    }

    private function parsePlaceholders(): void
    {
        foreach ($this->config->statements as $statement) {
            do {
                $replacer = $this->getNextReplacer($statement);
                if (!$replacer) {
                    break;
                }

                $this->replacers[$replacer] = method_exists($this, $replacer);
            } while (true);
        }
    }

    private function getNextReplacer(string &$statement): string|false
    {
        $start = strpos($statement, $this->config->startDelimiter) + 2;
        $end = strpos($statement, $this->config->endDelimiter);
        if (!$start || !$end) {
            return false;
        }

        $replacer = substr($statement, $start, $end - $start);
        $statement = str_replace($this->config->startDelimiter . $replacer . $this->config->endDelimiter, '', $statement);

        return $replacer;
    }
}