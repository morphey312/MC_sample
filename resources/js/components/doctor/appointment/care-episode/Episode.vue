<template>
    <div
        v-loading="loading">
        <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
            <el-tab-pane
                :label="__('Текущий эпизод')"
                name="encounter">
                <alert
                    v-if="!patientInEhealth"
                    type="attention">
                    {{ __('Данный пациент не был зарегистрирован в eHealth') }}
                </alert>
                <section>
                    <el-row :gutter="20">
                        <el-col :span="12">
                            <form-select
                                :entity="model"
                                property="episode_id"
                                :options="episodes" />
                        </el-col>
                        <el-col :span="12">
                            <el-button
                                @click="createEpisode">
                                {{ __('Создать новый эпизод') }}
                            </el-button>
                        </el-col>
                    </el-row>
                </section>
                <hr />
                <section v-if="model.episode_id && encounter">
                    <encounter-packege
                        v-loading="loadingEncounters"
                        :encounter="encounter"
                        :outpatientRecord="outpatientRecord"
                        :appointment="appointment"
                        @saveEncounter="save"/>
                </section>
                <section
                    v-else
                    class="text-center">
                    {{ __('У данного пациента нет открытых эпизодов оказания медицинской помощи.') }}
                    {{ __('Чтобы продолжить, ') }}
                    <a href="#" @click.prevent="createEpisode">{{ __('создайте новый эпизод') }}</a>
                </section>
                <div class="dialog-footer text-right">
                    <el-button
                        @click="cancel">
                        {{ __('Отменить') }}
                    </el-button>
                    <el-button
                        :disabled="model.episode_id && encounter ? false : true"
                        @click="removeEncounter">
                        {{ __('Удалить') }}
                    </el-button>
                    <el-button
                        type="primary"
                        @click="save">
                        {{ __('Сохранить') }}
                    </el-button>
                    <el-button
                        type="primary"
                        @click="createAndSubmit">
                        {{ __('Сохранить и отправить в e-Health') }}
                    </el-button>
                </div>
            </el-tab-pane>
            <el-tab-pane
                :label="__('Эпизоды медицинской помощи')"
                name="episodes">
                <section>
                    <episodes
                        :episodes="episodes"
                        @updated="episodeUpdated"
                        @deleted="episodeDeleted" />
                </section>
            </el-tab-pane>
        </el-tabs>
    </div>
</template>

<script>
import CareEpisodeRepository from '@/repositories/ehealth/care-episode';
import CreateEpisode from './CreateForm.vue';
import Episodes from './Episodes.vue';
import CloseForm from './encounter/CloseForm.vue';
import EncounterPackege from './encounter/EncounterPackege.vue';
import Encounter from '@/models/ehealth/encounter';
import EncounterRepository from '@/repositories/ehealth/encounter';

export default {
    components: {
        Episodes,
        EncounterPackege,
    },
    props: {
        appointment: Object,
        outpatientRecord: Object,
    },
    computed: {
        patientInEhealth() {
            return _.isFilled(this.appointment.patient.ehealth_id);
        }
    },
    watch: {
        ['model.episode_id'](val) {
            this.loadingEncounters = true
            this.$clearErrors()
            this.getEncounter(val);
        },
    },
    data() {
        return {
            activeTab: 'encounter',
            loading: true,
            loadingEncounters: true,
            episodes: [],
            encounter: null,
            model: {
                episode_id: null,
            },
        };
    },
    mounted() {
        let repository = new CareEpisodeRepository();
        repository.fetch({
            patient: this.appointment.patient_id,
            date_end: true,
        }, [{direction: 'desc', field: 'date'}], null, 1, 100).then((result) => {
            this.episodes = result.rows.map((row) => ({
                id: row.id,
                value: row.name,
                data: row,
            }));
            if (this.episodes.length !== 0) {
                this.model.episode_id = this.episodes[this.episodes.length - 1].id;
            } else {
                this.loadingEncounters = false
            }
            this.loading = false;
        }).catch(() => {
            this.loading = false;
        });
    },
    methods: {
        createAndSubmit() {

        },
        removeEncounter() {
            if (this.encounter.id) {
                this.$modalComponent(CloseForm, {
                        model: this.encounter
                    }, {
                        cancel: (dialog) => {
                            dialog.close();
                        },
                        saved: (dialog, model) => {
                            dialog.close();
                            this.createNewEncounter()
                        },
                    },
                    {
                        header: __('Удаление взаимодействия'),
                        width: '500px',
                    });
            } else {
                this.createEpisode(this.model.episode_id)
            }
        },
        getEncounter(episode_id) {
            let repository = new EncounterRepository();
            repository.fetch({
                cancellation_reason: null,
                care_episode_id: episode_id,
            }).then((result) => {
                if (result.rows.length !== 0) {
                    this.encounter = result.rows[0]
                } else {
                    this.createNewEncounter(episode_id)
                }
                this.loadingEncounters = false
            }).catch(() => {
                this.loadingEncounters = false;
            });
        },
        createNewEncounter(episode_id) {
            this.encounter = new Encounter({
                care_episode_id: episode_id,
                appointment_id: this.appointment.id,
                action_references: this.appointment.services.filter(service => !_.isNull(service.ehealth_service_id)).map(service => service.ehealth_service_id)
            })
        },
        cancel() {
            this.$emit('close');
        },
        createEpisode() {
            this.$modalComponent(CreateEpisode, {
                appointment: this.appointment,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                created: (dialog, model) => {
                    dialog.close();
                    this.episodes = [
                        ...this.episodes,
                        {
                            id: model.id,
                            value: model.name,
                            data: model,
                        },
                    ];
                },
            }, {
                header: __('Создание эпизода'),
                width: '500px',
            });
        },
        episodeUpdated(model) {
            this.episodes = this.episodes.map((e) => {
                if (e.data.id === model.id) {
                    return {
                        id: model.id,
                        value: model.name,
                        data: model,
                    };
                }

                return e;
            });
        },
        episodeDeleted(id) {
            this.episodes = this.episodes.filter((e) => e.id !== id);
        },
        save(close = false) {
            this.encounter.save().then((response) => {
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
    }
}
</script>
