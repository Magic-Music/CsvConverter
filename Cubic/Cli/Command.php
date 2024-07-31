<?php

namespace Cubic\Cli;

abstract class Command
{
    public string $command;
    public string $signature;

    protected array $arguments = [];
    protected array $options = [];

    abstract public function handle();

    public function setArgument(string $key, string $value): void
    {
        $this->arguments[$key] = $value;
    }

    public function setOption(string $key, string|bool $value): void
    {
        $this->options[$key] = $value;
    }

    protected function arguments(): array
    {
        return $this->arguments;
    }

    protected function options(): array
    {
        return $this->options;
    }

    protected function argument(string $argument): ?string
    {
        return $this->arguments[$argument] ?? null;
    }

    protected function option(string $option): ?string
    {
        return $this->options[$option] ?? null;
    }

    protected function log(?string $text = null, int $colour = CliColours::GREEN): void
    {
        if ($text) {
            Cli::log($text, $colour);
        }
    }

    protected function error(?string $text = null): void
    {
        if ($text) {
            Cli::log($text, CliColours::RED);
        }
    }
}