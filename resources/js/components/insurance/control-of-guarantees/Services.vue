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
                        @click="exportAct()" />
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
                        :disabled="activeItem === null"
                        :text="__('Задача')"
                        icon="edit"
                        @click="editTask" />
                    <form-button
                        :disabled="activeItem === null"
                        :text="__('Примечание')"
                        icon="edit"
                        @click="editNote" />
                </div>
            </service-list>
        </section>
    </div>
</template>
<script>
import ManageMixin from '@/mixins/manage';
import ExportActMixin from '../execution-act/mixin/act-requisites';
import ServiceFilter from './ServiceFilter.vue';
import ServiceList from './ServiceList.vue';
import EditNote from './EditNote.vue';
import AppointmentServiceRepository from '@/repositories/appointment/service';
import FileSaver from 'file-saver';
import actGenerator from './generator/act';
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
            repo: new AppointmentServiceRepository(),
        };
    },
    methods: {
        editNote() {
            let header = this.activeItem.note ? __('Редактировать примечание') : __('Добавить примечание')
            this.getNoteEditModal(header, 'note')
        },
        editTask() {
            let header = this.activeItem.note ? __('Редактировать задачу') : __('Добавить задачу')
            this.getNoteEditModal(header, 'task')
        },
        getNoteEditModal(header, field) {
            this.$modalComponent(EditNote, {
                note: this.activeItem.note,
                appointment_id: this.activeItem.appointment_id,
                field: field
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                saved: (dialog) => {
                    dialog.close();
                    this.activeItem = null;
                    this.refresh()
                },
            }, {
                header: header,
                width: '500px',
            });
        },
        getFilterUid() {
            return 'control-of-guarantees-list';
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
        exportAct() {
            this.loading = true;
            let rows = [];
            let makeAct = async () => {
                rows = await this.getServiceRows();
                if (rows.length === 0) {
                    this.loading = false;
                    return this.$error(__('Данные для формирования акта отсутствуют'));
                }

                let requisites = await this.getRequisites(this.filters.clinic, this.filters.with_policy.insurer);
                actGenerator(rows, requisites).then(({book, amount}) => {
                    book.xlsx.writeBuffer().then((buffer) => {
                        FileSaver.saveAs(new Blob([buffer]), __('Акт выполненных работ (страховые)') + '.xlsx');
                        this.loading = false;
                        this.refresh();
                    }).catch((err) => {
                        this.$error(__('Не удалось сохранить файл'));
                        this.loading = false;
                    });
                });
            };
            makeAct();
        },
        getServiceRows() {
            let rows = [];
            let table = this.getManageTable();
            let totalPages = table.$refs.pagination.last_page;
            let filters = _.onlyFilled(this.filters);
            let sort = table.sortOrder;
            let limit = table.$refs.pagination.pageSize;
            let scopes = ['note'];

            let getDataRows = async () => {
                for (let page = 1; page <= totalPages; page++) {
                    let response = await this.repo.fetchInsuranceExportList(this.getExportFilters(filters), sort,scopes, page, limit);
                    rows = [...rows, ...response.rows];
                }
                return Promise.resolve();
            }
            return getDataRows().then(() => {
                return Promise.resolve(rows);
            });
        },
        getExportFilters(filters) {
            return {
                ...filters,
                not_in_act: true,
            };
        },
        filterNotEmpty(filters) {
            return Object.keys(filters)
                .filter((k) => {
                    if (_.isArray(filters[k]))
                        return filters[k].length === 0
                    else
                        return _.isVoid(filters[k])
                    })
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
