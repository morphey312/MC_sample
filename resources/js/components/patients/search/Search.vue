<template>
    <div class="sections-wrapper">
        <drawer :open="displayFilter">
            <section class="grey pb-0 pt-10">
                <patient-filter 
                    ref="patientFilter"
                    :skipId="skipId"
                    :show-create-button="showCreateButton"
                    :restrict-clinics="restrictClinics"
                    :initial-state="filters"
                    @create-patient="addPatient"
                    @changed="syncListFilters"
                    @cleared="clearFilters"/>
            </section>
        </drawer>
        <section 
            class="grey-cap flex-content"
            :style="{'height': listHeight}">
            <template v-if="loadList">
                <div v-show="emptySearchResults">
                    <empty>
                        <a 
                            v-if="$canCreate('patients')"
                            href="#" @click.prevent="addPatient">
                            {{ __('добавьте контакт') }}
                        </a>
                    </empty>
                </div>
                <patient-list
                    v-show="!emptySearchResults"
                    ref="table"
                    :filters="filters"
                    :load-list="loadList"
                    @loaded="refreshed"
                    @rows-count="setRowsCount"
                    @header-filter-updated="syncFilters"
                    @selected="selected"
                />
            </template>
        </section>
    </div>
</template>

<script>
import PatientFilter from './Filter.vue';
import PatientList from './List.vue';
import PatientCreateMixin from '../mixins/patient-create';
import Empty from './Empty.vue';
import ManageMixin from '@/mixins/manage';
import BackToList from './BackToList.vue';

export default {
    mixins: [
        PatientCreateMixin,
        ManageMixin,
    ],
    components: {
        PatientFilter,
        PatientList,
        Empty,
    },
    props: {
        skipId: {
            type: Array,
            default: () => [],
        },
        showCreateButton: {
            type: Boolean,
            default: true,
        },
        customCreateFunction: {
            type: Function,
            default: null,
        },
        restrictClinics: {
            type: Boolean,
            default: false,
        },
        filterDefaults: {
            type: Function,
            default: () => ({}),
        },
        autoPick: {
            type: Boolean,
            default: true,
        },
        autoSearch: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            displayFilter: true,
            loadList: false,
            rowsCount: null,
        };
    },
    computed: {
        emptySearchResults() {
            return this.rowsCount === 0;
        },
        listHeight() {
            return this.displayFilter ? '360px' : '560px';
        },
    },
    mounted() {
        if (this.autoSearch) {
            this.loadList = true;
            this.rowsCount = null;
        }
    },
    methods: {
        getFilterUid() {
            return false;
        },
        getDefaultFilters() {
            return this.filterDefaults();
        },
        cancel() {
            this.$emit('cancel');
        },
        selected(item) {
            this.$emit('selected', item);
        },
        syncListFilters(updates) {
            this.loadList = true;
            this.rowsCount = null;
            this.changeFilters(updates);
        },
        getFilter() {
            return this.$refs.patientFilter;
        },
        setRowsCount(count) {
            this.rowsCount = count;
        },
        toggleFilter(val) {
            this.displayFilter = val;
        },
        changeFilterValues(patient) {
            this.getFilter().sync({
                firstname: patient.firstname,
                lastname: patient.lastname,
                middlename: patient.middlename,
            });
        },
        addPatient() {
            if (this.customCreateFunction !== null) {
                this.customCreateFunction((result) => {
                    if (result === false) {
                        this.$emit('cancel');
                    } else if (result) {
                        this.$emit('selected', result);
                    }
                });
            } else {
                this.createPatient({
                    component: BackToList,
                    eventListeners: {
                        close: (dialog) => {
                            dialog.close();
                        },
                    },
                }, (patient) => {
                    if (this.autoPick) {
                        this.selected(patient)
                    } else {
                        this.changeFilterValues(patient);
                    }
                });
            }
        },
    },
}
</script>