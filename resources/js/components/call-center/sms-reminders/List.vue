<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :scopes="scopes"
        @header-filter-updated="syncFilters">
        <template
            slot="phone_number"
            slot-scope="props">
            <span v-if="props.rowData.appointment">
                {{ $formatter.phoneNumberFormat(props.rowData.appointment.patient.phone_number) }}
            </span>
            <span v-else>
                {{ $formatter.phoneNumberFormat(props.rowData.phone_number) }}
            </span>
        </template>
        <template slot="spacer">
        </template>
    </manage-table>
</template>


<script>
import SmsReminderRepository from '@/repositories/sms-reminder';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import NotificationTemplateRepository from "@/repositories/notification/template";
import SelectContactMixin from '../mixins/select-contact';
import DateRangeHeaderFilter from '@/components/general/table/DateRangeHeaderFilter.vue';
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';

export default {
    mixins: [
        SelectContactMixin,
    ],
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new SmsReminderRepository(),
            fields: [
                {
                    name: 'appointment.patient.full_name',
                    sortField: 'patient_name',
                    filterField: 'patient_name',
                    title: __('Пациент'),
                    width: '15%',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'phone_number',
                    filterField: 'patient_number',
                    title: __('Номер телефона'),
                    width: '15%',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'scheduled_at',
                    sortField: 'scheduled_at',
                    filterField: 'scheduled_at',
                    title: __('План. Отправка'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                    filter: DateRangeHeaderFilter,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'status',
                    sortField: 'status',
                    filterField: 'status',
                    title: __('Статус'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$handbook.getOption('delivery_status', value);
                    },
                    filter: 'delivery_status',
                    filterProps: {
                        searchModes: true,
                        multiple: true
                    },
                },
                {
                    name: 'appointment.clinic',
                    title: __('Клиника'),
                    width: '8%',
                    filter: new ClinicRepository(),
                    filterField: 'clinic',
                    sortField: 'clinic',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'appointment.specialization.name',
                    title: __('Специализация'),
                    width: "10%",
                    filterField: 'specialization',
                    filter: new SpecializationRepository(),
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'template.name',
                    sortField: 'template',
                    filterField: 'template',
                    title: __('Шаблон'),
                    width: '15%',
                    filter: new NotificationTemplateRepository(),
                    filterProps: {
                        multiple: true,
                    },
                },
            ],
            initialSortOrder: [
                {field: 'started_at', direction: 'desc'},
            ],
            scopes: [
                'template',
                'appointment',
            ],
        };
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
    },
};
</script>
