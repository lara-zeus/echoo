<?php

namespace LaraZeus\Echoo\Forms\Components;

use Filament\Forms\Components\Field;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\UnableToCheckFileExistence;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class Echoo extends Field
{
    protected string $view = 'zeus-echoo::forms.components.echoo';

    protected string $disk = 'public';

    protected string $directory = 'recordings';

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrateStateUsing(function ($state) {
            if (! $state) {
                return null;
            }

            $file = $state;

            if (! $state instanceof TemporaryUploadedFile) {
                $file = TemporaryUploadedFile::createFromLivewire($state);
            }

            if ($file) {
                try {
                    if (! $file->exists()) {
                        return null;
                    }
                } catch (UnableToCheckFileExistence) {
                    return null;
                }

                $filename = Str::ulid() . '.' . $file->getClientOriginalExtension();

                $path = $file->storeAs(
                    $this->getDirectory(),
                    $filename,
                    $this->getDisk(),
                );

                if (config('filesystems.disks.' . $this->getDisk() . '.visibility') === 'public') {
                    rescue(fn () => Storage::disk($this->getDisk())->setVisibility($path, 'public'), report: false);
                }

                return $path;
            }

            return $state;
        });
    }

    /**
     * Define where the finalized recording should be saved.
     */
    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * Define the directory where the audio file should be placed.
     */
    public function directory(string $directory): static
    {
        $this->directory = $directory;

        return $this;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }
}
