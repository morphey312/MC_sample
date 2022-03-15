<template>
    <page
        :title="__('Курсы валют')"
        type="flex">
        <template slot="header-addon">
            <div class="buttons">
                <toggle-link v-model="displayFilter">
                    <svg-icon name="filter-alt" class="icon-small icon-blue">
                        {{ __('Фильтр') }}
                    </svg-icon>
                </toggle-link>
            </div>
        </template>
        <drawer :open="displayFilter">
            <section class="grey">
                <exchange-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <exchange-list
                ref="table"
                :filters="filters"
                @header-filter-updated="syncFilters">
            </exchange-list>
         </section>
    </page>
</template>
<script>
import ExchangeFilter from './exchange/Filter.vue';
import ExchangeList from './exchange/List.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        ExchangeFilter,
        ExchangeList,
    },
    data() {
        return {
            displayFilter: true,
            loading: false,
        }
    },
}
</script>