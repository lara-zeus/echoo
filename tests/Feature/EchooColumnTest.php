<?php

use LaraZeus\Echoo\Tables\Columns\EchooColumn;

it('can be instantiated', function () {
    $component = EchooColumn::make('audio');

    expect($component)->toBeInstanceOf(EchooColumn::class);
});

it('has default disk', function () {
    $component = EchooColumn::make('audio');

    expect($component->getDisk())->toBe('public');
});

it('can set and get disk', function () {
    $component = EchooColumn::make('audio')->disk('s3');

    expect($component->getDisk())->toBe('s3');
});
