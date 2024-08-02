<?php

namespace Cubic\Commands;

use Cubic\Cli\Cli;
use Cubic\Cli\Command;

class CommandCache extends Command
{
    public string $command = "command:cache|command:cache:clear";

    public function __construct(private Cli $cli)
    {
    }

    public function handle()
    {
        match($this->cliCommand) {
            'command:cache' => $this->cli->cacheCommands(),
            'command:cache:clear' => $this->cli->clearCommandCache()
        };
    }
}