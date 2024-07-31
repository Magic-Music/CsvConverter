<?php

/**
 * Cubic lightweight framework for cli scripts
 * @author Adam Hembrough
 *
 * Main entry point
 */

use Cubic\Bootstrap;
use Cubic\Cli\Cli;

require_once 'Cubic/Autoload.php';
require_once 'Cubic/Helpers.php';

create(Bootstrap::class)->boot();
create(Cli::class)->run();
