<?php

namespace LaraZeus\Echoo\Infolists\Components;

use Closure;
use Filament\Infolists\Components\Entry;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Throwable;

class EchooEntry extends Entry
{
    protected string $view = 'zeus-echoo::infolists.components.echoo-entry';

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
