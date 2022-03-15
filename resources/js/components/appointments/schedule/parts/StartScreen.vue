<template>
    <div class="empty-content-wrapper">
        <div class="empty-content">
            <div class="empty-content-img"></div>
            <span @click="searchDaySheet">{{ __('Добавить лист записи врачей') }}</span>
        </div>
    </div>
</template>

<script>
import SelectDaySheet from '@/components/appointments/modal/SelectDaySheet.vue';

export default {
    props: {
        doctor: {
            type: [Number, String],
            default: null,
        },
        patient: {
            type: Object,
            default: null,
        },
    },
    methods: {
        searchDaySheet() {
            this.$modalComponent(SelectDaySheet, {
                doctor: this.doctor,
                patient: this.patient,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, params) => {
                    dialog.close();
                    this.$emit('schedule-selected', params);
                },
            }, {
                header: __('Выбор листов записи пациентов'),
                width: '1200px',
                customClass: 'padding-0',
            });
        },
    }
}
</script>