<template>
    <div class="calls-appointments-pane">
        <section class="grey filter">
            <calls-appointments-filter
                ref="filter"
                :start-collapsed="!displayFilter"
                :initial-state="filters"
                @changed="changeFilters"
                @cleared="clearFilters" />
        </section>
        <section class="grey-cap">
            <div
                v-if="$canAccess('appointments')"
                class="split-table">
                <h2>{{ __('Записи') }}</h2>
                <appointments
                    :table-filters="filters"
                    @header-filter-updated="syncFilters" />
            </div>
            <div
                v-if="$canAccess('calls')"
                class="split-table">
                <h2>{{ __('Звонки') }}</h2>
                <calls
                    :table-filters="filters"
                    @header-filter-updated="syncFilters" />
            </div>
        </section>
    </div>
</template>

<script>
import CallsAppointmentsFilter from './Filter.vue';
import Calls from './Calls.vue';
import Appointments from './Appointments.vue';
import TablesMixin from '../mixins/tables';
import {PatientContact} from '@/services/sip-ua/process-state';

export default {
    mixins: [
        TablesMixin,
    ],
    components: {
        CallsAppointmentsFilter,
        Calls,
        Appointments,
    },
    mounted() {
        this.watchViopContactChange();
        this.watchClearVoipData();
    },
    methods: {
        getInitialFilters(defaultFilters) {
            let contact = this.getVoipContact();
            return contact
                ? this.filterByContact(contact)
                : this.getStoredFilter(defaultFilters);
        },
        getFilterUid() {
            return 'call-center-calls-appointments';
        },
        getDefaultFilters() {
            let user = this.getUser();
            let today = this.$moment().format('YYYY-MM-DD');

            return {
                ...(user.isOperator ? {operator: [user.employee_id]} : {}),
                is_deleted: 0,
                created_start: today,
                created_end: today,
            };
        },
        filterByContact(contact) {
            return (contact instanceof PatientContact) ? {
                patient: contact.id,
            } : {
                patient_phone_number: `=${contact.defaultNumber}`,
            };
        },
        filterByPatient(patient) {
            this.changeFilters({
                patient: patient.id,
            });
        },
    },
}
</script>
