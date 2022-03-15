<template>
    <page
        :title="__('Медицинские карты: {name}', {name: patient.full_name})"
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
        <section class="grey details">
            <el-row :gutter="20">
                <el-col :span="6">
                    <span class="patient-info">
                        <span>{{ __('Номер карты:') }}</span>
                        {{ patient.cards[0] ? patient.cards[0].number : __('НЕТ КАРТЫ') }}
                    </span>
                </el-col>
                <el-col :span="6">
                    <span class="patient-info">
                        <span>{{ __('Дата рождения:') }}</span>
                        {{ $formatter.dateFormat(patient.birthday) || __('Нет данных') }}
                    </span>
                </el-col>
            </el-row>
        </section>
        <card-list
            :filters="filters"
            :patient="patient"
            @header-filter-updated="syncFilters" />
    </page>
</template>

<script>
import CabinetMixin from './mixins/cabinet';
import CardList from './outpatient-cards/List.vue';

export default {
    mixins: [
        CabinetMixin,
    ],
    components: {
        CardList,
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
                has_records: 1,
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
