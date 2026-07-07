<?php

use LaraZeus\Echoo\Forms\Components\Echoo;

it('can be instantiated', function () {
    $component = Echoo::make('audio');

    expect($component)->toBeInstanceOf(Echoo::class);
});

it('has default disk and directory', function () {
    $component = Echoo::make('audio');

    expect($component->getDisk())->toBe('public')
        ->and($component->getDirectory())->toBe('recordings');
});

it('can set and get disk', function () {
    $component = Echoo::make('audio')->disk('s3');

    expect($component->getDisk())->toBe('s3');
});

it('can set and get directory', function () {
    $component = Echoo::make('audio')->directory('custom-recordings');

    expect($component->getDirectory())->toBe('custom-recordings');
});
