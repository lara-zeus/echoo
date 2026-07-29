<?php

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
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

it('can set and get visibility', function () {
    $component = EchooEntry::make('audio')->visibility('private');

    expect($component->getVisibility())->toBe('private');
});

it('evaluates visibility correctly based on disk', function () {
    $publicComponent = EchooEntry::make('audio')->disk('public');
    expect($publicComponent->getVisibility())->toBe('public');

    $privateComponent = EchooEntry::make('audio')->disk('s3');
    expect($privateComponent->getVisibility())->toBe('private');
});

it('returns null for audio url if state is empty', function () {
    $component = new class('audio') extends EchooEntry
    {
        public function getState(): mixed
        {
            return null;
        }
    };
    expect($component->getAudioUrl())->toBeNull();
});

it('returns state directly if it is a valid url', function () {
    $component = new class('audio') extends EchooEntry
    {
        public function getState(): mixed
        {
            return 'https://example.com/audio.mp3';
        }
    };
    expect($component->getAudioUrl())->toBe('https://example.com/audio.mp3');
});

it('returns storage url for public visibility', function () {
    Storage::fake('public');

    $component = new class('audio') extends EchooEntry
    {
        public function getState(): mixed
        {
            return 'recordings/audio.mp3';
        }
    };
    $component->disk('public');

    expect($component->getAudioUrl())->toBe('/storage/recordings/audio.mp3');
});

it('returns temporary storage url for private visibility', function () {
    Storage::fake('s3');

    $component = new class('audio') extends EchooEntry
    {
        public function getState(): mixed
        {
            return 'recordings/audio.mp3';
        }
    };
    $component->disk('s3');

    $url = $component->getAudioUrl();
    expect($url)->toBeString();
});

it('catches exception for temporaryUrl on driver without support', function () {
    $component = new class('audio') extends EchooEntry
    {
        public function getState(): mixed
        {
            return 'recordings/audio.mp3';
        }
    };
    $component->disk('s3')->visibility('private');

    $mockDisk = Mockery::mock(FilesystemAdapter::class);
    $mockDisk->shouldReceive('temporaryUrl')->andThrow(new RuntimeException('Not supported'));
    $mockDisk->shouldReceive('url')->andReturn('/storage/recordings/audio.mp3');
    Storage::shouldReceive('disk')->with('s3')->andReturn($mockDisk);

    $url = $component->getAudioUrl();
    expect($url)->toBeString()->toContain('/storage/recordings/audio.mp3');
});
