<div
    x-data="{
        playing: false,
        audio: null,
        init() {
            this.audio = new Audio('{{ \Illuminate\Support\Facades\Storage::disk($getDisk())->url($getState()) }}');
            this.audio.onended = () => { this.playing = false; };
        },
        toggle() {
            if (this.playing) {
                this.audio.pause();
            } else {
                this.audio.play();
            }
            this.playing = !this.playing;
        }
    }"
    x-on:click.stop
    class="w-full flex items-center justify-center px-4"
>
    @if ($getState())
        <button
            type="button"
            x-on:click.stop="toggle()"
            class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 text-primary-600 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition"
            title="Play/Pause"
        >
            <x-filament::icon x-show="!playing" icon="heroicon-m-play" class="w-6 h-6" />
            <x-filament::icon x-show="playing" x-cloak icon="heroicon-m-pause" class="w-6 h-6" />
        </button>
    @else
        <span class="text-gray-400 dark:text-gray-500">-</span>
    @endif
</div>
