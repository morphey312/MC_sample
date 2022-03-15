<template>
    <div v-loading="loading">
        <section class="grey filter">
            <service-filter
                ref="filter"
                :initial-state="filters"
                @changed="changeServiceFilters"
                @cleared="clearServiceFilters" />
        </section>
        <section class="grey-cap p-20">
            <service-list
                v-if="displayTable"
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters">
                <div class="buttons" slot="buttons">
                    <form-button
                        v-if="$canCreate('insurance-acts')"
                        :text="__('Экспорт в Excel')"
                        icon="download"
                        @click="exportAct(false)" />
                    <form-button
                        v-if="$canCreate('insurance-acts')"
                        :text="__('Редактировать пациента')"
                         :disabled="activeItem === null"
                        icon="edit"
                        @click="editPatient" />
                    <form-button
                        v-if="$canCreate('insurance-acts')"
                        :text="__('Редактировать запись')"
                         :disabled="activeItem === null"
                        icon="edit"
                        @click="showAppointment" />
                    <form-button
                        v-if="$can('patient-cabinet.access')"
                        :disabled="activeItem === null"
                        :text="__('Кабинет пациента')"
                        icon="catalogue"
                        @click="goPatientCabinet" />
                    <form-button
                        v-if="$canCreate('insurance-acts')"
                        :text="__('Сформировать акт')"
                        icon="plus"
                        @click="exportConfirm" />
                </div>
            </service-list>
        </section>
    </div>
</template>
<script>
import ManageMixin from '@/mixins/manage';
import ExportActMixin from './mixin/act-requisites';
import ServiceFilter from './ServiceFilter.vue';
import ServiceList from './ServiceList.vue';
import AppointmentServiceRepository from '@/repositories/appointment/service';
import InsuranceAct from '@/models/insurance-company/act';
import FileSaver from 'file-saver';
import actGenerator from './generator/act';
import actGeneratorService from './generator/actService';
import Appointment from '@/models/appointment';
import AppointmentManagerMixin from '@/components/appointments/mixin/manager';

