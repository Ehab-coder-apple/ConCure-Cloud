{{-- Voice-to-Text (Speech Recognition) component --}}
<style>
.voice-input-wrapper { position: relative; }
.voice-input-wrapper textarea,
.voice-input-wrapper input[type="text"] { padding-right: 44px; }
.btn-voice {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: #e9ecef;
    color: #495057;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    z-index: 2;
    padding: 0;
    font-size: 14px;
}
.btn-voice:hover { background: #dee2e6; }
.btn-voice.recording {
    background: #dc3545;
    color: #fff;
    animation: pulse-mic 1.2s infinite;
}
@keyframes pulse-mic {
    0%, 100% { box-shadow: 0 0 0 0 rgba(220,53,69,0.5); }
    50% { box-shadow: 0 0 0 8px rgba(220,53,69,0); }
}
.voice-status {
    font-size: 0.75rem;
    margin-top: 2px;
    min-height: 18px;
}
/* RTL adjustment */
html[dir="rtl"] .voice-input-wrapper textarea,
html[dir="rtl"] .voice-input-wrapper input[type="text"] { padding-right: 12px; padding-left: 44px; }
html[dir="rtl"] .btn-voice { right: auto; left: 8px; }
</style>

<script>
const VoiceInput = {
    supported: !!( window.SpeechRecognition || window.webkitSpeechRecognition ),
    activeInstance: null,

    langMap: {
        'en': 'en-US',
        'ar': 'ar-SA',
        'ku': 'ar-IQ'
    },

    getLang() {
        const appLocale = '{{ app()->getLocale() }}';
        return this.langMap[appLocale] || 'en-US';
    },

    init(btn) {
        if (!this.supported) {
            btn.title = '{{ __("Speech recognition not supported in this browser") }}';
            btn.style.opacity = '0.4';
            btn.style.cursor = 'not-allowed';
            return;
        }
        btn.addEventListener('click', () => this.toggle(btn));
    },

    toggle(btn) {
        if (btn.classList.contains('recording')) {
            this.stop(btn);
        } else {
            this.start(btn);
        }
    },

    start(btn) {
        // Stop any other active recording
        if (this.activeInstance) {
            this.activeInstance.recognition.stop();
            this.activeInstance.btn.classList.remove('recording');
        }

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const recognition = new SpeechRecognition();
        recognition.lang = this.getLang();
        recognition.continuous = true;
        recognition.interimResults = true;

        const textarea = btn.closest('.voice-input-wrapper').querySelector('textarea, input[type="text"]');
        const statusEl = btn.closest('.voice-input-wrapper').querySelector('.voice-status');
        const startPos = textarea.value.length;

        btn.classList.add('recording');
        btn.innerHTML = '<i class="fas fa-stop"></i>';
        if (statusEl) statusEl.innerHTML = '<span class="text-danger"><i class="fas fa-circle me-1" style="font-size:8px"></i>{{ __("Listening...") }}</span>';

        recognition.onresult = (event) => {
            let interim = '', final = '';
            for (let i = event.resultIndex; i < event.results.length; i++) {
                const transcript = event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                    final += transcript;
                } else {
                    interim += transcript;
                }
            }
            if (final) {
                const separator = textarea.value.length > 0 && !textarea.value.endsWith(' ') ? ' ' : '';
                textarea.value += separator + final;
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (statusEl && interim) {
                statusEl.innerHTML = '<span class="text-muted"><i class="fas fa-ellipsis-h me-1"></i>' + interim + '</span>';
            }
        };

        recognition.onerror = (event) => {
            console.warn('Speech error:', event.error);
            if (statusEl) {
                const msg = event.error === 'not-allowed' ? '{{ __("Microphone access denied") }}' : '{{ __("Error:") }} ' + event.error;
                statusEl.innerHTML = '<span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>' + msg + '</span>';
            }
            this.stop(btn);
        };

        recognition.onend = () => {
            // Only reset if still marked as recording (not manually stopped)
            if (btn.classList.contains('recording')) {
                this.stop(btn);
            }
        };

        recognition.start();
        this.activeInstance = { recognition, btn };
    },

    stop(btn) {
        btn.classList.remove('recording');
        btn.innerHTML = '<i class="fas fa-microphone"></i>';
        const statusEl = btn.closest('.voice-input-wrapper').querySelector('.voice-status');
        if (statusEl) statusEl.innerHTML = '';
        if (this.activeInstance && this.activeInstance.btn === btn) {
            try { this.activeInstance.recognition.stop(); } catch(e) {}
            this.activeInstance = null;
        }
    },

    /** Special handler for medicine-name mic buttons */
    initMedicine(btn) {
        if (!this.supported) {
            btn.style.opacity = '0.4';
            btn.style.cursor = 'not-allowed';
            return;
        }
        btn.addEventListener('click', () => this.toggleMedicine(btn));
    },

    toggleMedicine(btn) {
        if (btn.classList.contains('recording')) {
            this.stopMedicine(btn);
        } else {
            this.startMedicine(btn);
        }
    },

    startMedicine(btn) {
        if (this.activeInstance) {
            this.activeInstance.recognition.stop();
            this.activeInstance.btn.classList.remove('recording');
        }

        const container = btn.closest('.medicine-select-container');
        const selectEl = container.querySelector('.medicine-select');
        const customInput = container.querySelector('.custom-medicine-input');
        const statusEl = container.querySelector('.voice-status-medicine');

        // Switch to custom input mode
        selectEl.style.display = 'none';
        customInput.style.display = 'block';
        customInput.value = '';
        customInput.focus();

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const recognition = new SpeechRecognition();
        recognition.lang = this.getLang();
        recognition.continuous = false;
        recognition.interimResults = true;

        btn.classList.add('recording');
        btn.innerHTML = '<i class="fas fa-stop"></i>';
        if (statusEl) statusEl.innerHTML = '<span class="text-danger"><i class="fas fa-circle me-1" style="font-size:8px"></i>{{ __("Listening...") }}</span>';

        recognition.onresult = (event) => {
            let interim = '', final = '';
            for (let i = event.resultIndex; i < event.results.length; i++) {
                const transcript = event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                    final += transcript;
                } else {
                    interim += transcript;
                }
            }
            if (final) {
                customInput.value = final.trim();
                customInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (statusEl && interim) {
                statusEl.innerHTML = '<span class="text-muted"><i class="fas fa-ellipsis-h me-1"></i>' + interim + '</span>';
            }
        };

        recognition.onerror = (event) => {
            console.warn('Speech error:', event.error);
            if (statusEl) {
                const msg = event.error === 'not-allowed' ? '{{ __("Microphone access denied") }}' : '{{ __("Error:") }} ' + event.error;
                statusEl.innerHTML = '<span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>' + msg + '</span>';
            }
            this.stopMedicine(btn);
        };

        recognition.onend = () => {
            if (btn.classList.contains('recording')) {
                this.stopMedicine(btn);
            }
            // After recognition ends, finalize the custom medicine
            if (customInput.value.trim()) {
                handleCustomMedicine(customInput);
            }
        };

        recognition.start();
        this.activeInstance = { recognition, btn };
    },

    stopMedicine(btn) {
        btn.classList.remove('recording');
        btn.innerHTML = '<i class="fas fa-microphone"></i>';
        const container = btn.closest('.medicine-select-container');
        const statusEl = container ? container.querySelector('.voice-status-medicine') : null;
        if (statusEl) statusEl.innerHTML = '';
        if (this.activeInstance && this.activeInstance.btn === btn) {
            try { this.activeInstance.recognition.stop(); } catch(e) {}
            this.activeInstance = null;
        }
    },

    /** Call this after dynamically adding elements (e.g. medicine rows) */
    bindAll() {
        document.querySelectorAll('.btn-voice:not(.btn-voice-medicine):not([data-voice-bound])').forEach(btn => {
            btn.setAttribute('data-voice-bound', '1');
            this.init(btn);
        });
        document.querySelectorAll('.btn-voice-medicine:not([data-voice-bound])').forEach(btn => {
            btn.setAttribute('data-voice-bound', '1');
            this.initMedicine(btn);
        });
    }
};

document.addEventListener('DOMContentLoaded', () => VoiceInput.bindAll());
</script>

