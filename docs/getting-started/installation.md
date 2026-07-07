---
title: Installation
weight: 1
---

## Prerequisites

This package is built for Laravel and Filament.

## Installation

```bash
composer require lara-zeus/echoo
```

## Usage in Forms

Simply:

```php
use LaraZeus\Echoo\Forms\Components\Echoo;

Echoo::make('voice_notes')
    ->disk('voices')
    ->directory('voices')
    ->required(),
```

## Usage in Infolists

To display the audio player in Filament View pages or Infolists:

```php
use LaraZeus\Echoo\Infolists\Components\EchooEntry;

EchooEntry::make('voice_notes')
    ->disk('voices'),
```

## Usage in Tables

To display a play/pause button in your tables for the audio file:

```php
use LaraZeus\Echoo\Tables\Columns\EchooColumn;

EchooColumn::make('voice_notes')
    ->disk('voices'),
```
