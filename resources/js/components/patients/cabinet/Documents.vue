<template>
    <page
        :title="__('Документы пациента: {name}', {name: patient.full_name})"
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
            </div>
        </template>
        <documents-list
            :filters="filters"
            :patient="patient"
            @header-filter-updated="syncFilters" />
    </page>
</template>

<script>
import CabinetMixin from '@/components/patients/cabinet/mixins/cabinet';
import DocumentsList from '@/components/patients/cabinet/documents/List.vue';
import CONSTANTS from '@/constants';

export default {
    components: {
        DocumentsList,
    },
    mixins: [
        CabinetMixin,
    ],
    data() {
        return {
            filters: this.getBaseFilter(),
        };
    },
    methods: {
        getBaseFilter() {
            return {
                specialization_card: this.patient.cards.map(card => card.id),
                type: [CONSTANTS.CARD_RECORD.TYPE.PATIENT_DOCUMENT],
            };
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({
                ...this.filters,
                ...updates,
                ...this.getBaseFilter(),
            });
        },
    }
};
</script>
