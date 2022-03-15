<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :flex-height="true"
        @header-filter-updated="syncFilters">
        <template
            slot="remove"
            slot-scope="props" >
            <span @click="remove(props.rowData)">
                <svg-icon name="delete" class="icon-small icon-blue" />
            </span>
        </template>
        <template
            slot="name"
            slot-scope="props">
            <a
                href="#"
                @click.prevent="view(props.rowData.attachments_data[0].url)">
                {{ props.rowData.name || props.rowData.attachments_data[0].name}}
            </a>
        </template>
        <template slot="footer-top">
            <div
                slot="buttons"
                class="buttons">
                <el-button
                    v-if="$can('patient-cabinet.create-documents')"
                    @click="addDocument">
                    {{ __('Добавить документ') }}
                </el-button>
                <el-button
                    @click="showPatientQuestionnaire">
                    {{ __('Заполнить анкету') }}
                </el-button>
                <el-button
                    @click="showLog">
                    {{ __('Операции') }}
                </el-button>
            </div>
        </template>
    </manage-table>
</template>

<script>
import SpecializationRepository from '@/repositories/specialization';
import ProxyRepository from '@/repositories/proxy-repository';
import CardRecordRepository from '@/repositories/patient/card/record';
import EmployeeRepository from '@/repositories/employee';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import CONSTANTS  from '@/constants';
import FileActionMixin from '@/mixins/file-action';
import DocumentForm from "@/components/patients/cabinet/documents/DocumentForm.vue";
import QuestionnaireForm from "@/components/patients/cabinet/documents/QuestionnaireForm.vue";
import DocumentLog from "@/components/action-log/patient/card/SignedDocument.vue";

export default {
    mixins: [
        FileActionMixin,
    ],
    props: {
        filters: Object,
        patient: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(({filters, sort, page, limit}) => {
                let cards = this.patient.cards;
                let repository = new CardRecordRepository();
                if (cards.length === 0) {
                    return repository.emptyData();
                }
                return repository.fetch({
                    ...filters,
                    specialization_card: cards.map(c => c.id),
                }, sort, ['appointment', 'specialization', 'doctor'], page, limit);
            }),
            fields: [
                {
                    name: 'name',
                    filterField: 'name',
                    title: __('Имя документа'),
                    filter: true,
                },
                {
                    name: 'date',
                    sortField: 'date',
                    filterField: 'date',
                    title: __('Дата'),
                    width: '10%',
                    filter: DateHeaderFilter,
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
                {
                    name: 'specialization_name',
                    filterField: 'specialization',
                    title: __('Специализация'),
                    width: '15%',
                    filter: new SpecializationRepository({filters: {id: this.patient.cardsSpecializations}}),
                },
                {
                    name: 'doctor_name',
                    filterField: 'doctor',
                    title: __('Врач'),
                    width: '15%',
                    filter: new EmployeeRepository({
                        filters: {
                            positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                            has_patient_appointment: this.patient.id,
                        },
                    }),
                },
                ...(this.$can('patient-cabinet.delete-documents') ? [   {
                    name: 'remove',
                    title: '',
                    width: "35px",
                    dataClass: 'no-ellipsis no-dash',
                },] : []),
            ],
            initialSortOrder: [
                {field: 'date', direction: 'desc'},
            ],
        };
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        addDocument() {
            this.$modalComponent(DocumentForm, {
                patient: this.patient,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                saved: (dialog, record) => {
                    dialog.close();
                    this.getTable().refresh();
                    this.$emit('document-added', record);
                },
            },
            {
                header: __('Прикрепить документ к пациенту: {name}', {name: this.patient.full_name}),
                width: '450px',
                customClass: 'padding-0',
            });
        },
        showLog() {
            this.$modalComponent(DocumentLog, {
                id: this.patient.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения записи'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        showPatientQuestionnaire(){
            this.$modalComponent(QuestionnaireForm, {
                patient: this.patient
            }, {
                close: (dialog) => {
                    dialog.close();
                },
                saved: () => {
                    this.getTable().refresh();
                }
            }, {
                header: __('Выберите клинику для анкеты'),
                width: '400px',
                customClass: 'no-footer',
            });
        },
        getTable() {
            return this.$refs.table;
        },
        remove(document){
            document.delete().then(() => {
                this.getTable().refresh();
                return this.$info(__('Документ удалён успешно!'));
            }).catch(() => {
                return this.$error(__('Не удалось удалить документ'));
            });
        }
    },
};
</script>
