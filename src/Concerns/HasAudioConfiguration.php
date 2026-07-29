<?php

namespace LaraZeus\Echoo\Concerns;

use Closure;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use League\Flysystem\UnableToCheckFileExistence;
use Livewire\Features\SupportFileUploads\FileNotPreviewableException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

trait HasAudioConfiguration
{
    protected string $disk = 'public';

    protected string | Closure | null $visibility = null;

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function getDisk(): string
    {
        return $this->disk;
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

        try {
            $isStoredFile = false;
            if (is_string($state)) {
                try {
                    $isStoredFile = $storage->exists($state);
                } catch (UnableToCheckFileExistence $e) {
                    $isStoredFile = false;
                }
            }

            $tempFile = null;
            if (is_string($state) && ! $isStoredFile) {
                // Could be a TemporaryUploadedFile from Livewire before saving
                $tempFile = TemporaryUploadedFile::createFromLivewire($state);
            } elseif ($state instanceof TemporaryUploadedFile) {
                $tempFile = $state;
            }

            if ($tempFile && $tempFile->exists()) {
                try {
                    return $tempFile->temporaryUrl();
                } catch (FileNotPreviewableException $e) {
                    return URL::temporarySignedRoute(
                        'livewire.preview-file',
                        now()->addMinutes(30)->endOfHour(),
                        ['filename' => $tempFile->getFilename()]
                    );
                }
            }
        } catch (Throwable $e) {
            // Ignore temporary file creation errors and fall through
        }

        // Prevent passing objects (like TemporaryUploadedFile) to the storage driver if they fell through
        if (! is_string($state)) {
            return null;
        }

        if ($this->getVisibility() === 'private') {
            try {
                return $storage->temporaryUrl(
                    $state,
                    now()->addMinutes(config('filament.temporary_file_url_expiry_minutes', 30))->endOfHour(),
                );
            } catch (Throwable $exception) {
                // Fallback to regular url if temporary is not supported
            }
        }

        return $storage->url($state);
    }
}
