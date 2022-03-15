<template>
    <div
        class="video-chat"
        :class="{'mode-mini': modeMini}"
    >
        <div class="content-wrap">
            <div class="header-block">
                <h2>{{ __('Видеоконсультация') }}</h2>
                <svg-icon
                    name="dismiss-alt"
                    class="icon-small icon-blue"
                    @click="hide"
                >
                </svg-icon>
            </div>
            <div class="body-block">
                <div
                    v-show="started && !disconnected"
                    ref="patient_video"
                    class="video"
                >
                </div>
                <div
                    ref="preview"
                    v-loading="loading"
                    class="start-session"
                >
                    <a
                        v-if="!room || disconnected"
                        href="#"
                        @click.prevent="createVideoChat"
                    >
                        {{ __('Начать сеанс видеосвязи') }}
                    </a>
                    <span
                        v-else-if="room && !disconnected && countParticipants === 0"
                        v-text="__('Ожидаем подключения пациента')"
                    ></span>
                </div>
            </div>
            <div class="footer-block">
                <div class="controls-main form-input-group"
                     style="margin-bottom: 0;">
                    <el-button
                        type="danger"
                        :disabled="!room || disconnected"
                        @click="disconnect"
                        style="margin-right: 10px;">
                        {{ modeMini ? __('Завершить') : __('Завершить видеозвонок') }}
                    </el-button>
                    <el-select
                        v-if="!modeMini"
                        v-model="activeCamera"
                        @change="cameraChanged"
                        popper-class="camera-selection"
                        style="margin-right: 10px;">
                        <el-option
                            v-for="camera in cameraList"
                            :key="camera.id"
                            :value="camera.id"
                            :label="camera.value">
                            {{ camera.value }}
                        </el-option>
                    </el-select>
                </div>
                <div class="controls-addon">
                    <el-button
                        v-if="!modeMini"
                        type="primary"
                        @click="toggleCameraPreview"
                    >
                        {{ __('Миниатюра камеры: {enabled}', {enabled: cameraPreview ? __('Вкл') : __('Откл')}) }}
                    </el-button>
                    <el-button
                        type="primary"
                        @click="toggleMode"
                    >
                        {{ __('Режим: {mode}', {mode: modeMini ? __('Компактный') : __('Стандартный')}) }}
                    </el-button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import VideoChat from "@/models/video-chat";

const {connect, createLocalTracks, createLocalVideoTrack} = require('twilio-video');

export default {
    props: {
        appointment: Object,
        cameraPreview: Boolean
    },
    data() {
        return {
            activeCamera: null,
            cameraList: [],
            started: false,
            modeMini: false,
            loading: false,
            countParticipants: 0,
            previewVideoTrack: null,
            videoSession: new VideoChat({
                appointment_id: this.appointment.id,
                closed: false,
                initiated: false
            }),
            room: null,
            disconnected: false,
        };
    },
    mounted() {
        window.addEventListener('beforeunload', this.disconnect);
        window.addEventListener('pagehide', this.disconnect);
    },
    beforeDestroy() {
        window.removeEventListener('beforeunload', this.disconnect);
        window.removeEventListener('pagehide', this.disconnect);
    },
    created() {
        if (navigator.mediaDevices) {
            navigator.mediaDevices.enumerateDevices().then((response) => {
                response.forEach((mediaDevice) => {
                    if (mediaDevice.kind === 'videoinput') {
                        this.cameraList.push({
                            id: mediaDevice.deviceId,
                            value: mediaDevice.label,
                        });
                    }
                })
                this.activeCamera = this.cameraList[0].id
            })
        } else {
            this.$error(__('Подключите микрофон и камеру !'))
        }

    },
    methods: {
        cameraChanged(event) {
            this.$eventHub.$emit('cameraChanged', event)
        },
        updateVideoDevice(event) {
            const select = event;
            const localParticipant = this.room.localParticipant;
            if (select !== '') {
                createLocalVideoTrack({
                    facingMode: 'environment',
                    deviceId: {exact: event},
                    width: 890
                }).then(function (localVideoTrack) {
                    const tracks = Array.from(localParticipant.videoTracks.values()).map(publication => publication.track);
                    localParticipant.unpublishTracks(tracks);
                    localParticipant.publishTrack(localVideoTrack);
                });
            }
        },
        hide() {
            this.disconnect();
            this.$emit('hidden');
        },
        toggleCameraPreview() {
            this.$emit('cameraPreview', !this.cameraPreview);
        },
        toggleMode() {
            this.modeMini = !this.modeMini;
        },
        createVideoChat() {
            this.loading = true;
            this.videoSession.save().then((response) => {
                this.twilioConnect(response.response.data.token, response.response.data.room_name);
            }).catch((e) => {
                console.log(e);
                this.loading = false;
            });
        },
        twilioConnect(token, roomName) {
            createLocalTracks({
                audio: true,
                video: {width: 890, deviceId: this.activeCamera}
            }).then(localTracks => {
                connect(token, {
                    name: roomName,
                    tracks: localTracks
                }).then(room => {
                    this.room = room;
                    this.disconnected = false;

                    console.log(`Successfully joined a Room: ${this.room}`);
                    this.room.participants.forEach((participant) => {
                        this.participantConnected(participant, true);
                    });

                    this.room.on('participantConnected', this.participantConnected);

                    this.$eventHub.$on('cameraChanged', this.updateVideoDevice);

                    this.room.on('participantDisconnected', participant => {
                        this.$info(__('Пациент {name} отсоединился', {name: participant.identity}));
                        console.log(__('Пациент {name} отсоединился', {name: participant.identity}));
                        this.countParticipants--;
                        this.$refs.patient_video.innerHTML = '';
                    });

                    this.room.on('disconnected', (room, error) => {
                        this.disconnected = true;
                        localTracks.forEach((track) => {
                            track.stop();
                            room.localParticipant.unpublishTrack(track);
                        })
                        this.$eventHub.$off('cameraChanged', this.updateVideoDevice);
                        this.$refs.patient_video.innerHTML = '';
                    });

                    this.loading = false;
                }, error => {
                    this.$error(`Unable to connect to Room: ${error.message}`)
                    this.loading = false;
                });
            }).catch((error) => {
                this.$error(__('Ошибка {error}', {error: error.message}))
            });
        },
        participantConnected(participant, existing = false) {
            this.countParticipants++;

            if (!existing) {
                this.$info(__('Пациент {name} присоединился', {name: participant.identity}));
            }

            participant.on('trackSubscribed', track => {
                this.$refs.patient_video.appendChild(track.attach());
            });

            participant.on('trackUnsubscribed', track => {
                const attachedElements = track.detach();
                attachedElements.forEach(element => element.remove());
                this.$info(__('Пациент отключил {kind} но может слышать вас', {kind: track.kind}));
            });

            this.disconnected = false;
            this.started = true;
        },
        disconnect() {
            if (this.room) {
                this.room.disconnect();
            }
        },
    }
}
</script>
