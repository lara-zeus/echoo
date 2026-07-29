<?php

namespace LaraZeus\Echoo\Forms\Components;

use Filament\Forms\Components\Field;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaraZeus\Echoo\Concerns\HasAudioConfiguration;
use League\Flysystem\UnableToCheckFileExistence;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class Echoo extends Field
{
    use HasAudioConfiguration;

    protected string $view = 'zeus-echoo::forms.components.echoo';

    protected string $directory = 'recordings';

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrateStateUsing(function ($state) {
            if (! $state) {
                return null;
            }

            if (is_string($state) && str_starts_with($state, $this->getDirectory()) && Storage::disk($this->getDisk())->exists($state)) {
                return $state;
            }

            if (! $state instanceof TemporaryUploadedFile) {
                $file = TemporaryUploadedFile::createFromLivewire($state);
            } else {
                $file = $state;
            }

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

            if ($this->getVisibility() === 'public') {
                rescue(fn () => Storage::disk($this->getDisk())->setVisibility($path, 'public'), report: false);
            }

            return $path;
        });
    }

    /**
     * Define the directory where the audio file should be placed.
     */
    public function directory(string $directory): static
    {
        $this->directory = $directory;

        return $this;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }
}
