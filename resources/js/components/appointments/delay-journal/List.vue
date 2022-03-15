<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :scopes="scopes"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="false"
        :flex-height="true"
        @loaded="loaded">
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>
<script>
import AppointmentDelayRepository from '@/repositories/appointment/delay';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new AppointmentDelayRepository(),
            fields: [
                {
                    name: 'appointment.clinic_name',
                    title: __('Клиника'),
                    width: '150px',
                },
                {
                    name: 'appointment.date',
                    title: __('Дата записи'),
                    width: '100px',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                },
                {
                    name: 'appointment.card_number',
                    title: __('№ карты'),
                    width: '100px',
                },
                {
                    name: 'appointment.doctor',
                    title: __('ФИО врача'),
                    width: '200px',
                },
                {
                    name: 'employee.full_name',
                    title: __('Кто изменил'),
                    width: '200px',
                },
                {
                    name: 'delay_reason.name',
                    title: __('Причина задержки'),
                    width: '250px',
                },
                {
                    name: 'comment',
                    title: __('Комментарий'),
                    width: '250px',
                },
                {
                    name: 'duration',
                    title: __('Длительность задержки, мин'),
                    width: '150px',
                    formatter: (val) => {
                        return this.$formatter.durationShortFormat(val);
                    },
                },
            ],
            initialSortOrder: [
                {field: 'created', direction: 'asc'},
            ],
            scopes: [
                'appointment',
                'delay_reason',
                'employee',
            ],
        }
    },
    methods: {
        loaded() {
            this.$emit('loaded');
        },
    },
}
</script>