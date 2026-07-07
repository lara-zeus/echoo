<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{
        state: $wire.$entangle('{{ $getStatePath() }}'),
        recording: false,
        hasPermission: false,
        mediaRecorder: null,
        audioChunks: [],
        audioUrl: null,
        isUploading: false,

        async checkPermission() {
            try {
                const result = await navigator.permissions.query({ name: 'microphone' });
                this.hasPermission = result.state === 'granted';

                result.onchange = () => {
                    this.hasPermission = result.state === 'granted';
                };
            } catch (err) {
                // Ignore if browser doesn't support permissions.query for microphone
            }
        },

        init() {
            // Check if permission is already granted
            this.checkPermission();

            // If there's an existing saved file path, create a preview URL for it
            if (this.state) {
                this.audioUrl = `/storage/` + this.state;
            }
        },

        async requestPermission() {
            try {
                // Request the stream just to ask for permission, then immediately release it
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                stream.getTracks().forEach(track => track.stop());
                this.hasPermission = true;
                return true;
            } catch (err) {
                alert('{{ __('zeus-echoo::echoo.microphone_access_denied') }}');
                this.hasPermission = false;
                return false;
            }
        },

        async startRecording() {
            if (!this.hasPermission) {
                const granted = await this.requestPermission();
                if (!granted) return;
            }

            this.audioChunks = [];
            this.audioUrl = null;
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.mediaRecorder = new MediaRecorder(stream);

                this.mediaRecorder.ondataavailable = (e) => {
                    if (e.data.size > 0) {
                        this.audioChunks.push(e.data);
                    }
                };

                this.mediaRecorder.onstop = async () => {
                    const audioBlob = new Blob(this.audioChunks, { type: 'audio/wav' });
                    this.audioUrl = URL.createObjectURL(audioBlob);
                    this.isUploading = true;

                    let file = new File([audioBlob], 'recording.wav', { type: 'audio/wav' });

                    // Upload via Filament/Livewire core asset uploader
                    @this.upload('{{ $getStatePath() }}', file,
                        async (uploadedName) => {
                            this.state = uploadedName;
                            this.isUploading = false;
                        },
                        () => {
                            alert('{{ __('zeus-echoo::echoo.audio_upload_failed') }}');
                            this.isUploading = false;
                        }
                    );
                };

                this.mediaRecorder.start();
                this.recording = true;
            } catch (err) {
                alert('{{ __('zeus-echoo::echoo.microphone_access_denied') }}');
            }
        },

        stopRecording() {
            if (this.mediaRecorder && this.recording) {
                this.mediaRecorder.stop();
                this.recording = false;
                this.mediaRecorder.stream.getTracks().forEach(track => track.stop());
            }
        },

        deleteRecording() {
            if(confirm('{{ __('zeus-echoo::echoo.delete_recording_confirm') }}')) {
                this.state = null;
                this.audioUrl = null;
            }
        }
    }">

        <div class="flex items-center w-full">

            <!-- Default State: Ready to Record -->
            <template x-if="!recording && !state && !isUploading">
                <x-filament::button
                    type="button"
                    color="gray"
                    x-on:click="startRecording()"
                >
                    <x-filament::icon
                        icon="heroicon-m-microphone"
                        class="w-5 h-5 mr-1"
                    />
                    {{ __('zeus-echoo::echoo.record_audio') }}
                </x-filament::button>
            </template>

            <!-- Recording State -->
            <template x-if="recording">
                <div class="flex items-center space-x-6">
                    <x-filament::button
                        type="button"
                        color="danger"
                        x-on:click="stopRecording()"
                        class="animate-pulse"
                    >
                        <x-filament::icon
                            icon="heroicon-m-stop"
                            class="w-5 h-5 mr-1"
                        />
                        {{ __('zeus-echoo::echoo.stop_recording') }}
                    </x-filament::button>
                    <div class="flex items-center space-x-2 text-sm font-medium text-danger-600 dark:text-danger-400">
                        <span class="relative flex h-3 w-3">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-danger-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-danger-500"></span>
                        </span>
                        <span>{{ __('zeus-echoo::echoo.recording') }}</span>
                    </div>
                </div>
            </template>

            <!-- Uploading State -->
            <div x-show="isUploading" x-cloak class="flex items-center space-x-2 text-sm font-medium text-primary-600 dark:text-primary-400">
                <x-filament::loading-indicator class="h-5 w-5" />
                <span>{{ __('zeus-echoo::echoo.uploading') }}</span>
            </div>

            <!-- Completed / Has Audio State -->
            <template x-if="state && !recording && !isUploading">
                <div class="flex items-center space-x-4 w-full max-w-sm">
                    <div class="flex-1">
                        <audio :src="audioUrl || (state ? '/storage/' + state : '')" controls class="w-full h-10 rounded-lg"></audio>
                    </div>
                    <x-filament::icon-button
                        icon="heroicon-m-trash"
                        type="button"
                        color="danger"
                        size="md"
                        x-on:click="deleteRecording()"
                        tooltip="{{ __('zeus-echoo::echoo.remove_recording') }}"
                    />
                </div>
            </template>

        </div>
    </div>
</x-dynamic-component>
