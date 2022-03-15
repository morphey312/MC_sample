<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :filters="filters"
        :scopes="scopes"
        :selectable-rows="true"
        :initial-sort-order="initialSortOrder"
        :flex-height="true"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template slot="download_link" slot-scope="props">
            <a  style="word-break: break-all;"
                v-if="props.rowData.attachments_data[0]" href="" @click.prevent="showVideo(props.rowData)"
                v-text="props.rowData.attachments_data[0].name"></a>
        </template>
        <template slot="composition_status" slot-scope="props">
            <span>
                {{ $handbook.getOption('twilio_composition_status', props.rowData.composition_status) }}
            </span>
            <span v-if="props.rowData.composition_status && props.rowData.composition_status !== 'composition-available'">
                ({{ props.rowData.composition_progress }}%)
            </span>
        </template>
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import DateHeaderFilter from "@/components/general/table/DateHeaderFilter.vue";
import TimeHeaderFilter from "@/components/general/table/TimeHeaderFilter.vue";
import VideoChatRepository from "@/repositories/video-chat";
import FileViewerHeader from "@/components/general/FileViewerHeader.vue";
import VideoPlayer from "@/components/general/VideoPlayer.vue";
import PatientRepository from "@/repositories/patient";
import EmployeeRepository from "@/repositories/employee";
import CONSTANTS from "@/constants";

export default {
    props: {
        filters: Object,
    },
    data() {
        let employeeRepo = new EmployeeRepository({
            filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR},
            limit: 50});
        let patientRepository = new PatientRepository({});
        return {
            repository: new VideoChatRepository(),
            fields: [
                {
                    name: 'appointment.date',
                    sortField: 'appointment_date',
                    title: __('Дата записи'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filter: DateHeaderFilter,
                    filterField: 'appointment_date',
                },
                {
                    name: 'appointment.start',
                    sortField: 'appointment_start',
                    title: __('Начало приема'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.timeFormat(this.$moment(value, 'HH:mm:ss'));
                    },
                    filter: TimeHeaderFilter,
                    filterField: 'appointment_start',
                },
                {
                    name: 'appointment.end',
                    sortField: 'appointment_end',
                    title: __('Окончание приема'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.timeFormat(this.$moment(value, 'HH:mm:ss'));
                    },
                    filter: TimeHeaderFilter,
                    filterField: 'appointment_end',
                },
                {
                    name: 'appointment.doctor',
                    sortField: 'appointment_doctor',
                    title: __('Врач'),
                    filter: employeeRepo,
                    filterField: 'doctor',
                    filterProps: {
                        multiple: true,
                        limit: 70
                    },
                    width: '10%',
                },
                {
                    name: 'appointment.patient',
                    sortField: 'patient',
                    filter: patientRepository,
                    filterProps: {
                        multiple: true,
                        limit: 70
                    },
                    filterField: 'patient',
                    title: __('Пациент'),
                    width: '15%',
                },
                {
                    name: 'room_status',
                    title: __('Статус комнаты'),
                    filter: this.$handbook.getOptions('twilio_room_status'),
                    formatter: (val) => {
                        return this.$handbook.getOption('twilio_room_status', val);
                    },
                    width: "10%",
                    filterField: 'room_status',
                    titleClass: 'text-right',
                    dataClass: 'no-ellipsis',
                },
                {
                    name: 'composition_status',
                    title: __('Статус обработки видео'),
                    filter: this.$handbook.getOptions('twilio_composition_status'),
                    filterField: 'composition_status',
                    width: "10%",
                    titleClass: 'text-right',
                    dataClass: 'no-ellipsis',
                },
                {
                    name: 'download_link',
                    title: __('Ссылка на запись'),
                    width: "10%",
                    filter: 'yes_no',
                    filterField: 'download_link',
                    titleClass: 'text-right',
                    dataClass: 'no-ellipsis',
                },
                {
                    name: 'duration_in_minutes',
                    title: __('Длительность (мин)'),
                    sortField: 'duration',
                    width: "10%",
                    titleClass: 'text-right',
                    dataClass: 'no-ellipsis',
                },
                {
                    name: 'twilio_price',
                    title: __('Вартість (дол. США)'),
                    width: "10%",
                    formatter: (val) => {
                        return '≈ ' + val + ' $';
                    },
                    titleClass: 'text-right',
                    dataClass: 'no-ellipsis',
                },
            ],
            scopes: [
                'appointment',
                'attachments'
            ],
            initialSortOrder: [
                {field: 'appointment_date', direction: 'desc'},
            ],
        };
    },
    methods: {
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        showVideo(video){
            this.$modalComponent(VideoPlayer, {
                url: video.attachments_data[0].url,
                name: video.attachments_data[0].name
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Просмотр видео'),
                width: '835px',
                customClass: 'no-footer',
                headerAddon: {
                    component: FileViewerHeader,
                    props: {
                        showDownload: true,
                        showPrint: false
                    },
                    eventListeners: {
                        downloadFile(dialog){
                            dialog.getTopComponent().download();
                        }
                    },
                }
            });
        },
        loaded() {
            this.$emit('loaded');
        },
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
    },
}
</script>
