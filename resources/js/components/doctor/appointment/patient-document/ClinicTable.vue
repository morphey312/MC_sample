<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        @header-filter-updated="syncFilters"
        @loaded="loaded">
        <template
            slot="fill_out"
            slot-scope="props">
            <div class="has-icon">
                <a href="#"
                   @click.prevent="fillOut(props.rowData)">
                   {{ __('Заполнить и печатать') }}
                </a>
            </div>
        </template>
    </manage-table>
</template>

<script>
import SearchTable from './mixins/search-table';

export default {
    mixins: [
        SearchTable,
    ],
    data() {
        return {
            filters: {
                specialization: _.map(this.doctorSpecializationList, 'id'),
                clinic: this.appointment.clinic_id,
                is_official_form: false,
                is_conclusion: false,
            },
        };
    },
}
</script>
