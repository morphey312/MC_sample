<template>
    <page
        :title="__('Сотрудники')"
        v-loading="loading"
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
            <section class="grey filter">
                <employee-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <employee-list
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters">
                <div class="buttons" slot="buttons">
                    <form-button
                        v-if="$canCreate('employees')"
                        :text="__('Добавить')"
                        icon="plus"
                        @click="create" />
                    <form-button
                        v-if="$canUpdate('employees')"
                        :disabled="activeItem === null || !$canManage('employees.update', activeItem.clinic_ids)"
                        :text="__('Редактировать')"
                        icon="edit"
                        @click="edit" />
                    <form-button
                        v-if="$canAccess('day-sheets')"
                        :disabled="hasNotDaySheet"
                        :text="__('Табель')"
                        icon="menu-documents"
                        @click="showDaysheets" />
                    <form-button
                        v-if="$can('action-logs.access')"
                        :disabled="activeItem === null"
                        :text="__('Операции')"
                        icon="menu-marketing"
                        @click="showLog" />
                    <el-dropdown class="ml-10">
                        <el-button >
                            {{ __('Еще') }}
                        </el-button>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item v-if="$canDelete('employees')">
                                <el-button
                                    type="text"
                                    :disabled="activeItem === null || !$canManage('employees.delete', activeItem.clinic_ids)"
                                    @click="remove">
                                    {{ __('Удалить') }}
                                </el-button>
                            </el-dropdown-item>
                            <el-dropdown-item  v-if="$can('employees.export-password')">
                                <el-button
                                    type="text"
                                    @click="exportPassExcel">
                                    {{ __('Экспорт доступов сотрудников') }}
                                </el-button>
                            </el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                </div>
            </employee-list>
        </section>
    </page>
</template>
<script>
import EmployeeList from './employee/List.vue';
import EmployeeFilter from './employee/Filter.vue';
import FormCreate from './employee/FormCreate.vue';
import FormEdit from './employee/FormEdit.vue';
import EmployeeLog from '@/components/action-log/Employee.vue';
import ManageMixin from '@/mixins/manage';
import CONSTANTS from '@/constants';
import EmployeeRepository from '@/repositories/employee';
import * as CredentialGenerator from './employee/generators/credential';
import ExportXLSXMixin from '@/mixins/export-xlsx-list';
import FileSaver from 'file-saver';

export default {
    mixins: [
        ManageMixin,
        ExportXLSXMixin,
    ],
    components: {
        EmployeeList,
        EmployeeFilter,
    },
    data() {
        return {
            displayFilter: true,
            needRefresh: false,
            loading: false,
            credentialGenerator: CredentialGenerator,
        };
    },
    computed: {
        hasNotDaySheet() {
            if (this.activeItem === null || this.activeItem.employee_clinics.length === 0) {
                return true;
            }

            let doctor = _.find(this.activeItem.employee_clinics, (clinic) => {
                return clinic.doctor && clinic.doctor.id;
            });

            return _.isNil(doctor);
        },
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить сотрудника'),
                editHeader: __('Изменить сотрудника'),
                width: '900px',
                beforeClose: (dialog) => {
                    let form = dialog.getTopComponent();
                    return form.checkPreventClose === undefined
                        || form.checkPreventClose() !== false;
                },
                onClosed: () => {
                    if (this.needRefresh) {
                        this.refresh();
                    }
                },
                events: {
                    clinicsUpdated: () => {
                        this.needRefresh = true;
                    },
                },
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этого сотрудника?'),
                deleted: __('Сотрудник был успешно удален'),
                created: __('Сотрудник был успешно добавлен'),
                updated: __('Сотрудник был успешно обновлен'),
            };
        },
        showDaysheets() {
            this.$router.push({name: 'day-sheet-schedule', params: {
                    id: this.activeItem.id,
                    owner_type: CONSTANTS.DAY_SHEET.OWNER_TYPES.EMPLOYEE,
                }
            });
        },
        getDefaultFilters() {
            return {
                status: CONSTANTS.EMPLOYEE.STATUSES.WORKING,
            };
        },
        refresh() {
            this.needRefresh = false;
            this.getManageTable().refresh();
        },
        showLog() {
            this.$modalComponent(EmployeeLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения данных сотрудника'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        exportPassExcel() {
            let reportRepository = new EmployeeRepository();
            this.loading = true;
            let fileName = __('Доступы сотрудников');
            let promise = Promise.resolve();
            let table = this.getManageTable();
            let filters = _.onlyFilled(this.filters);
            let scopes = ['permissions', 'clinics'];
            let sort = table.sortOrder;
            let totalPages = table.$refs.pagination.last_page;
            let limit = table.$refs.pagination.pageSize;
            let fields = [
                {
                    name: 'full_name',
                    title: __('ФИО'),
                },
                {
                    name: 'user.login',
                    title: __('Логин'),
                },
                {
                    name: 'user.password',
                    title: __('Пароль'),
                },
                {
                    name: 'clinic_names',
                    title: __('Клиника'),
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                },
                {
                    name: 'position_names',
                    title: __('Должность'),
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                },
            ];
            let book = this.getReportWorkBook(fields, {width: 25});

            let getDataRows = async () => {
                for (let page = 1; page <= totalPages; page++) {
                    let response = await reportRepository.fetch(filters, sort, scopes, page, limit);
                    let rows = response.rows;
                    this.credentialGenerator.reportAddRows(book, rows, fields)
                }
                return book.xlsx.writeBuffer().then((buffer) => {
                    FileSaver.saveAs(new Blob([buffer]), `${fileName}.xlsx`);
                    this.loading = false;
                    return promise;
                }).catch((err) => {
                    console.error(err);
                    this.$error(__('Не удалось сохранить файл'));
                });
            }
            getDataRows();
            return promise;
        },
    },
}
</script>
