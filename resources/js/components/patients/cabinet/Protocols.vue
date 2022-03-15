<template>
    <page 
        :title="__('Результаты исследований: {name}', {name: patient.full_name})"
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
        <section class="grey-cap shrinkable">
            <protocols-list 
                :filters="filters"
                :patient="patient"
                @header-filter-updated="syncFilters" />
        </section>
    </page>
</template>

<script>
import CabinetMixin from './mixins/cabinet';
import ProtocolsList from './protocols/List.vue';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        CabinetMixin,
    ],
    components: {
        ProtocolsList,
    },
    data() {
        return {
            filters: this.getBaseFilter(),
        };
    },
    methods: {
        getBaseFilter() {
            return {
                specialization_card: this.patient.cards.map(card => card.id),
                type: [CONSTANTS.CARD_RECORD.TYPE.PROTOCOL_RECORD, CONSTANTS.CARD_RECORD.TYPE.OUTCLINIC_PROTOCOL_RECORD],
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
