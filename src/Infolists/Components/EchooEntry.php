<?php

namespace LaraZeus\Echoo\Infolists\Components;

use Filament\Infolists\Components\Entry;
use LaraZeus\Echoo\Concerns\HasAudioConfiguration;

class EchooEntry extends Entry
{
    use HasAudioConfiguration;

    protected string $view = 'zeus-echoo::infolists.components.echoo-entry';
}
