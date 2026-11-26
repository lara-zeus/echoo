# Lara Zeus Echoo (`lara-zeus/echoo`)

FilamentPHP v5 browser audio recorder form field, infolist audio player entry, and table column playback component.

## Key Classes
- **Form Field**: `LaraZeus\Echoo\Forms\Components\Echoo` (extends `Filament\Forms\Components\Field`, default directory `'recordings'`)
- **Infolist Entry**: `LaraZeus\Echoo\Infolists\Components\EchooEntry` (extends `Filament\Infolists\Components\Entry`)
- **Table Column**: `LaraZeus\Echoo\Tables\Columns\EchooColumn` (extends `Filament\Tables\Columns\Column`, non-sortable/non-searchable, click disabled)
- **Trait**: `LaraZeus\Echoo\Concerns\HasAudioConfiguration` (`disk()`, `visibility()`, `getAudioUrl()`)
- **Service Provider**: `LaraZeus\Echoo\EchooServiceProvider` (`$name = 'zeus-echoo'`, registers views and translations)

## Rules & Anti-Patterns
- **No Filament Panel Plugin**: Do not register a panel plugin; use `Echoo`, `EchooEntry`, and `EchooColumn` directly in schemas and tables.
- **Match `->disk(...)` across Form, Infolist, and Table**: All three components default `disk` to `'public'`. If you store recordings on another disk (e.g. `'s3'` or `'voices'`), set the same `->disk(...)` on `EchooEntry` and `EchooColumn` too.
- **Automatic visibility & signed URLs**: When `visibility()` is omitted, `'public'` disk defaults to `'public'` visibility while any other disk defaults to `'private'` and generates temporary signed URLs (falling back to `url()` if the driver does not support `temporaryUrl()`).
- **Previous file cleanup**: `Echoo` automatically deletes the original stored file when cleared or replaced; customize or disable with `->deleteUploadedFileUsing(fn (string $file) => ...)`.