export default {
    mixins: [
        ManageMixin,
        ExportActMixin,
        AppointmentManagerMixin
    ],
    components: {
        ServiceFilter,
        ServiceList
    },
    data() {
        return {
            displayTable: false,
            loading: false,
            filterInAct: true,
            repo: new AppointmentServiceRepository(),
            limit: 1000,
        };
    },
    methods: {
        getFilterUid() {
            return 'insurance-act-service-list';
        },
        changeServiceFilters(filters) {
            this.displayTable = this.filterNotEmpty(this.getServiceFilters());
            this.changeFilters(filters);
            if (!this.displayTable) {
                this.$error(__('Выберите все фильтры'));
            }
        },
        clearServiceFilters() {
            this.displayTable = false;
            this.clearFilters();
        },
        goPatientCabinet() {
            let routeData = this.$router.resolve({name: 'patient-cabinet-calls', params: {patientId: this.activeItem.patient_id}});
            window.open(routeData.href, '_blank');
        },
        exportConfirm() {
            if (! this.$refs.filter.filter.clinic.length) {
                this.loading = false;
                return this.$error(__('Выберите клинику для формирования акта'));
            }

            if (! this.$refs.filter.filter.with_policy.insurer.length) {
                this.loading = false;
                return this.$error(__('Выберите страховую компанию для формирования акта'));
            }

            if (this.$refs.filter.filter.clinic.length > 1 || this.$refs.filter.filter.with_policy.insurer.length > 1) {
                this.loading = false;
                return this.$error(__('Для формирования акта выберите только одну клинику и страховую компанию'));
            }

            this.$confirm(
                __('Вы подтверждаете, что Акт финально согласован, и можно сформировать акт?'),
                () => this.exportAct(true),
                {
                    confirmBtnText: __('Да'),
                    cancelBtnText: __('Нет'),
                }
            );
        },
        showAppointment() {
            let appointment = new Appointment({id: this.activeItem.appointment_id});
            appointment.fetch([
                'doctor',
                'clinic',
            ]).then(() => {
                this.makeDaySheetData(appointment, true).then(() => {
                    this.editAppointment((appointment) => {
                       this.refresh();
                    }, appointment);
                });
            });
        },
        exportAct(createAct = false) {
            this.filterInAct = createAct;
            this.loading = true;
            let rows = [];
            let makeAct = async () => {
                rows = await this.getServiceRows();
                if (rows.length === 0) {
                    this.loading = false;
                    return this.$error(__('Данные для формирования акта отсутствуют'));
                }

                let requisites = await this.getRequisites(
                    _.first(this.filters.clinic), 
                    _.first(this.filters.with_policy.insurer)
                    );
                let insuranceAct;
                if (createAct) {
                   insuranceAct = await this.createInsuranceAct();
                   requisites.insuranceAct = insuranceAct;
                   actGenerator(rows, requisites).then(({book, amount}) => {
                    book.xlsx.writeBuffer().then((buffer) => {
                        FileSaver.saveAs(new Blob([buffer]), __('Акт выполненных работ (страховые)') + '.xlsx');
                        insuranceAct.amount = amount;
                        this.updateInsuranceAct(insuranceAct, rows);
                        this.$info(__('Акт был успешно сформирован'));
                        this.loading = false;
                        this.refresh();
                    }).catch((err) => {
                        this.$error(__('Не удалось сохранить файл'));
                        this.loading = false;
                    });
                });
                } else {
                    requisites.insuranceAct = {number : ''};
                    actGeneratorService(rows, requisites).then(({book}) => {
                    book.xlsx.writeBuffer().then((buffer) => {
                        FileSaver.saveAs(new Blob([buffer]), __('Акт выполненных работ (страховые) пример') + '.xlsx');
                        this.loading = false;
                        this.refresh();
                    }).catch((err) => {
                        this.$error(__('Не удалось сохранить файл'));
                        this.loading = false;
                    });
                });
                }
                this.filterInAct = true;
            };
            makeAct();
        },
        createInsuranceAct() {
            let act = new InsuranceAct({
                insurance_company_id: this.filters.with_policy.insurer[0],
                clinic_id: this.filters.clinic[0],
            });
            return act.save().then(() => {
                return Promise.resolve(act);
            });
        },
        updateInsuranceAct(insuranceAct, rows = []) {
            insuranceAct.services = rows.map(row => {
                return {
                    service_id: row.id,
                    patient_id: row.patient_id,
                    insurance_payment: this.calcInsurancePayment(row),
                };
            });
            insuranceAct.save();
            insuranceAct.printed();
        },
        getServiceRows() {
            let rows = [];
            let table = this.getManageTable();
            let totalPages = table.$refs.pagination.last_page;
            let filters = _.onlyFilled(this.filters);
            let sort = table.sortOrder;

            let getDataRows = async () => {
                for (let page = 1; page <= totalPages; page++) {
                    let response = await this.repo.fetchInsuranceExportList(this.getExportFilters(filters), sort, null, page, this.limit);
                    rows = [...rows, ...response.rows];
                }
                return Promise.resolve();
            }
            return getDataRows().then(() => {
                return Promise.resolve(rows);
            });
        },
        calcInsurancePayment(row) {
            let debt = 0;
            row.cost = Number(row.cost);
            if (row.franchise == 0) {
                debt = row.cost;
            } else {
                debt = row.cost - Math.round(row.cost / 100 * Number(row.franchise));
            }
            return debt;
        },
        getExportFilters(filters) {
            if (this.filterInAct) {
                return {
                    ...filters,
                    not_in_act: true,
                };
            } else {
                return filters;
            }
            
        },
        filterNotEmpty(filters) {
            return Object.keys(filters)
                .filter(k => _.isVoid(filters[k]))
                .length === 0;
        },
        getServiceFilters() {
            let filters = {...this.getFilter().filter};
            let policyFilters = {...filters.with_policy};
            delete filters.with_policy;
            return {
                ...filters,
                ...policyFilters
            };
        },
        editPatient() {
            this.displayEditPatientForm(this.activeItem.patient_id,
                (patient) => {
                    this.refresh();
                },
            );
        },
    }
}
</script>
