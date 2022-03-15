<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :scopes="scopes"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        :flex-height="true"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters"
    >
           <template slot="appointment.patient.name" slot-scope="props">
                <a href="#" @click.prevent="showAppointment(props.rowData.appointment_id)">{{ props.rowData.appointment.patient.name }}</a>
        </template>
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import AmbulanceCallRepository from "@/repositories/appointment/ambulance-call";
import EmployeeRepository from "@/repositories/employee";
import DateRangeHeaderFilter from "@/components/general/table/DateRangeHeaderFilter.vue";
import AppointmentManagerMixin from "@/components/appointments/mixin/manager";
import Appointment from '@/models/appointment';
import CONSTANTS from "@/constants";

export default {
    mixins: [AppointmentManagerMixin],
    props: {
        filters: Object
    },
    data() {
        return {
            repository: new AmbulanceCallRepository({
                filters: { appointment_completed: true }
            }),
            fields: [
                {
                    name: "id",
                    title: __("№ Вызова"),
                    dataClass: "text-select",
                    width: "5%",
                    filter: true
                },
                {
                    name: "created_at",
                    filterField: "created_at",
                    title: __("Период"),
                    width: "10%",
                    dataClass: "no-dash text-select",
                    formatter: value => {
                        return this.$formatter.dateFormat(value, "DD.MM.YYYY");
                    },
                    filter: DateRangeHeaderFilter
                },
                {
                    name: "created_at",
                    title: __("Время вызова"),
                    formatter: value => {
                        return this.$formatter.dateFormat(value, "HH:mm:ss");
                    },
                    dataClass: "text-select",
                    width: "7%"
                },
                {
                    name: "call_transferred_time",
                    title: __("Время передачи вызова"),
                    formatter: value => {
                        return this.$formatter.dateFormat(value, "HH:mm:ss");
                    },
                    dataClass: "text-select",
                    width: "7%"
                },
                {
                    name: "en_route_time",
                    title: __("Время выезда"),
                    formatter: value => {
                        return this.$formatter.dateFormat(value, "HH:mm:ss");
                    },
                    dataClass: "text-select",
                    width: "7%"
                },
                {
                    name: "arrival_time",
                    title: __("Время доезда"),
                    formatter: value => {
                        return this.$formatter.dateFormat(value, "HH:mm:ss");
                    },
                    dataClass: "text-select",
                    width: "7%"
                },
                {
                    name: "en_route_overall_minutes",
                    title: __("Время в пути"),
                    dataClass: "text-select",
                    sortField: "en_route_overall_minutes",
                    width: "7%"
                },
                {
                    name: "appointment.doctor",
                    sortField: "doctor_name",
                    title: __("Врач"),
                    filter: new EmployeeRepository({
                        filters: {
                            positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR
                        },
                        limit: 50
                    }),
                    filterField: "doctor",
                    filterProps: {
                        multiple: true,
                        limit: 70
                    },
                    width: "6%"
                },
                {
                    name: "appointment.patient.name",
                    title: __("Пациент"),
                    dataClass: "text-select",
                    width: "12%"
                },
                {
                    name: "appointment.card_number",
                    title: __("Номер карты"),
                    width: "10%"
                },
                {
                    name: "appointment.patient.birthday",
                    title: __("Дата рождения"),
                    width: "10%",
                    dataClass: "no-dash",
                    formatter: value => {
                        return this.$formatter.dateFormat(value);
                    }
                },
                {
                    name:
                        "appointment.patient.contact_details.primary_phone_number",
                    title: __("Номер телефона"),
                    filterField: "patient_phone",
                    width: "15%",
                    formatter: value => {
                        return this.$formatter.phoneNumberFormat(value);
                    }
                },
                {
                    name: "district",
                    title: __("Район"),
                    width: "10%",
                    filter: true,
                    filterField: "district",
                    filterProps: {
                        searchModes: true
                    }
                },
                {
                    name: "caller",
                    title: __("Кто вызвал"),
                    width: "10%",
                    filterField: "caller"
                },
                {
                    name: "reason",
                    title: __("Повод вызова"),
                    width: "10%",
                    filterField: "reason"
                },
                {
                    name: "appointment.patient.card_debt",
                    title: __("Долг по карте"),
                    width: "10%"
                }
            ],
            initialSortOrder: [{ field: "created", direction: "desc" }],
            scopes: ["appointment", "patient_contacts"]
        };
    },
    methods: {
        syncFilters(updates) {
            this.$emit("header-filter-updated", updates);
        },
        loaded() {
            this.$emit("loaded");
        },
        selectionChanged(selection) {
            this.$emit("selection-changed", selection);
        },
        showAppointment(appointment_id) {
            let appointment = new Appointment({
                id: appointment_id
            });
            appointment
                .fetch([
                    "patient_assigned_analyses",
                    "patient_assigned_services",
                    "patient_assigned_consultations",
                    "patient_debts",
                    "patient_issued_discount_cards",
                    "patient_insurance_policies",
                    "patient_legal_entity",
                    "appointment_services_prices",
                    "doctor",
                    "insurance_policy",
                    "default",
                    "existing_call_request",
                    "surgery_employees",
                    "clinic",
                    "ambulance_call"
                ])
                .then(() => {
                    this.makeDaySheetData(appointment, true).then(() => {
                        this.editAppointment(appointment => {
                            this.refresh();
                        }, appointment);
                    });
                });
        }
    }
};
</script>
