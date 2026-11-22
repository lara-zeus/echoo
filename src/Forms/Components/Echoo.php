<?php

namespace LaraZeus\Echoo\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Illuminate\Database\Eloquent\Model;
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

    protected ?Closure $deleteUploadedFileUsing = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrateStateUsing(function ($state) {
            $oldFile = $this->getOriginalAudioPath();

            if (! $state) {
                if ($oldFile !== null) {
                    $this->deleteUploadedFile($oldFile);
                }

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

            if (! is_string($path)) {
                return null;
            }

            if ($this->getVisibility() === 'public') {
                rescue(fn () => Storage::disk($this->getDisk())->setVisibility($path, 'public'), report: false);
            }

            if ($oldFile !== null && $oldFile !== $path) {
                $this->deleteUploadedFile($oldFile);
            }

            return $path;
        });
    }

    public function deleteUploadedFileUsing(?Closure $callback): static
    {
        $this->deleteUploadedFileUsing = $callback;

        return $this;
    }

    public function deleteUploadedFile(string $file): static
    {
        if (blank($file)) {
            return $this;
        }

        if ($this->deleteUploadedFileUsing) {
            $this->evaluate($this->deleteUploadedFileUsing, [
                'file' => $file,
            ]);

            return $this;
        }

        rescue(
            fn () => Storage::disk($this->getDisk())->delete($file),
            report: false,
        );

        return $this;
    }

    protected function getOriginalAudioPath(): ?string
    {
        $record = rescue(fn () => $this->getRecord(), report: false);

        if (! $record) {
            return null;
        }

        $attribute = $this->getName();

        $oldFile = $record instanceof Model
            ? ($record->getOriginal($attribute) ?? data_get($record, $attribute))
            : data_get($record, $attribute);

        if (! is_string($oldFile) || blank($oldFile) || filter_var($oldFile, FILTER_VALIDATE_URL) !== false) {
            return null;
        }

        return $oldFile;
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
