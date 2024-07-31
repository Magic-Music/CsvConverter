<?php

namespace Cubic;

use Cubic\Cli\Cli;
use Cubic\Providers\Providers as CubicProviders;
use Providers\Providers as DefaultProviders;

class Bootstrap
{
    public function boot(): void
    {
        //Register the system provider class bindings into the service container
        create(CubicProviders::class)->register();

        //Register the default user provider class bindings into the service container
        create(DefaultProviders::class)->register();

        //Register all available CLI commands
        create(Cli::class)->registerCommands();
    }
}