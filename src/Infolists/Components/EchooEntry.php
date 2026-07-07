<?php

namespace LaraZeus\Echoo\Infolists\Components;

use Filament\Infolists\Components\Entry;

class EchooEntry extends Entry
{
    protected string $view = 'zeus-echoo::infolists.components.echoo-entry';

    protected string $disk = 'public';

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }
}
