# CSV File Converter

This is a command line script used to build files in any format, using data from a csv file as the input.

You can create any number of converters, which are simple classes that define the output based on each line of the csv.

### CSV Files
The csv file must have a header row, as the headers define the data for use in the Conversion scripts.

### Conversion Scripts

- The conversion scripts are created in the folder and namespace `Converters`
- They should extend `Resources\Conversion`
- They should implement a method `getOutputStatements` which should return an array of one or more strings
- Each string will be used to generate output based on each row of the csv.

#### Replacers

- The conversion script can make use of placeholders called _replacers_ to add data from the csv row into the string
- The default replacer format consists of a start marker, a header from the csv and an end marker.
- The default markers are `:~ ~:` but can be changed by defining them in an array in the conversion script: 
```
protected array $delimiters = [':~', '~:'];
```

#### Calculated replacers

- Normally a replacer would refer to a header within the csv file. However a calculated value can be added into the file.
- The calculation can use any data in the current row, as well as other sources (for example the current datetime)
- To insert calculated data, create a method in the conversion script that accepts an array
- Use the name of the method between the markers to invoke the method
- The method will receive the current row as an array of `header => value`
- The method should return a single string or numeric to be inserted into the output

```
<?php

namespace Converters;

use Resources\Conversion;

class People extends Conversion
{

    protected function getOutputStatements(): string|array
    {
        return [
            "INSERT INTO `people` VALUES(':~name~:', :~age~:, ':~city~:', :~lengthOfName~:);\n"
        ];
    }

    protected function lengthOfName(array $row): int
    {
        return strlen($row['name']);
    }
}
```

### Input and Output files

By default the script will look for the input csv in `Files/Input/input.csv` and write the output to `Files/Output/output.txt`
The input and output folders can be changed by updating the array in `Config/csv.php`
The input and output files can be changed when invoking the script from the cli.

### Invoking the script

Invoke the script from the command line.

```
./run convert [ConversionScriptName] [{OptionalInputFileName}] [{OptionalOutputFileName}] [--test | -t]
```

### Test Mode

- The `--test` option invokes Test Mode. 
- This will not write an output file, instead the output will be sent to the cli to check that the format is correct
- By default, only the first line of the csv will be tested. 
- To output more lines, send a value to the test option `--test=5`
