<?php

namespace Resources;

abstract class Conversion
{
    //Override these in your conversion script if required
    protected array $delimiters = [':~', '~:'];

    private array $row;
    private array $replacers = [];

    abstract protected function getOutputStatements(): string|array;

    public function __construct()
    {
        /**
         * Build an array of all replacers in all output statements
         * Key = replacer. Value = true if there is a conversion method
         */
        $this->parsePlaceholders();
    }

    public function convertRow(array $row): string
    {
        $this->row = $row;
        $output = '';

        foreach (array_wrap($this->getOutputStatements()) as $statement) {
            foreach ($this->replacers as $replacer => $method) {
                $replaceText = $method
                    ? $this->$replacer($row)
                    : $row[strtolower($replacer)];

                $statement = str_replace(
                    $this->delimiters[0] . $replacer . $this->delimiters[1],
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
        foreach (array_wrap($this->getOutputStatements()) as $statement) {
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
        $start = strpos($statement, $this->delimiters[0]) + 2;
        $end = strpos($statement, $this->delimiters[1]);
        if (!$start || !$end) {
            return false;
        }

        $replacer = substr($statement, $start, $end - $start);
        $statement = str_replace($this->delimiters[0] . $replacer . $this->delimiters[1], '', $statement);

        return $replacer;
    }


}