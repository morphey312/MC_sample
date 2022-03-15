<template>
    <page 
        :title="__('Результаты анализов: {name}', {name: patient.full_name})"
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
            <analyses-list
                :filters="filters"
                :patient="patient"
                @header-filter-updated="syncFilters" />
        </section>
    </page>
</template>

<script>
import CabinetMixin from './mixins/cabinet';
import AnalysesList from './analyses-results/List.vue';

export default {
    mixins: [
        CabinetMixin,
    ],
    components: {
        AnalysesList,
    },
    data() {
        return {
            filters: this.getBaseFilter(),
        };
    },
    methods: {
        getBaseFilter() {
            return {
                patient: this.patient.id,
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