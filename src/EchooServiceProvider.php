<?php

namespace LaraZeus\Echoo;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class EchooServiceProvider extends PackageServiceProvider
{
    public static string $name = 'zeus-echoo';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews()
            ->hasTranslations();
    }
}
