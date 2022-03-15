<template>
    <page
        :title="__('Видеоконсультации')"
        type="flex">
        <template slot="header-addon">
            <div class="buttons">
                <toggle-link v-model="displayFilter">
                    <svg-icon name="filter-alt" class="icon-small icon-blue">
                        {{ __('Фильтр') }}
                    </svg-icon>
                </toggle-link>
            </div>
        </template>
        <drawer :open="displayFilter">
            <section class="grey filter">
                <video-consultations-filter
                    ref="filter"
                    :initial-state="filters"
                    permissions="services"
                    @changed="changeFiltersAndShowTable"
                    @cleared="clearFiltersAndHideTable" />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <video-consultations-list
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters">
                <div class="buttons" slot="buttons">
                    <form-button
                        :disabled="!canOrderVideo"
                        :text="__('Заказать видео')"
                        @click="requestComposition"
                        icon="menu-marketing"
                    />
                    <form-button
                        :disabled="!canRequestLogs"
                        :text="__('Логи')"
                        :loading="logRequestLoading"
                        @click="requestParticipantLogs"
                        icon="menu-marketing"
                    />
                </div>
            </video-consultations-list>
        </section>
    </page>
</template>




<script>
import VideoConsultationsList from './video-consultations/List.vue';
import VideoConsultationsFilter from './video-consultations/Filter.vue';
import ParticipantLogs from './video-consultations/ParticipantsLog';
import ManageMixin from '@/mixins/manage';

export default {
    name: 'VideoConsultations',
    mixins: [
        ManageMixin
    ],
    components: {
        VideoConsultationsFilter,
        VideoConsultationsList,
    },
    data(){
        return {
            displayFilter: true,
            displayTable: true,
            logRequestLoading: false,
        }
    },
    computed: {
        canOrderVideo(){
            return this.activeItem !== null &&
                this.activeItem.room_sid &&
                !this.activeItem.composition_sid &&
                this.activeItem.room_status !== 'in-progress'
        },
        canRequestLogs(){
            return this.activeItem !== null && this.$can('video-chat.request-room-participant-logs');
        }
    },
    methods: {
        requestComposition(){
            this.activeItem.requestComposition().then(() => {
                this.$info(__('Заявка на видео отправлена успешно!'));
                this.activeItem.composition_sid = true;
            });
        },
        requestParticipantLogs(){
            this.logRequestLoading = true;
            this.activeItem.requestParticipantsLogs().then((response) => {
                this.logRequestLoading = false;

                this.$modalComponent(ParticipantLogs, {
                    participants: response.response.data,
                }, {
                    cancel: (dialog) => {
                        dialog.close();
                    },
                }, {
                    header: __('Просмотр логов по участникам видеосвязи'),
                    width: '1020px',
                    customClass: 'padding-0',
                })
            });
        },
        clearFiltersAndHideTable() {
            this.displayTable = false;
            this.clearFilters();
        },
        changeFiltersAndShowTable(filters) {
            this.changeFilters(filters);
            this.displayTable = true;
        },
    },
};
</script>
