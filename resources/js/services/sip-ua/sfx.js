const SOUND_RING = '/audio/voip/ring.ogg';
const SOUND_RINGBACK = '/audio/voip/ringback.ogg';
const SOUND_BUSY = '/audio/voip/busy.ogg';

class AudioChannel 
{
    /**
     * Constructor
     */ 
    constructor(src, loop) {
        this._element = document.createElement('audio');
        this._element.src = src;
        this._element.preload = 'auto';
        this._element.loop = loop;
        this._playing = false;
        this._shouldPlay = false;
        this._ended = () => {};
        if (!loop) {
            this._element.onended = () => {
                this._playing = false;
                this._shouldPlay = false;
                this._ended(this);
            }
        }
    }

    /**
     * Start playing this audio
     */
    play() {
        this._shouldPlay = true;
        if (!this._playing) {
            this._element.currentTime = 0;
            let result = this._element.play();
            if (result instanceof Promise) {
                result.then(() => {
                    if (this._shouldPlay) {
                        this._playing = true;
                    } else {
                        this._element.pause();
                    }
                });
            } else {
                this._playing = true;
            }
        }
    }

    /**
     * Stop playing this audio
     */
    mute() {
        this._shouldPlay = false;
        if (this._playing) {
            this._element.pause();
            this._playing = false;
        }
    }

    /**
     * Set callback to ended event
     * 
     * @param {*} callback 
     */
    onended(callback) {
        this._ended = callback;
    }
}

class SoundFX
{
    /**
     * Constructor
     */ 
    constructor() {
        this._audio = {
            ring: this.createAudio(SOUND_RING, true),
            ringback: this.createAudio(SOUND_RINGBACK, true),
            busy: this.createAudio(SOUND_BUSY, false),
        };
        this._playing = null;
    }
    
    /**
     * Start "Ringing" sfx
     */ 
    ring() {
        this.play(this._audio.ring);
    }
    
    /**
     * Start "Ringback" sfx
     */ 
    ringback() {
        this.play(this._audio.ringback);
    }
    
    /**
     * Start "Busy" sfx
     */ 
    busy() {
        this.play(this._audio.busy);
    }
    
    /**
     * Play specified audio
     * 
     * @param {object} audio
     */ 
    play(audio) {
        if (audio !== this._playing) {
            this.mute();
            audio.play();
            this._playing = audio;
        }
    }
    
    /**
     * Mute playing audio
     */ 
    mute() {
        if (this._playing !== null) {
            this._playing.mute();
            this._playing = null;
        }
    }
    
    /**
     * Create an audio channel
     * 
     * @param {string} src
     * @param {bool} loop
     * 
     * @returns {object}
     */ 
    createAudio(src, loop) {
        let audio = new AudioChannel(src, loop);
        audio.onended((audio) => {
            if (this._playing === audio) {
                this._playing = null;
            }
        });
        return audio;
    }
}

export default SoundFX;