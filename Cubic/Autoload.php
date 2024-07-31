<?php

namespace Cubic;

new Autoload();

class Autoload
{
    public function __construct()
    {
        spl_autoload_register([$this, 'autoload']);
    }

    public function autoload(string $class): void
    {
        include $class . '.php';
    }
}
