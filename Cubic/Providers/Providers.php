<?php

namespace Cubic\Providers;

use Cubic\Cli\Cli;
use Cubic\Config;
use Cubic\File;

class Providers extends Provider
{
    /**
     * Add bindings and singletons here:
     *
     * $this->bind(ClassName, ResolvesToClassName)
     *
     * $this->singleton(ClassName)
     * $this->singleton(ClassName, ResolvesToClassName)
     */
    public function register(): void
    {
        $this->singleton(Cli::class);
        $this->singleton(File::class);
        $this->singleton(Config::class);
    }
}