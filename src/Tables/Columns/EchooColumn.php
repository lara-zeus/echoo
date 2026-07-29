<?php

namespace LaraZeus\Echoo\Tables\Columns;

use Filament\Tables\Columns\Column;
use LaraZeus\Echoo\Concerns\HasAudioConfiguration;

class EchooColumn extends Column
{
    use HasAudioConfiguration;

    protected string $view = 'zeus-echoo::tables.columns.echoo-column';

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->disabledClick()
            ->sortable(false)
            ->searchable(false);
    }
}
