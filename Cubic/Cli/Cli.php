<?php

namespace Cubic\Cli;

use Cubic\Exceptions\CommandMissingSignatureException;
use Cubic\Exceptions\CommandNotFoundException;
use Cubic\Exceptions\CommandNotSpecifiedException;
use Cubic\Exceptions\InvalidCommandCacheException;
use Cubic\File;

class Cli
{
    private const LOG_END_COLOURS = "\e[" . CliColours::DEFAULT . ";" . CliColours::BG_DEFAULT . "m";
    private const COMMAND_CACHE_FILENAME = 'command-cache';

    private array $commands = [];
    private array $argv;

    public function __construct(private File $file)
    {
        global $argv;
        $this->argv = $argv;
    }

    /**
     * Log text to command line with colour options
     */
    public static function log(
        string $text,
        int|array|null $foreground = CliColours::DEFAULT,
        int|array|null $background = CliColours::BG_DEFAULT
    ): void
    {
        $foreground = array_join(';', $foreground);
        $background = array_join(';', $background);
        $separator = ($foreground && $background) ? ';' : '';
        $start = "\e[{$foreground}{$separator}{$background}m";

        echo $start . $text . self::LOG_END_COLOURS . PHP_EOL;
    }

    /**
     * Recursively search core and user command folders and register command signatures
     */
    public function registerCommands(): void
    {
        if ($this->registerCommandsFromCache()) {
            return;
        }

        $this->registerCommandsInFolder('Cubic\Commands');
        $this->registerCommandsInFolder('Commands');
    }

    public function cacheCommands(): int
    {
        file_put_contents(
            app_root('Files') . self::COMMAND_CACHE_FILENAME,
            serialize($this->commands)
        );

        return count($this->commands);
    }

    public function clearCommandCache(): void
    {
        $filename = file_path(self::COMMAND_CACHE_FILENAME);

        if ($filename) {
            unlink ($filename);
        }
    }

    /**
     * Run the given CLI command
     */
    public function run()
    {
        try {
            $class = create($this->getCommandClass());
            $this->parseCliArguments($class);
            $class->handle();
        }
        catch (CommandNotSpecifiedException $e) {
            self::log("No command specified", CliColours::RED);
            exit(E_ERROR);
        }
        catch (CommandNotFoundException $e) {
            self::log($e->getMessage(), CliColours::RED);
            exit(E_ERROR);
        }

    }

    /**
     * Check to see if commands have been cached.
     * If so, use the cache and return true.
     */
    private function registerCommandsFromCache(): bool
    {
        $cacheFile = file_path(self::COMMAND_CACHE_FILENAME);
        if (!$cacheFile) {
            return false;
        }

        try {
            $this->commands = unserialize(file_get_contents($cacheFile));

            return true;
        } catch (\Exception $e) {
            throw new InvalidCommandCacheException("Command cache is invalid");
        }
    }

    /**
     * Recursively search the given folder for php files,
     * check the class for a command signature and store
     * each signature with the name of the command class
     */
    private function registerCommandsInFolder(string $folder): void
    {
        $files = $this->file->search($folder, 'php');

        try {
            foreach ($files as $file) {
                $class = $file['path'] . '\\' . str_replace('.php', '', $file['file']);
                $commands = get_class_vars($class)['command'] ?? null;

                throw_if(
                    !$commands,
                    new CommandMissingSignatureException("Command $class missing 'command' property")
                );

                foreach(explode('|', $commands) as $command) {
                    $this->commands[$command] = $class;
                }
            }
        }
        catch (CommandMissingSignatureException $e) {
            self::log($e->getMessage(), CliColours::RED);
            exit(E_ERROR);
        }
    }

    /**
     * Get the command signature provided to the CLI, look
     * it up against the registered command signatures and
     * return the class name that provides that command
     */
    private function getCommandClass():string
    {
        $command = $this->argv[1] ?? null;

        throw_if(
            !$command,
            new CommandNotSpecifiedException()
        );

        throw_if(
            !in_array($command, array_keys($this->commands)),
            new CommandNotFoundException("Command $command not defined")
        );

        return $this->commands[$command];
    }

    /**
     * Get the CLI arguments and options, and pass them to the command class
     */
    private function parseCliArguments(Command $class): void
    {
        $class->setCliCommand($this->argv[1]);
        unset ($this->argv[0], $this->argv[1]);

        $parsedSignature = $this->parseCommandSignature($class);
        $this->setArgumentValuesOnCommandClass($class, $parsedSignature['arguments']);
        $this->setOptionValuesOnCommandClass($class, $parsedSignature['options']);
    }

    /**
     * Parse the signature property on the command class,
     * work out the names of arguments and options
     */
    private function parseCommandSignature(object $class): array
    {
        $signature = explode(' ', $class->signature);
        $arguments = [];
        $options = [];
        $parsingArguments = true;

        foreach ($signature as $element) {
            if (!$element) {
                continue;
            }

            if ($parsingArguments && str_starts_with($element, '-')) {
                $parsingArguments = false;
            }

            if ($parsingArguments) {
                $arguments[] = trim($element);
            } else {
                $element = explode('|', $element);
                if ($element[1] ?? null) {
                    $options[trim($element[1], ' -')] = trim($element[0]);
                } else {
                    $options[] = trim($element[0]);
                }
            }
        }

        return [
            'arguments' => $arguments,
            'options' => $options,
        ];
    }

    /**
     * Go through each argument in the command
     * signature, get each value from the command
     * line and set the value on the command class
     */
    private function setArgumentValuesOnCommandClass(Command $class, array $arguments): void
    {
        $pointer = 2;
        $optionalArguments = false;
        foreach ($arguments as $argument) {
            if (str_starts_with($argument, '?')) {
                $optionalArguments = true;
                $argument = trim($argument, '?');
            }

            $value = trim($this->argv[$pointer] ?? '');
            if (str_starts_with($value, '-') || !$value)
            {
                if ($optionalArguments) {
                    break;
                }

                self::log("Missing required argument $argument");
                exit(E_ERROR);
            }

            $class->setArgument($argument, $value);
            unset($this->argv[$pointer]);
            $pointer++;
        }
    }

    /**
     * Go through each option in the command signature,
     * check to see if the option has a value provided on
     * the command line and set the value on the command class
     */
    private function setOptionValuesOnCommandClass(Command $class, array $options): void
    {
        foreach ($this->argv as $arg) {
            $argSplit = explode('=', $arg);
            [$key, $value] =  [trim($argSplit[0], ' -'), trim($argSplit[1] ?? true)];
            $key = $options[$key] ?? $key;
            $class->setOption(trim($key, ' -'), $value);
        }
    }
}