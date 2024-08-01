<?php

namespace Entities;

use Entities\Entity;

class ConversionConfig extends Entity
{
    public string $inputFile;
    public string $outputFile;
    public string $startDelimiter;
    public string $endDelimiter;
    public array $statements;
}