---
name: zeus-echoo
description: Use when recording browser audio in Filament forms or playing back stored audio in infolists and tables with lara-zeus/echoo.
---

# Lara Zeus Echoo

## Overview & Requirements
`lara-zeus/echoo` provides browser-based audio recording for Filament Forms (`Echoo`) plus audio playback for Infolists (`EchooEntry`) and Tables (`EchooColumn`).
- **PHP**: `^8.1`
- **Filament**: `^5.0`
- **Dependencies**: `spatie/laravel-package-tools ^1.19`
- **Package Name**: `lara-zeus/echoo` (Service Provider: `LaraZeus\Echoo\EchooServiceProvider`)

## Installation
```bash
composer require lara-zeus/echoo
```

Optionally publish translations:
```bash
php artisan vendor:publish --tag=zeus-echoo-translations
```

## Configuration & Migrations
No config files or migrations are shipped. Add a nullable string column on your model's table (for example, `$table->string('voice_notes')->nullable();`) to store the saved file path.

## Filament Panel Registration
No panel plugin registration is required. Use `Echoo`, `EchooEntry`, and `EchooColumn` directly.

## Core Usage & Examples

### Form Field (`LaraZeus\Echoo\Forms\Components\Echoo`)
```php
use LaraZeus\Echoo\Forms\Components\Echoo;

Echoo::make('voice_notes')
    ->disk('public') // default: 'public'
    ->directory('voices') // default: 'recordings'
    ->visibility('public') // optional: defaults to 'public' for 'public' disk, else 'private'
    ->deleteUploadedFileUsing(function (string $file): void {
        // optional custom cleanup when cleared or replaced
    })
    ->required();
```

### Infolist Entry (`LaraZeus\Echoo\Infolists\Components\EchooEntry`)
```php
use LaraZeus\Echoo\Infolists\Components\EchooEntry;

EchooEntry::make('voice_notes')
    ->disk('public');
```

### Table Column (`LaraZeus\Echoo\Tables\Columns\EchooColumn`)
```php
use LaraZeus\Echoo\Tables\Columns\EchooColumn;

EchooColumn::make('voice_notes')
    ->disk('public');
```

## Common Pitfalls / Anti-Patterns
- **Mismatched `->disk(...)` between `Echoo` and `EchooEntry`/`EchooColumn`**: `Echoo` stores relative paths (`"{$directory}/{$ulid}.{$ext}"`); if `EchooEntry` or `EchooColumn` uses the default `'public'` disk while `Echoo` saved to another disk, playback URLs break.
- **Passing non-string / array casts**: `Echoo` dehydrates to a single `string` file path (or `null`); do not cast the column to `array`.
- **Testing microphone capture on insecure HTTP**: Browsers restrict microphone access (`getUserMedia`) to HTTPS or `localhost`.
