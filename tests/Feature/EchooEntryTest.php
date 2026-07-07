<?php

use LaraZeus\Echoo\Infolists\Components\EchooEntry;

it('can be instantiated', function () {
    $component = EchooEntry::make('audio');

    expect($component)->toBeInstanceOf(EchooEntry::class);
});

it('has default disk', function () {
    $component = EchooEntry::make('audio');

    expect($component->getDisk())->toBe('public');
});

it('can set and get disk', function () {
    $component = EchooEntry::make('audio')->disk('s3');

    expect($component->getDisk())->toBe('s3');
});
