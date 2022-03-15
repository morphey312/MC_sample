<template>
    <section>
        <manage-table
            ref="table"
            :fields="fields"
            :filters="filters"
            :repository="repository"
            :scopes="scopes"
            :show-table-settings="false"
            @header-filter-updated="syncFilters"
            @loaded="loaded">
            <template
                slot="view"
                slot-scope="props">
                <div class="has-icon">
                    <a href="#"
                        v-if="props.rowData.file_data"
                        @click.prevent="view(props.rowData.file_data.url, '', {}, false)">
                        {{ __('Открыть') }}
                    </a>
                </div>
            </template>
        </manage-table>
    </section>
</template>

<script>
import PatientClinicRouteRepository from "@/repositories/patient/clinic-route";
import FileActionMixin from '@/mixins/file-action';

export default {
    mixins: [
        FileActionMixin,
    ],
    props: {
        appointment: Object,
    },
    data() {
        let doctorSpecializationList = this.appointment.doctor_specializations;
        return {
            filters: {
                specialization: doctorSpecializationList.map(s => s.id),
            },
            repository: new PatientClinicRouteRepository(),
            fields: [
                {
                    name: 'name',
                    title: __('Название'),
                    filterField: 'name',
                    filter: true,
                },
                {
                    name: 'specialization_names',
                    title: __('Специализация'),
                    width: "20%",
                    filterField: 'specialization',
                    filter: doctorSpecializationList,
                    filterProps: {
                        multiple: true,
                    },
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                },
                {
                    name: 'view',
                    title: __('Заполнить и печатать'),
                    width: "150px",
                },
            ],
            scopes: [
                'file',
                'specializations',
            ],
        };
    },
    methods: {
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
        loaded() {
            this.$emit('loaded');
        },
        cancel() {
            this.$emit('cancel');
        },
    }
}
</script>