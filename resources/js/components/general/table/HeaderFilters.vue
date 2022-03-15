<template>
    <tr class="header-filters">
        <template v-for="(field, fieldIndex) in $parent.tableFields">
            <template v-if="field.visible">
                <th 
                    :key="'_' + field.name"
                    :id="'_' + field.name"
                    :style="{width: field.width}">
                    <template v-if="field.filter === true">
                        <input-header-filter
                            v-model="filters[field.filterField || field.name]"
                            v-bind="field.filterProps || {}"
                            @change="updateFilter(field.filterField || field.name)" />
                    </template>
                    <template v-else-if="isOptions(field.filter)">
                        <select-header-filter
                            v-model="filters[field.filterField || field.name]"
                            v-bind="field.filterProps || {}"
                            :options="field.filter"
                            @change="updateFilter(field.filterField || field.name)" />
                    </template>
                    <template v-else-if="field.filter">
                        <component
                            v-model="filters[field.filterField || field.name]"
                            v-bind="field.filterProps || {}"
                            :is="field.filter"
                            :field="field"
                            :vuetable="$parent"
                            :key="fieldIndex" 
                            @change="updateFilter(field.filterField || field.name)" />
                    </template>
                </th>
            </template>
        </template>
        <vuetable-col-gutter v-if="$parent.scrollVisible"/>
    </tr>
</template>
<script>
import VuetableColGutter from 'vuetable-2';
import DateHeaderFilter from './DateHeaderFilter.vue';
import SelectHeaderFilter from './SelectHeaderFilter.vue';
import InputHeaderFilter from './InputHeaderFilter.vue';
import BaseRepository from '@/repositories/base-repository';

export default {
    components: {
        VuetableColGutter,
        DateHeaderFilter,
        SelectHeaderFilter,
        InputHeaderFilter,
    },
    data() {
        return {
            filters: {},
        };
    },
    mounted() {
        this.filters = {...this.$parent.headerFilter};    
    },
    methods: {
        isOptions(obj) {
            return _.isArray(obj) 
                || _.isString(obj) 
                || (obj instanceof BaseRepository);
        },
        updateFilter(key) {
            let update = {};
            update[key] = this.filters[key];
            this.$parent.$emit('header-filters-updated', update);
        },
    },
    watch: {
        ['$parent.headerFilter'](val) {
            this.filters = {...val};
        }
    },
}
</script>
