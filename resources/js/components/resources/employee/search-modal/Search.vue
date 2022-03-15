<template>
    <div class="sections-wrapper">
        <section class="grey">
            <employee-filter @changed="changeFilters"/>    
        </section>
        <section>
            <employee-list
                :filters="filters"
                :scopes="scopes"
                :load-list="loadList"
                @selected="selected"
            />
        </section>
        <div class="dialog-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
        </div>
    </div>  
</template>

<script>
import EmployeeFilter from '../Filter.vue';
import EmployeeList from './List.vue';

export default {
    components: {
        EmployeeFilter,
        EmployeeList,
    },
    props: {
        scopes: {
            type: Array,
            default: null,
        },
        initialFilters: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            filters: {
                ...this.initialFilters,
            },
            loadList: false,
        };
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        selected(item) {
            this.$emit('selected', item);
        },
        changeFilters(filters) {
            this.loadList = true;
            this.filters = filters;
        },
    },
}
</script>