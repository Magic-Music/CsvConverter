<?php

use Cubic\Cli\Cli;
use Cubic\Config;
use Cubic\Container;
use Cubic\File;

function dd(...$output): void
{
    foreach($output as $out) {
        Cli::log(print_r($out, 1));
    }

    exit;
}

function create($class)
{
    return Container::getClass($class);
}

function throw_if($condition, $exception = 'RuntimeException', ...$parameters)
{
    if ($condition) {
        if (is_string($exception) && class_exists($exception)) {
            $exception = new $exception(...$parameters);
        }

        throw is_string($exception) ? new RuntimeException($exception) : $exception;
    }

    return $condition;
}

function array_join(string $separator, string|array $subject): string
{
    if (is_array($subject)) {
        $subject = implode($separator, $subject);
    }

    return $subject;
}

function array_wrap(string|array|null $subject): array
{
    if (!is_array($subject)) {
        $subject = [$subject];
    }

    return $subject;
}

function app_root(): string
{
    return create(File::class)->rootFolder();
}

function array_dot(array $array, string $dotKey): mixed
{
    foreach (explode('.', $dotKey) as $key) {
        $array = $array[$key] ?? null;

        if (is_null($array)) {
            return null;
        }
    }

    return $array;
}

function config($key): mixed
{
    return create(Config::class)->get($key);
}