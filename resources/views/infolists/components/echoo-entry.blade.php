<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @php
        $url = $getAudioUrl();
    @endphp

    @if ($url)
        <div class="flex items-center space-x-4 w-full max-w-sm">
            <audio src="{{ $url }}" controls class="w-full h-10 rounded-lg"></audio>
        </div>
    @endif
</x-dynamic-component>
