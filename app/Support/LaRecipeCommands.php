<?php

namespace App\Support;

use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use LaRecipe\LaRecipe;
use ReflectionClass;

/**
 * LaRecipe registers its artisan commands for the console only. These docs
 * show them with <Artisan command="larecipe:..." />, which reads the console
 * kernel's command list on web requests too, so this lists them there as well.
 * Nothing runs: <Artisan> reads a command's signature and description.
 */
final class LaRecipeCommands
{
    public static function registerForWeb(): void
    {
        Artisan::starting(fn (Artisan $artisan) => $artisan->resolveCommands(self::all()));
    }

    /** @return list<class-string<Command>> */
    public static function all(): array
    {
        $src = dirname((string) (new ReflectionClass(LaRecipe::class))->getFileName());
        $commands = [];

        foreach (glob($src.'/Domains/*/Commands/*.php') ?: [] as $file) {
            $class = 'LaRecipe\\'.str_replace('/', '\\', substr($file, strlen($src) + 1, -4));

            if (class_exists($class) && is_subclass_of($class, Command::class)) {
                $commands[] = $class;
            }
        }

        return $commands;
    }
}
