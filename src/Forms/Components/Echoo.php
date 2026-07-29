<?php

namespace LaraZeus\Echoo\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\UnableToCheckFileExistence;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class Echoo extends Field
{
    protected string $view = 'zeus-echoo::forms.components.echoo';

    protected string $disk = 'public';

    protected string $directory = 'recordings';

    protected string | Closure | null $visibility = null;

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

    public function visibility(string | Closure | null $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getVisibility(): string
    {
        $visibility = $this->evaluate($this->visibility);

        if (filled($visibility)) {
            return $visibility;
        }

        return ($this->getDisk() === 'public') ? 'public' : 'private';
    }

    public function getAudioUrl(): ?string
    {
        $state = $this->getState();

        if (! $state) {
            return null;
        }

        if (filter_var($state, FILTER_VALIDATE_URL) !== false) {
            return $state;
        }

        /** @var FilesystemAdapter $storage */
        $storage = Storage::disk($this->getDisk());

        if ($this->getVisibility() === 'private') {
            try {
                return $storage->temporaryUrl(
                    $state,
                    now()->addMinutes(config('filament.temporary_file_url_expiry_minutes', 30))->endOfHour(),
                );
            } catch (Throwable $exception) {
                // This driver does not support creating temporary URLs.
            }
        }

        return $storage->url($state);
    }
}
