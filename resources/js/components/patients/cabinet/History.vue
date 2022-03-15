<template>
    <page
        :title="__('История пациента: {name}', {name: patient.full_name})"
        type="flex">
        <template slot="header-addon">
            <div class="buttons">
                <a
                    href="#"
                    @click.prevent="showSignalRecord">
                    <svg-icon
                        name="report-alt"
                        class="icon-small icon-blue">
                        {{ __('Сигнальные обозначения') }}
                    </svg-icon>
                </a>
                <toggle-link v-model="displayFilter">
                    <svg-icon name="filter-alt" class="icon-small icon-blue">
                        {{ __('Фильтр') }}
                    </svg-icon>
                </toggle-link>
            </div>
        </template>
        <patient-history
            :patient="patient"
            :display-filter="displayFilter"
            footer-class="sticky-footer"
            body-class="shrinkable block-card-records"
            css-class="shrinkable content-wrapper records-history" />
    </page>
</template>

<script>
import CabinetMixin from './mixins/cabinet';
import PatientHistory from '@/components/doctor/appointment/patient-history/History.vue';

export default {
    mixins: [
        CabinetMixin,
    ],
    components: {
        PatientHistory,
    },
    data() {
        return {
            displayFilter: true,
        };
    },
};
</script>
