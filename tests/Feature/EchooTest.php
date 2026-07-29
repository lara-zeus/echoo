<?php

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use LaraZeus\Echoo\Forms\Components\Echoo;
use League\Flysystem\UnableToCheckFileExistence;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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

it('can set and get visibility', function () {
    $component = Echoo::make('audio')->visibility('private');

    expect($component->getVisibility())->toBe('private');
});

it('evaluates visibility correctly based on disk', function () {
    $publicComponent = Echoo::make('audio')->disk('public');
    expect($publicComponent->getVisibility())->toBe('public');

    $privateComponent = Echoo::make('audio')->disk('s3');
    expect($privateComponent->getVisibility())->toBe('private');
});

it('returns null for audio url if state is empty', function () {
    $component = new class('audio') extends Echoo
    {
        public function getState(): mixed
        {
            return null;
        }
    };
    expect($component->getAudioUrl())->toBeNull();
});

it('returns state directly if it is a valid url', function () {
    $component = new class('audio') extends Echoo
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

    $component = new class('audio') extends Echoo
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

    $component = new class('audio') extends Echoo
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

it('handles dehydrate with existing string state', function () {
    $component = Echoo::make('audio')->disk('public')->directory('recordings');
    Storage::fake('public');
    Storage::disk('public')->put('recordings/test.mp3', 'audio data');

    $property = new ReflectionProperty($component, 'dehydrateStateUsing');
    $property->setAccessible(true);
    $modifier = $property->getValue($component);

    $bound = $modifier->bindTo($component, $component);
    $dehydrated = $bound('recordings/test.mp3');

    expect($dehydrated)->toBe('recordings/test.mp3');
});

it('handles dehydrate with null state', function () {
    $component = Echoo::make('audio');

    $property = new ReflectionProperty($component, 'dehydrateStateUsing');
    $property->setAccessible(true);
    $modifier = $property->getValue($component);

    $bound = $modifier->bindTo($component, $component);
    $dehydrated = $bound(null);

    expect($dehydrated)->toBeNull();
});

it('handles dehydrate with file not in directory falling through', function () {
    $component = Echoo::make('audio');
    Storage::fake('tmp-for-tests');

    $property = new ReflectionProperty($component, 'dehydrateStateUsing');
    $property->setAccessible(true);
    $modifier = $property->getValue($component);

    $bound = $modifier->bindTo($component, $component);
    $dehydrated = $bound('other-path/test.mp3');

    expect($dehydrated)->toBeNull();
});

it('handles dehydrate with temporary uploaded file', function () {
    $component = Echoo::make('audio')->disk('public')->directory('recordings');
    Storage::fake('public');

    $tempFile = Mockery::mock(TemporaryUploadedFile::class);
    $tempFile->shouldReceive('exists')->andReturn(true);
    $tempFile->shouldReceive('getClientOriginalExtension')->andReturn('mp3');
    $tempFile->shouldReceive('storeAs')->andReturn('recordings/mocked-file.mp3');

    $property = new ReflectionProperty($component, 'dehydrateStateUsing');
    $property->setAccessible(true);
    $modifier = $property->getValue($component);

    $bound = $modifier->bindTo($component, $component);
    $dehydrated = $bound($tempFile);

    expect($dehydrated)->toBe('recordings/mocked-file.mp3');
});

it('handles dehydrate with temporary uploaded file failing existence check', function () {
    $component = Echoo::make('audio')->disk('public')->directory('recordings');
    Storage::fake('public');

    $tempFile = Mockery::mock(TemporaryUploadedFile::class);
    $tempFile->shouldReceive('exists')->andThrow(new UnableToCheckFileExistence);

    $property = new ReflectionProperty($component, 'dehydrateStateUsing');
    $property->setAccessible(true);
    $modifier = $property->getValue($component);

    $bound = $modifier->bindTo($component, $component);
    $dehydrated = $bound($tempFile);

    expect($dehydrated)->toBeNull();
});

it('catches exception for temporaryUrl on driver without support', function () {
    $component = new class('audio') extends Echoo
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
