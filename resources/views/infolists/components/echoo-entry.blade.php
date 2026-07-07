<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @php
        $state = $getState();
        $disk = $getDisk();
    @endphp

    @if ($state)
        <div class="flex items-center space-x-4 w-full max-w-sm">
            <audio src="{{ \Illuminate\Support\Facades\Storage::disk($disk)->url($state) }}" controls class="w-full h-10 rounded-lg"></audio>
        </div>
    @endif
</x-dynamic-component>
