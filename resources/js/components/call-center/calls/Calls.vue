<template>
    <page
        :title="__('Информационные звонки')"
        type="flex">
        <template
            slot="header-addon">
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
                <call-filter 
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <calls 
                :table-filters="filters"
                :flex-height="true"
                @header-filter-updated="syncFilters" />
        </section>
    </page>
</template>

<script>
import Calls from '../calls-appointments/Calls.vue';
import CallFilter from './Filter.vue';
import TablesMixin from '../mixins/tables';

export default {
    mixins: [
        TablesMixin,
    ],
    components: {
        Calls,
        CallFilter,
    },
    methods: {
        getDefaultFilters() {
            let today = this.$moment().format('YYYY-MM-DD');
            return {
                is_deleted: 0,
                created_start: today,
                created_end: today,
            };
        },
    },
}
</script>