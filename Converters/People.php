<?php

namespace Converters;

use Resources\Conversion;

class People extends Conversion
{

    protected function configure(): void
    {
        $this->config->statements = [
            "INSERT INTO `people` VALUES(':~name~:', :~age~:, ':~city~:', :~lengthOfName~:);\n"
        ];
    }

    protected function lengthOfName(array $row): int
    {
        return strlen($row['name']);
    }
}