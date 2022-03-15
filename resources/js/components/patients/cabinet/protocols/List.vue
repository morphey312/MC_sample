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
            slot="template"
            slot-scope="props">
            <span v-text="props.rowData.template_name || props.rowData.name"></span>
        </template>
        <template
            slot="attachments_data"
            slot-scope="props" >
            <div v-if="props.rowData.type === CONSTANTS.CARD_RECORD.TYPE.OUTCLINIC_PROTOCOL_RECORD">
                <div
                    v-for="(outclinicProtocolRecord, index) in props.rowData.attachments_data"
                    :key="index"
                    style="margin-top: 5px;">
                    <a
                        style="word-break: break-word;"
                        @click.prevent="view(outclinicProtocolRecord.url, __('Просмотр файла') + ' ' + outclinicProtocolRecord.name, props.rowData)"
                        v-text="outclinicProtocolRecord.name"></a>
                </div>
            </div>
            <div v-else>
                <a
                    href="#"
                    @click.prevent="view(props.rowData.file_data.url)">
                    {{ props.rowData.template_name || props.rowData.name }}
                </a>
            </div>
        </template>
        <template
            slot="remove"
            slot-scope="props" >
            <span @click="remove(props.rowData)">
                <svg-icon name="delete" class="icon-small icon-blue" />
            </span>
        </template>
        <template slot="footer-top">
            <div class="buttons" slot="buttons">
                <el-button
                    v-if="$can('patient-cabinet.outclinic-examination-add')"
                    @click="addResearchResult">
                    {{ __('Добавить результат') }}
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
import OutclinicProtocolForm from "@/components/patients/cabinet/protocols/OutclinicProtocolForm";

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
                    name: 'template',
                    filterField: 'protocol_template_name',
                    title: __('Название протокола'),
                    filter: true,
                },
                {
                    name: 'date',
                    sortField: 'created',
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
                    name: 'doctor.full_name',
                    filterField: 'doctor',
                    title: __('Врач'),
                    width: '10%',
                    filter: new EmployeeRepository({
                        filters: {
                            positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                            has_patient_appointment: this.patient.id,
                        },
                    }),
                },
                {
                    name: 'appointment_id',
                    title: __('Вне записи'),
                    filter: 'yes_no',
                    filterField: 'outclinic',
                    dataClass: 'no-ellipsis no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString( !value,
                            '<span class="check-yes" />');
                    },
                    width: '8%',
                },
                {
                    name: 'allowed_in_ok',
                    title: __('Доступно в ЛК'),
                    filter: 'yes_no',
                    filterField: 'allowed_in_ok',
                    dataClass: 'no-ellipsis no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString( value,
                            '<span class="check-yes" />');
                    },
                    width: '8%',
                },
                {
                    name: 'attachments_data',
                    title: __('Файлы'),
                    dataClass: 'no-ellipsis',
                    width: '15%',
                },
                ...(this.$can('patient-cabinet.delete-protocols') ? [   {
                    name: 'remove',
                    title: '',
                    width: "35px",
                    dataClass: 'no-ellipsis no-dash',
                },] : []),
            ],
            initialSortOrder: [
                {field: 'created', direction: 'desc'},
            ],
            CONSTANTS: CONSTANTS
        };
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        addResearchResult() {
            this.$modalComponent(OutclinicProtocolForm, {
                patient: this.patient,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                saved: (dialog, record) => {
                    dialog.close();
                    this.$emit('document-added', record);
                    this.refresh();
                },
            },
            {
                header: __('Прикрепить результат исследования к пациенту: {name}', {name: this.patient.full_name}),
                width: '450px',
                customClass: 'padding-0',
            });
        },
        remove(protocol){
            this.$confirm(__('Вы уверены, что хотите удалить документ?'), () => {
                protocol.delete().then(() => {
                    this.refresh();
                    return this.$info(__('Результат исследования успешно удалён'));
                }).catch((error) => {
                    return this.$error(__('Не удалось удалить результат исследования'));
                });
            });
        },
        getTable() {
            return this.$refs.table;
        },
        refresh() {
            return this.getTable().refresh();
        },
    },
};
</script>
