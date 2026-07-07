<?php

namespace LaraZeus\Echoo\Tables\Columns;

use Filament\Tables\Columns\Column;

class EchooColumn extends Column
{
    protected string $view = 'zeus-echoo::tables.columns.echoo-column';

    protected string $disk = 'public';

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->disabledClick()
            ->sortable(false)
            ->searchable(false);
    }

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
